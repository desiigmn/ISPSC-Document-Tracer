<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\Office;
use App\Models\Signatory;
use App\Models\User;
use App\Models\Notification;
use App\Models\DocumentAttachment;
use App\Mail\UrgentDocumentAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Hash;

class DocumentController extends Controller
{
    const RECORDS_OFFICE_ID = 'ISPSC-MC-REC-2026-4URQGK';

    public function create()
    {
        $offices = Office::where('id', '!=', self::RECORDS_OFFICE_ID)->orderBy('office_name', 'asc')->get();
        $users = User::with('office')->get();
        return view('documents.create', compact('offices', 'users'));
    }

    /**
     * STORE: Register document and handle automatic Title/ID generation
     */
    public function store(Request $request)
    {
        $isHardCopy = $request->has('is_hard_copy');

        // 1. Unified Validation
        $request->validate([
            'classification' => 'required',
            'custom_title' => $request->classification === 'Others' ? 'required|string|max:100' : 'nullable',
            'target_office_id' => 'required',
            'signatory_names' => 'required|array|min:1',
            'priority' => 'required|integer',
            'physical_description' => $isHardCopy ? 'required|string|max:255' : 'nullable',
            'doc_files' => $isHardCopy ? 'nullable' : 'required|array',
            'doc_files.*' => 'required|mimes:pdf,jpg,jpeg,png|max:51200', // Set to 50MB (51200 KB)
        ]);

        return DB::transaction(function () use ($request, $isHardCopy) {
            
            // 2. Automated Title Generation (Fixes "Title field doesn't have default value")
            if ($isHardCopy) {
                $title = $request->physical_description; 
            } else {
                $title = ($request->classification === 'Others') ? $request->custom_title : $request->classification;
            }

            // 3. Determine Suffix and Generate Tracking ID
            $suffix = match((int)$request->priority) {
                3 => 'EXT',
                2 => 'URG',
                default => 'NOR',
            };
            $trackingId = "ISPSC-" . now()->format('m/d/Y-H:i:s') . "-" . $suffix;

            // 4. Create Document Record
            $document = Document::create([
                'tracking_id' => $trackingId,
                'title' => $title,
                'classification' => $request->classification,
                'priority' => $request->priority,
                'status' => 'pending',
                'uploader_id' => Auth::id(),
                'is_hard_copy' => $isHardCopy,
                'current_office_id' => Auth::user()->office_id, 
                'target_office_id' => $request->target_office_id,
                'file_path' => $isHardCopy ? 'PHYSICAL_ITEM' : '',
                'current_step' => 1
            ]);

            // 5. Handle File Uploads (Digital only)
            if (!$isHardCopy && $request->hasFile('doc_files')) {
                foreach ($request->file('doc_files') as $index => $file) {
                    $path = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
                    
                    if ($index === 0) $document->update(['file_path' => $path]);

                    DocumentAttachment::create([
                        'document_id' => $document->id,
                        'file_path'   => $path,
                        'file_name'   => $file->getClientOriginalName(),
                        'file_type'   => $file->getMimeType()
                    ]);
                }
            }

            // 6. Create Signatories & Initial Notifications
            foreach ($request->signatory_names as $index => $name) {
                $user = User::where('username', $name)->first();
                if ($user) {
                    Signatory::create([
                        'document_id' => $document->id, 
                        'user_id'     => $user->id, 
                        'sign_order'  => $index + 1, 
                        'status'      => 'pending'
                    ]);

                    if ($index === 0) {
                        Notification::create([
                            'user_id' => $user->id,
                            'type'    => 'incoming', 
                            'message' => "Action Required: {$document->tracking_id}",
                            'link'    => route('documents.view', $document->id)
                        ]);

                        if ($document->priority >= 2 && !empty($user->email)) {
                            try {
                                Mail::to($user->email)->send(new UrgentDocumentAlert($document, false));
                            } catch (\Exception $e) { \Log::error("Mail failed: " . $e->getMessage()); }
                        }
                    }
                }
            }

            // 7. Initial Log
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => Auth::id(),
                'action'      => 'TIME OF HELLO',
                'office_id'   => Auth::user()->office_id,
                'remarks'     => $isHardCopy ? "Physical item registered: " . $title : "Digital document uploaded: " . $title
            ]);

            // 8. Final Redirect Logic
            if ($isHardCopy) {
                return redirect()->route('dashboard')->with('msg', 'Physical Tracking Started Successfully!');
            }
            
            return redirect()->route('documents.map', $document->id)->with('msg', 'Digital Routing Started! Please place signature tags.');
        });
    }
/**
     * SHOW: HUB
     */
    public function show($id)
    {
        $document = Document::with(['logs.user', 'signatories.user', 'uploader'])
                    ->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();
        return view('documents.view', compact('document'));
    }

    /**
     * SIGN: Apply signature/receipt and notify NEXT person immediately via email
     */
    public function sign(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $signatory = Signatory::where('document_id', $id)
                    ->where('user_id', Auth::id())
                    ->where('sign_order', $document->current_step)
                    ->firstOrFail();

        return DB::transaction(function () use ($request, $document, $signatory) {
            $signatory->update([
                'status' => 'signed',
                'signature_data' => $document->is_hard_copy ? 'PHYSICAL_RECEIPT' : $request->signature_data,
                'signed_at' => now()
            ]);

            $nextSigner = Signatory::with('user')->where('document_id', $document->id)
                        ->where('sign_order', $document->current_step + 1)
                        ->first();

            if ($nextSigner) {
                $document->update([
                    'current_step' => $document->current_step + 1,
                    'current_office_id' => $nextSigner->user->office_id
                ]);

                Notification::create([
                    'user_id' => $nextSigner->user_id,
                    'type'    => 'incoming', 
                    'message' => "Incoming document: {$document->tracking_id}",
                    'link'    => route('documents.view', $document->id)
                ]);

                // EMAIL ALERT TO NEXT SIGNER (Immediate)
                if ($document->priority >= 2 && !empty($nextSigner->user->email)) {
                    try {
                        Mail::to($nextSigner->user->email)->send(new UrgentDocumentAlert($document, false));
                    } catch (\Exception $e) { \Log::error("Next signer mail failed: " . $e->getMessage()); }
                }
            } else {
                $document->update(['status' => 'accepted']);
                Notification::create([
                    'user_id' => $document->uploader_id,
                    'type'    => 'finished',
                    'message' => "Congrats! Your document is fully signed.",
                    'link'    => route('documents.view', $document->id)
                ]);
            }

            DocumentLog::create([
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'action' => $document->is_hard_copy ? 'PHYSICAL ITEM RECEIVED' : 'DIGITAL SIGNATURE APPLIED',
                'office_id' => Auth::user()->office_id,
                'remarks' => $document->is_hard_copy ? 'Possession confirmed.' : 'Signature applied.'
            ]);

            return response()->json(['status' => 'success']);
        });
    }

    /**
     * PREVIEW: Live generation (Numeric ID to avoid 404)
     */
    public function previewWithSigs($id)
    {
            ini_set('memory_limit', '512M'); 
            set_time_limit(300);

            $document = Document::with('signatories.user')->findOrFail($id);
        $document = Document::with('signatories.user')->findOrFail($id);
        $pdf = new Fpdi();
        $filePath = storage_path('app/public/' . $document->file_path);
        if (!file_exists($filePath)) abort(404);

        $pageCount = $pdf->setSourceFile($filePath);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            foreach ($document->signatories as $sig) {
                if ($sig->status == 'signed' && $sig->signature_data && $sig->signature_data !== 'PHYSICAL_RECEIPT' && intval($sig->page_num ?? 1) == $pageNo) {
                    $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $sig->signature_data));
                    $tempPath = storage_path('app/public/temp_'.uniqid().'.png');
                    file_put_contents($tempPath, $imgData);
                    $pdf->Image($tempPath, ($sig->x_pos/100)*$size['width']-15, ($sig->y_pos/100)*$size['height']-10, 30);
                    unlink($tempPath);
                }
            }
        }
        return response($pdf->Output('S'), 200)->header('Content-Type', 'application/pdf');
    }

    public function downloadFinal(Request $request, $id) { return $this->previewWithSigs($id); }
    public function map($id) { $document = Document::with('signatories.user')->findOrFail($id); return view('documents.map', compact('document')); }
    public function saveTag(Request $request) { 
        Signatory::where('document_id', $request->doc_id)->where('user_id', $request->user_id)
                 ->update(['x_pos' => $request->x, 'y_pos' => $request->y, 'page_num' => $request->page_num]);
        return response()->json(['status' => 'OK']);
    }

/**
 * 7. RETURN: Signatory sends document back to creator
 */
public function return(Request $request, $id)
{
    $request->validate(['remarks' => 'required|string|max:500']);

    // Find by numeric ID or Tracking ID
    $document = Document::where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

    return DB::transaction(function () use ($request, $document) {
        $document->update(['status' => 'returned']);

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'DOCUMENT RETURNED',
            'office_id' => Auth::user()->office_id,
            'remarks' => "REASON: " . $request->remarks 
        ]);

        Notification::create([
            'user_id' => $document->uploader_id,
            'type' => 'returned',
            'message' => "URGENT: Document {$document->tracking_id} was returned for correction.",
            'link' => route('documents.view', $document->tracking_id)
        ]);

        return response()->json(['status' => 'success']);
    });
}

/**
 * DISSEMINATE: Share finalized document with multiple offices.
 * Only accessible by Records Office personnel.
 */
public function disseminate(Request $request, $id)
{
    // 1. Security Check: Only Records Office staff
    if (!str_contains(Auth::user()->office_id, '-REC-')) {
        abort(403, 'Unauthorized. Only Records Office personnel can share finalized documents.');
    }

    // 2. Validation
    $request->validate([
        'office_ids' => 'required|array',
        'office_ids.*' => 'exists:offices,id'
    ]);

    $document = Document::findOrFail($id);

    return DB::transaction(function () use ($request, $document) {
        foreach ($request->office_ids as $officeId) {
            
            // 3. Create a Log entry for the RECEIVING office
            // This is the CRITICAL link that makes it appear in their Dashboard table
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => Auth::id(), // The sender
                'action'      => 'DISSEMINATED',
                'office_id'   => $officeId, // The recipient office
                'remarks'     => 'A finalized copy was officially shared with this office by the Records Office.'
            ]);

            // 4. Find all users in the receiving office
            $recipients = User::where('office_id', $officeId)->get();

            foreach ($recipients as $recipient) {
                // 5. Notify them
                Notification::create([
                    'user_id' => $recipient->id,
                    'type'    => 'disseminated',
                    'message' => "New Document Shared: {$document->tracking_id} is now available in your finished records.",
                    // FIX: Direct them to their Dashboard (Finished filter) instead of a single view
                    'link'    => route('dashboard', ['filter' => 'accepted'])
                ]);
            }
        }

        return back()->with('msg', 'Document successfully shared with ' . count($request->office_ids) . ' office(s).');
    });
}
    public function changeOwnPassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $user = Auth::user();

    // Verify Current Password
    if (!Hash::check($request->current_password, $user->password)) {
        return back()->with('error', 'The current password you entered is incorrect.');
    }

    // Update to New Password
    $user->password = Hash::make($request->password);
    $user->save();

    return back()->with('msg', 'Your password has been updated successfully!');
}
public function streamFile($id)
{
    // Find the document by numeric ID
    $document = Document::findOrFail($id);
    
    // Check if file exists in the public disk
    if (!Storage::disk('public')->exists($document->file_path)) {
        abort(404, "Physical file not found in storage/app/public/" . $document->file_path);
    }

    // This streams the file directly to the browser with correct headers
    return response()->file(storage_path('app/public/' . $document->file_path));
}
/**
 * 8. RESUBMIT: Creator uploads corrected files
 */
public function resubmit(Request $request, $id)
{
    $request->validate([
        'doc_files' => 'required|array',
        'doc_files.*' => 'required|mimes:pdf,jpg,jpeg,png,docx,doc|max:10240'
    ]);

    // Find document by numeric ID or Tracking ID
    $document = Document::with('signatories')->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

    if ($document->uploader_id !== Auth::id()) abort(403);

    return DB::transaction(function () use ($request, $document) {
        // 1. Handle File Replacement
        // Delete old attachments
        foreach ($document->attachments as $oldFile) {
            Storage::disk('public')->delete($oldFile->file_path);
            $oldFile->delete();
        }

        // Upload new files
        if ($request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $index => $file) {
                $path = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
                if ($index === 0) $document->update(['file_path' => $path]);

                DocumentAttachment::create([
                    'document_id' => $document->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType()
                ]);
            }
        }

        // 2. LOGIC FIX: PRESERVE PREVIOUS SIGNATURES
        // We only reset the status of the current person (who returned it) 
        // and anyone further down the list.
        $document->signatories()
            ->where('sign_order', '>=', $document->current_step)
            ->update([
                'status' => 'pending',
                'signed_at' => null
                // Note: We do NOT clear signature_data or coordinates for previous steps
            ]);

        // 3. Set document back to pending
        $document->update(['status' => 'pending']);

        // 4. Audit Log
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'RE-SUBMITTED',
            'office_id' => Auth::user()->office_id,
            'remarks' => 'Corrected document uploaded. Existing valid signatures were preserved.'
        ]);

        return redirect()->route('documents.view', $document->tracking_id)->with('msg', 'Document resubmitted. Signatures from previous steps were kept.');
    });
}
}