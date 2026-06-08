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
     * STORE: Register document and send IMMEDIATE Email Alert
     */
public function store(Request $request)
{
    $isHardCopy = $request->has('is_hard_copy');

    // 1. Unified Validation
    $request->validate([
        'title' => 'required|string|max:255',
        'classification' => 'required',
        'classification_other' => $request->classification === 'Others' ? 'required|string|max:100' : 'nullable',
        'target_office_id' => 'required',
        'signatory_names' => 'required|array|min:1',
        'priority' => 'required|integer',
        'physical_description' => $isHardCopy ? 'required|string|max:255' : 'nullable',
        'doc_files' => $isHardCopy ? 'nullable' : 'required|array',
    ]);

    return DB::transaction(function () use ($request, $isHardCopy) {
        
        // 2. Handle Classification Logic (If "Others" is picked, use the specific input)
        $classification = $request->classification;
        if ($classification === 'Others') {
            $classification = $request->classification_other;
        }

        // 3. Generate Tracking ID (Added random string to prevent collisions)
        $trackingId = "ISPSC-" . now()->format('mdY-His') . "-" . strtoupper(Str::random(4));

        // 4. Create Document 
        // We use $request->title which is now required in the form
        $document = Document::create([
            'tracking_id'        => $trackingId,
            'title'              => $request->title, 
            'classification'     => $classification,
            'priority'           => $request->priority,
            'status'             => 'pending',
            'uploader_id'        => Auth::id(),
            'is_hard_copy'       => $isHardCopy,
            'current_office_id'  => Auth::user()->office_id, 
            'target_office_id'   => $request->target_office_id,
            'file_path'          => $isHardCopy ? 'PHYSICAL_ITEM' : '',
            'current_step'       => 1
        ]);

        // 5. Handle Files (if Digital)
        if (!$isHardCopy && $request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $index => $file) {
                $path = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
                
                // Update the main file_path with the first file uploaded
                if ($index === 0) {
                    $document->update(['file_path' => $path]);
                }

                DocumentAttachment::create([
                    'document_id' => $document->id,
                    'file_path'   => $path,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_type'   => $file->getMimeType()
                ]);
            }
        }

        // 6. Create Signatories & Handle Notifications
        foreach ($request->signatory_names as $index => $name) {
            $user = User::where('username', $name)->first();
            if ($user) {
                Signatory::create([
                    'document_id' => $document->id, 
                    'user_id'     => $user->id, 
                    'sign_order'  => $index + 1, 
                    'status'      => 'pending'
                ]);

                // Notify the FIRST signatory immediately
                if ($index === 0) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type'    => 'incoming', 
                        'message' => "Action Required: New Document {$document->tracking_id}",
                        'link'    => route('documents.view', $document->id)
                    ]);

                    // Send email if Priority is Urgent (2) or Extreme (3)
                    if ($document->priority >= 2 && !empty($user->email)) {
                        try {
                            Mail::to($user->email)->send(new UrgentDocumentAlert($document, false));
                        } catch (\Exception $e) { 
                            \Log::error("Mail failed for {$user->email}: " . $e->getMessage()); 
                        }
                    }
                }
            }
        }

        // 7. Initial Log
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'CREATED',
            'office_id'   => Auth::user()->office_id,
            'remarks'     => $isHardCopy ? "Physical item registered: " . $request->physical_description : "Digital document uploaded."
        ]);

        // 8. Redirect
        if ($isHardCopy) {
            return redirect()->route('dashboard')->with('msg', 'Physical Tracking Started!');
        }
        
        return redirect()->route('documents.map', $document->id)->with('msg', 'Digital Routing Started!');
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

    public function return(Request $request, $id)
    {
        $request->validate(['remarks' => 'required|string|max:500']);
        $document = Document::findOrFail($id);
        return DB::transaction(function () use ($request, $document) {
            $document->update(['status' => 'returned']);
            DocumentLog::create(['document_id' => $document->id, 'user_id' => Auth::id(), 'action' => 'DOCUMENT RETURNED', 'office_id' => Auth::user()->office_id, 'remarks' => "Reason: " . $request->remarks]);
            Notification::create(['user_id' => $document->uploader_id, 'type' => 'returned', 'message' => "Document {$document->tracking_id} returned.", 'link' => route('documents.view', $document->id)]);
            return response()->json(['status' => 'success']);
        });
    }

    public function disseminate(Request $request, $id)
    {
        $request->validate(['office_ids' => 'required|array']);
        foreach ($request->office_ids as $officeId) {
            DocumentLog::create(['document_id' => $id, 'user_id' => Auth::id(), 'action' => 'DISSEMINATED', 'office_id' => $officeId, 'remarks' => 'Shared by Records.']);
            $staffs = User::where('office_id', $officeId)->get();
            foreach($staffs as $staff) {
                Notification::create(['user_id' => $staff->id, 'type' => 'disseminated', 'message' => "Finalized copy shared with you.", 'link' => route('documents.view', $id)]);
            }
        }
        return back()->with('msg', 'Shared successfully!');
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
}