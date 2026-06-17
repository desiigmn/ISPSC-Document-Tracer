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
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Hash;

class DocumentController extends Controller
{
    const RECORDS_OFFICE_ID = 'ISPSC-MC-REC-2026-4URQGK';

    public function create()
    {
        $offices = Office::where('id', '!=', self::RECORDS_OFFICE_ID)->orderBy('office_name', 'asc')->get();
        $users = User::with('office')->select('username', 'role_title', 'office_id')->get();
        return view('documents.create', compact('offices', 'users'));
    }

    /**
     * STORE: Register document and handle automatic Title/ID generation
     */
public function store(Request $request)
{
    $isHardCopy = $request->has('is_hard_copy');
    $user = Auth::user();
    $isAdminOrRecords = ($user->role === 'superadmin' || str_contains($user->office_id ?? '', '-REC-'));

    $request->validate([
        'classification' => 'required',
        'custom_title' => $request->classification === 'Others' ? 'required|string|max:100' : 'nullable',
        'target_office_id' => 'required',
        'signatory_names' => 'required|array|min:1',
        'priority' => $isAdminOrRecords ? 'required|integer|in:1,2,3' : 'nullable',
        'physical_description' => $isHardCopy ? 'required|string|max:255' : 'nullable',
        'doc_files' => $isHardCopy ? 'nullable' : 'required|array',
        'doc_files.*' => 'mimes:pdf,jpg,jpeg,png|max:51200', 
    ]);

    // Check for duplicates
    if (count($request->signatory_names) !== count(array_unique($request->signatory_names))) {
        return back()->withInput()->with('error', 'Duplicate individuals found in the signing sequence.');
    }

    return DB::transaction(function () use ($request, $isHardCopy, $isAdminOrRecords) {
        $title = ($isHardCopy) ? $request->physical_description : (($request->classification === 'Others') ? $request->custom_title : $request->classification);

        // Logic for Initial Tracking ID
        if ($isAdminOrRecords && $request->filled('priority')) {
            $suffix = match((int)$request->priority) { 3 => 'EXT', 2 => 'URG', default => 'NOR' };
        } else {
            $suffix = 'REV';
        }
        $trackingId = "ISPSC-" . now()->format('m/d/Y-H:i:s') . "-" . $suffix;

        // CREATE MAIN RECORD
        // Status remains 'mapping' -> invisible to signatories
        $document = Document::create([
            'tracking_id' => $trackingId,
            'title' => $title,
            'classification' => $request->classification,
            'status' => 'mapping', 
            'priority' => $isAdminOrRecords ? $request->priority : null,
            'uploader_id' => Auth::id(),
            'is_hard_copy' => $isHardCopy,
            'current_office_id' => Auth::user()->office_id, 
            'target_office_id' => $request->target_office_id,
            'file_path' => $isHardCopy ? 'PHYSICAL_ITEM' : '',
            'current_step' => 1
        ]);

        // HANDLE FILES (DB entries only, no signatory dashboard yet)
        if (!$isHardCopy && $request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $index => $file) {
                $path = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
                if ($index === 0) $document->update(['file_path' => $path]);
                \App\Models\DocumentAttachment::create([
                    'document_id' => $document->id,
                    'file_path'   => $path,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_type'   => $file->getMimeType()
                ]);
            }
        }

        // CREATE SIGNATORIES (SILENT - No Notifications created)
        foreach ($request->signatory_names as $index => $name) {
            $signer = \App\Models\User::where('username', $name)->first();
            if ($signer) {
                \App\Models\Signatory::create([
                    'document_id' => $document->id, 
                    'user_id'     => $signer->id, 
                    'sign_order'  => $index + 1, 
                    'status'      => 'pending'
                ]);
            }
        }

        \App\Models\DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'CREATED',
            'office_id'   => Auth::user()->office_id,
            'remarks'     => "Phase 1: Registration Complete. Document in tag-mapping state."
        ]);

        // User moves to UI Phase 2 (Mapping)
        return redirect()->route('documents.map', $document->id);
    });
}
/**
     * SHOW: HUB
     */
/**
     * 5. SHOW: HUB
     * Logic updated to support ALL campus Records Offices
     */
 public function show($id)
{
    // 1. Fetch document once with all necessary relations
    $document = Document::with(['logs.user', 'signatories.user', 'uploader'])
                ->where('id', $id)
                ->orWhere('tracking_id', $id)
                ->firstOrFail();
    
    $user = Auth::user();

    // 2. Define Access Permissions (Master View vs. Restricted View)
    $isSuperAdmin = ($user->role === 'superadmin');
    $isRecordsOffice = str_contains($user->office_id ?? '', '-REC-');
    $isUploader = ($document->uploader_id == $user->id);
    $isSignatory = $document->signatories->contains('user_id', $user->id);
    
    // Check if shared officially with the user's specific office
    $isDisseminatedToMe = $document->logs()
                        ->where('office_id', $user->office_id)
                        ->where('action', 'DISSEMINATED')
                        ->exists();

    // 3. CONSOLIDATED SECURITY GATE
    // Only allow: Superadmins, ANY Records Staff, the Uploader, the Signatories, or Recipients
    if (!$isSuperAdmin && !$isRecordsOffice && !$isUploader && !$isSignatory && !$isDisseminatedToMe) {
        abort(403, "Access Denied. You do not have permission to view this tracking record.");
    }

    // 4. QR Code generation (ISPSC Maroon Color)
    $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)
                ->color(128, 0, 0)
                ->generate(route('documents.view', $document->tracking_id));

    return view('documents.view', compact('document', 'qrCode'));
}

    /**
     * 6. SIGN: Notify NEXT person
     * Logic updated to ensure cross-campus users trigger emails
     */
 public function sign(Request $request, $id)
{
    $document = Document::where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();
    
    $signatory = Signatory::where('document_id', $document->id)
                ->where('user_id', Auth::id())
                ->where('sign_order', $document->current_step)
                ->firstOrFail();

    return DB::transaction(function () use ($request, $document, $signatory) {
        $signatory->update([
            'status' => 'signed',
            'signature_data' => $document->is_hard_copy ? 'PHYSICAL_RECEIPT' : $request->signature_data,
            'signed_at' => now()
        ]);

        // Find the next person in the sequence
        $nextSigner = Signatory::with('user')->where('document_id', $document->id)
                    ->where('sign_order', $document->current_step + 1)
                    ->first();

        if ($nextSigner) {
            $document->update([
                'current_step' => $document->current_step + 1,
                'current_office_id' => $nextSigner->user->office_id
            ]);

            // Internal Notification
            Notification::create([
                'user_id' => $nextSigner->user_id,
                'type'    => 'incoming', 
                'message' => "Incoming document for signature: {$document->tracking_id}",
                'link'    => route('documents.view', $document->tracking_id)
            ]);

            // --- FEATURE: NOTIFY NEXT SIGNER VIA EMAIL ---
            if (!empty($nextSigner->user->email)) {
                try {
                    // Send email regardless of priority level to ensure sequential notification
                    Mail::to($nextSigner->user->email)->send(new \App\Mail\UrgentDocumentAlert($document, false));
                } catch (\Exception $e) { \Log::error("Next signer mail failed: " . $e->getMessage()); }
            }
        } else {
            // Document is finished
            $document->update(['status' => 'accepted']);
            
            Notification::create([
                'user_id' => $document->uploader_id,
                'type'    => 'finished',
                'message' => "Process Finished: Document {$document->tracking_id} is now complete.",
                'link'    => route('documents.view', $document->tracking_id)
            ]);
        }

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => $document->is_hard_copy ? 'PHYSICAL ITEM RECEIVED' : 'DIGITAL SIGNATURE APPLIED',
            'office_id' => Auth::user()->office_id,
            'remarks' => $document->is_hard_copy ? 'Item received.' : 'Digital signature applied.'
        ]);

        return response()->json(['status' => 'success']);
    });
}

    public function previewWithSigs($id)
    {
        // 1. PERFORMANCE & MEMORY OVERRIDE
        ini_set('memory_limit', '1024M'); 
        set_time_limit(300);

        // 2. FIND DOCUMENT (By Numeric ID or Tracking ID String)
        $document = Document::with(['signatories.user', 'logs'])
                    ->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

        // 3. SECURITY GATE
        $user = Auth::user();
        $isDisseminatedToMe = $document->logs->where('office_id', $user->office_id)->where('action', 'DISSEMINATED')->count() > 0;
        $isCreator = ($document->uploader_id == $user->id);
        $isSignatory = $document->signatories->contains('user_id', $user->id);
        $isAdminOrRecords = ($user->role === 'superadmin' || str_contains($user->office_id ?? '', '-REC-'));

        if (!$isAdminOrRecords && !$isCreator && !$isSignatory && !$isDisseminatedToMe) { 
            abort(403, "You do not have access to view this document."); 
        }

        // 4. GENERATE PDF WITH FPDI
        $pdf = new Fpdi();
        $filePath = storage_path('app/public/' . $document->file_path);
        if (!file_exists($filePath)) abort(404, "Physical file not found in storage.");

        $pageCount = $pdf->setSourceFile($filePath);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            foreach ($document->signatories as $sig) {
                // Ensure coordinates exist for this person and matches current page
                if ($sig->x_pos && $sig->y_pos && intval($sig->page_num ?? 1) == $pageNo) {
                    
                    // Convert percentage coordinates (0-100) to actual PDF points
                    $x = ($sig->x_pos / 100) * $size['width'];
                    $y = ($sig->y_pos / 100) * $size['height'];

                    if ($sig->status == 'signed' && $sig->signature_data && $sig->signature_data !== 'PHYSICAL_RECEIPT') {
                        // --- CASE A: ALREADY SIGNED - Render the actual signature ---
                        $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $sig->signature_data));
                        $tempPath = storage_path('app/public/temp_sig_'.uniqid().'.png');
                        file_put_contents($tempPath, $imgData);
                        
                        $pdf->Image($tempPath, $x - 15, $y - 10, 30);
                        unlink($tempPath);
                    } 
                    elseif ($sig->status == 'pending' || $sig->status == 'returned') {
                        // --- CASE B: PENDING - Render a "Sign Here" placeholder ---
                        // Colors set to ISPSC Yellow/Maroon
                        $pdf->SetFillColor(255, 204, 0); // Bright Yellow
                        $pdf->SetDrawColor(128, 0, 0);   // Dark Maroon Border
                        $pdf->SetLineWidth(0.4);
                        
                        // Draw box (width 34, height 12)
                        $pdf->Rect($x - 17, $y - 6, 34, 12, 'DF'); 

                        // Label Text for Elder Accessibility
                        $pdf->SetTextColor(128, 0, 0); // Maroon Text
                        $pdf->SetFont('Arial', 'B', 7);
                        $pdf->Text($x - 15, $y - 1, "SIGN HERE"); // Upper text
                        
                        $pdf->SetFont('Arial', '', 5);
                        // Show Signatory name in the box so there is no confusion
                        $displayName = substr(strtoupper($sig->user->username), 0, 25);
                        $pdf->Text($x - 15, $y + 3, $displayName); // Lower text
                    }
                }
            }
        }
        
        return response($pdf->Output('S'), 200)->header('Content-Type', 'application/pdf');
    }

    /**
     * FINAL DOWNLOAD: Resues the same secure preview logic
     */
    public function downloadFinal(Request $request, $id) 
    { 
        return $this->previewWithSigs($id); 
    }
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
        'doc_files.*' => 'required|mimes:pdf,jpg,jpeg,png,docx,doc|max:51200'
    ]);

    $document = Document::with(['signatories.user', 'logs', 'attachments'])
                ->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

    if ($document->uploader_id !== Auth::id()) abort(403);

    return DB::transaction(function () use ($request, $document) {
        // 1. Handle File Replacement
        foreach ($document->attachments as $oldFile) {
            \Storage::disk('public')->delete($oldFile->file_path);
            $oldFile->delete();
        }

        if ($request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $index => $file) {
                $path = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
                if ($index === 0) $document->update(['file_path' => $path]);

                \App\Models\DocumentAttachment::create([
                    'document_id' => $document->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType()
                ]);
            }
        }

        // 2. RESET FLOW: Set back to Signer #1
        $document->update([
            'status' => 'pending',
            'current_step' => 1
        ]);

        // 3. RESET ALL SIGNATORIES: They must all sign the new version
        $document->signatories()->update([
            'status' => 'pending',
            'signed_at' => null,
            'signature_data' => null 
        ]);

        // 4. RETRIEVE RETURN REASON: Get the last comment from the log
        $returnLog = $document->logs()
                    ->where('action', 'DOCUMENT RETURNED')
                    ->latest()
                    ->first();
        
        $reason = $returnLog ? $returnLog->remarks : 'Corrections requested.';

        // 5. NOTIFY FIRST SIGNER VIA EMAIL
        $firstSigner = $document->signatories->where('sign_order', 1)->first();
        if ($firstSigner) {
            Notification::create([
                'user_id' => $firstSigner->user_id,
                'type'    => 'incoming', 
                'message' => "RESUBMITTED: Document {$document->tracking_id} has been updated. Please review corrections.",
                'link'    => route('documents.view', $document->tracking_id)
            ]);

            if (!empty($firstSigner->user->email)) {
                try {
                    // We pass true for "isResubmit" and the reason to the Mailable
                    Mail::to($firstSigner->user->email)->send(new \App\Mail\UrgentDocumentAlert($document, true, $reason));
                } catch (\Exception $e) { \Log::error("Resubmit mail failed: " . $e->getMessage()); }
            }
        }

        // 6. Audit Log
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'RE-SUBMITTED',
            'office_id'   => Auth::user()->office_id,
            'remarks'     => 'Corrected document uploaded. Process restarted from Signer #1.'
        ]);

        return redirect()->route('documents.view', $document->tracking_id)->with('msg', 'Document resubmitted and restarted from Signatory #1.');
    });
}

public function downloadQr($id)
{
    $document = Document::where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();
    
    $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(500)
        ->color(128, 0, 0)
        ->margin(1)
        ->generate(route('documents.view', $document->tracking_id));

    return response($qrCode)
        ->header('Content-Type', 'image/svg+xml')
        ->header('Content-Disposition', 'attachment; filename="QR_'.$document->id.'.svg"');
}
public function discard($id)
{
    // Find document with relations
    $document = Document::with(['signatories.user', 'attachments'])->findOrFail($id);
    if ($document->uploader_id !== Auth::id()) abort(403);

    // Capture the data to be restored in the form
    $formData = [
        'classification' => $document->classification,
        'priority' => $document->priority,
        'target_office_id' => $document->target_office_id,
        'is_hard_copy' => $document->is_hard_copy,
        'physical_description' => ($document->is_hard_copy) ? $document->title : null,
        'custom_title' => ($document->classification == 'Others' && !$document->is_hard_copy) ? $document->title : null,
        // Map signers back to the name array
        'signatory_names' => $document->signatories->sortBy('sign_order')->pluck('user.username')->toArray()
    ];

    // Delete uploaded files so storage stays clean
    foreach ($document->attachments as $file) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
    }

    // Delete the database draft
    $document->delete();

    // Redirect with "Flashing" input
    return redirect()->route('documents.create')->withInput($formData);
}
/**
 * Finalize mapping: Staff moves document to the Records Office Review queue.
 */
public function finalizeMapping($id) 
{
    // Use the document instance to check tags for better code integrity
    $document = Document::with(['signatories.user'])->findOrFail($id);
    
    // Safety check: Has the uploader actually dropped markers?
    $hasTags = $document->signatories()->whereNotNull('x_pos')->exists();
    
    if (!$hasTags) {
        return back()->with('error', 'Critical Error: At least one signature tag must be placed before finalizing.');
    }

    return DB::transaction(function () use ($document) {
        
        // Scenario A: Admin-mode Dispatch
        if (!is_null($document->priority)) {
            $document->update(['status' => 'pending']);

            // 1st Official Sequential Notification Trigger
            $firstSigner = $document->signatories->where('sign_order', 1)->first();
            if ($firstSigner) {
                // Internal Dashboard Alert
                \App\Models\Notification::create([
                    'user_id' => $firstSigner->user_id,
                    'type'    => 'incoming', 
                    'message' => "Phase 3 Dispatch: Action Required on {$document->tracking_id}",
                    'link'    => route('documents.view', $document->tracking_id)
                ]);

                // Sequence Start Email
                if (!empty($firstSigner->user->email)) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($firstSigner->user->email)
                            ->send(new \App\Mail\UrgentDocumentAlert($document, false));
                    } catch (\Exception $e) { \Log::error("Phase 3 Initial Mail Error: " . $e->getMessage()); }
                }
            }
            $msg = 'Document finalized and released to Signer #1.';
        } 
        
        // Scenario B: Staff Dispatch (Review pool)
        else {
            $document->update(['status' => 'needs_review']);
            $msg = 'Tags placed. Document sent to Records Office for priority validation.';
        }

        // Phase 3 Official Dispatch Log
        \App\Models\DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'FINALIZED',
            'office_id'   => Auth::user()->office_id,
            'remarks'     => "Phase 2 Complete: Mapping confirmed by uploader."
        ]);

        return redirect()->route('dashboard')->with('msg', $msg);
    });
}

/**
 * Set Priority: Records Office/Admin assigns priority and officially starts tracking.
 */
public function setPriority(Request $request, $id)
{
    $user = Auth::user();
    if ($user->role !== 'superadmin' && !str_contains($user->office_id ?? '', '-REC-')) {
        abort(403);
    }

    $request->validate(['priority' => 'required|integer|in:1,2,3']);
    $document = Document::with('signatories.user')->findOrFail($id);

    $suffix = match((int)$request->priority) {
        3 => 'EXT', 2 => 'URG', default => 'NOR',
    };
    
    $newTrackingId = \Illuminate\Support\Str::replaceLast('REV', $suffix, $document->tracking_id);

    return DB::transaction(function () use ($request, $document, $newTrackingId) {
        $document->update([
            'priority' => $request->priority,
            'tracking_id' => $newTrackingId,
            'status' => 'pending'
        ]);

        // --- FEATURE: NOTIFY FIRST SIGNER VIA EMAIL ---
        $firstSigner = $document->signatories->where('sign_order', 1)->first();
        if ($firstSigner) {
            // Internal Notification
            Notification::create([
                'user_id' => $firstSigner->user_id,
                'type'    => 'incoming', 
                'message' => "Action Required: Document {$document->tracking_id} assigned priority.",
                'link'    => route('documents.view', $document->tracking_id)
            ]);

            // Email Notification
            if (!empty($firstSigner->user->email)) {
                try {
                    Mail::to($firstSigner->user->email)->send(new \App\Mail\UrgentDocumentAlert($document, false));
                } catch (\Exception $e) { \Log::error("First signer mail failed: " . $e->getMessage()); }
            }
        }

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'PRIORITY ASSIGNED',
            'office_id'   => Auth::user()->office_id,
            'remarks'     => "Priority set to level " . $request->priority . ". Tracking started."
        ]);

        return redirect()->route('dashboard')->with('msg', 'Priority assigned and first signer notified.');
    });
}
/**
 * REVALIDATE: Creator disagrees with return reason and sends it back to that specific signer.
 */
public function revalidate(Request $request, $id)
{
    $request->validate(['explanation' => 'required|string|max:1000']);

    $document = Document::with(['signatories.user', 'logs'])
                ->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

    if ($document->uploader_id !== Auth::id()) abort(403);

    return DB::transaction(function () use ($request, $document) {
        // Define the variable locally from the request
        $explanation = $request->explanation;

        // 1. Set status back to pending
        $document->update(['status' => 'pending']);

        // 2. Identify the signatory who currently needs to sign
        $currentSigner = $document->signatories->where('sign_order', $document->current_step)->first();

        if ($currentSigner) {
            // Internal Notification
            Notification::create([
                'user_id' => $currentSigner->user_id,
                'type'    => 'incoming', 
                'message' => "RE-VALIDATED: Creator maintained original file for {$document->tracking_id}.",
                'link'    => route('documents.view', $document->tracking_id)
            ]);

            // Email Notification
            if (!empty($currentSigner->user->email)) {
                try {
                    // FIXED: Used $currentSigner instead of $signer
                    // FIXED: $explanation is now defined above
                    Mail::to($currentSigner->user->email)->send(new \App\Mail\UrgentDocumentAlert(
                        $document, 
                        false,         // $isResubmit
                        null,          // $reason
                        false,         // $isReminder
                        $explanation   // $uploaderNote
                    ));
                } catch (\Exception $e) { 
                    \Log::error("Revalidate mail failed: " . $e->getMessage()); 
                }
            }
        }

        // 3. Audit Log
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'RE-VALIDATED',
            'office_id'   => Auth::user()->office_id,
            'remarks'     => "CREATOR EXPLANATION: " . $explanation
        ]);

        return redirect()->route('documents.view', $document->tracking_id)->with('msg', 'Explanation sent back to the signatory.');
    });
}
}