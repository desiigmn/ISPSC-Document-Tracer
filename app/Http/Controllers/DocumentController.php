<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\Office;
use App\Models\Signatory;
use App\Models\User;
use App\Models\DocumentAttachment;
use App\Mail\UrgentDocumentAlert;
use App\Mail\DocumentReturnedAlert;
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

    /**
     * Create View: Show form to register new document
     */
public function create()
{
    // Fetch offices for the destination dropdown
    $offices = Office::where('id', '!=', self::RECORDS_OFFICE_ID)->orderBy('office_name', 'asc')->get();

    // Fetch the users to allow the search engine to find the "Designations"
    // We pass this because your Blade script uses @json($users)
    $users = User::with('office')->get();

    // Fetch unique designations for the routing logic
    $designations = User::whereNotNull('role_title')->distinct()->pluck('role_title');

    return view('documents.create', compact('offices', 'users', 'designations'));
}

/**
     * STORE: Register document and handle initial routing
     */
    public function store(Request $request)
    {
        $isHardCopy = $request->has('is_hard_copy');
        $user = Auth::user();
        $isAdminOrRecords = ($user->role === 'superadmin' || str_contains($user->office_id ?? '', '-REC-'));

        $request->validate([
            'classification' => 'required',
            'custom_title' => $request->classification === 'Others' ? 'required|string|max:100' : 'nullable',
            'signatory_offices' => 'required|array|min:1', // Routed by Office IDs
            'priority' => $isAdminOrRecords ? 'required|integer|in:1,2,3' : 'nullable',
            'physical_description' => $isHardCopy ? 'required|string|max:255' : 'nullable',
            'doc_files' => $isHardCopy ? 'nullable' : 'required|array',
            'doc_files.*' => 'mimes:pdf,jpg,jpeg,png|max:51200',
        ]);

        return DB::transaction(function () use ($request, $isHardCopy, $isAdminOrRecords) {
            // Determine Title
            $title = ($isHardCopy) ? $request->physical_description : (($request->classification === 'Others') ? $request->custom_title : $request->classification);

            // Generate Suffix and Tracking ID
            $suffix = ($isAdminOrRecords && $request->filled('priority')) 
                ? match ((int)$request->priority) { 3 => 'EXT', 2 => 'URG', default => 'NOR' } 
                : 'REV';

            $trackingId = "ISPSC-" . now()->format('m/d/Y-H:i:s') . "-" . $suffix;

            // Create Main Document Record
            $document = Document::create([
                'tracking_id' => $trackingId,
                'title' => $title,
                'classification' => $request->classification,
                'status' => 'mapping',
                'priority' => $isAdminOrRecords ? $request->priority : null,
                'uploader_id' => Auth::id(),
                'is_hard_copy' => $isHardCopy,
                'current_office_id' => Auth::user()->office_id,
                'file_path' => $isHardCopy ? 'PHYSICAL_ITEM' : '',
                'current_step' => 1
            ]);
            
            // Create Signatories based on Office IDs
            // Inside the store() method's transaction
            foreach ($request->signatory_offices as $index => $officeId) {
                Signatory::create([
                    'document_id' => $document->id,
                    'office_id'   => $officeId, // We use Office ID
                    'user_id'     => null,     // User is unknown until they sign
                    'sign_order'  => $index + 1,
                    'status'      => 'pending'
                ]);
            }

            // Handle File Attachments (Soft Copy Only)
            if (!$isHardCopy && $request->hasFile('doc_files')) {
                foreach ($request->file('doc_files') as $index => $file) {
                    $path = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
                    if ($index === 0) $document->update(['file_path' => $path]);
                    
                    DocumentAttachment::create([
                        'document_id' => $document->id,
                        'file_path'    => $path,
                        'file_name'   => $file->getClientOriginalName(),
                        'file_type'   => $file->getMimeType()
                    ]);
                }
            }

            // --- HARD COPY LOGIC: SKIP MAPPING ---
            if ($isHardCopy) {
                if (!is_null($document->priority)) {
                    // Start sequence immediately
                    $document->update(['status' => 'pending']);
                    
                    // Notify first office in the chain
                    $firstOfficeId = $request->signatory_offices[0];
                    $staff = User::where('office_id', $firstOfficeId)->whereNotNull('email')->get();
                    foreach($staff as $person) {
                        try { Mail::to($person->email)->send(new UrgentDocumentAlert($document, false)); } catch(\Exception $e){}
                    }
                } else {
                    // Move to Assignment Queue for Records Office
                    $document->update(['status' => 'needs_review']);
                    
                    // Notify Records Office/Admins
                    $admins = User::where('role', 'superadmin')->orWhere('office_id', 'LIKE', '%-REC-%')->get();
                    foreach ($admins as $admin) {
                        if (!empty($admin->email)) {
                            try { Mail::to($admin->email)->send(new NeedsPriorityAlert($document)); } catch(\Exception $e){}
                        }
                    }
                }

                DocumentLog::create([
                    'document_id' => $document->id,
                    'user_id'     => Auth::id(),
                    'action'      => 'CREATED (HARD COPY)',
                    'office_id'   => Auth::user()->office_id,
                    'remarks'     => "Physical Item registration complete. Mapping phase skipped."
                ]);

                return redirect()->route('dashboard')->with('msg', 'Hard copy successfully registered.');
            }

            // --- SOFT COPY LOGIC: GO TO MAPPING ---
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => Auth::id(),
                'action'      => 'CREATED',
                'office_id'   => Auth::user()->office_id,
                'remarks'     => "Soft copy registration complete. Proceeding to signature mapping."
            ]);

            return redirect()->route('documents.map', $document->id);
        });
    }

    /**
     * SHOW: Main Document Tracking Hub
     */
    public function show($id)
    {
        // 1. Fetch document with all relations
    $document = Document::with([
        'signatories.user', 
        'signatories.office', // Add this
        'logs.user', 
        'logs.office'        // Add this
    ])->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

        $user = Auth::user();
        $isRecordsOffice = str_contains($user->office_id ?? '', '-REC-');
        
        // 2. Access Check: Creator, Records Office, or member of an assigned office in the chain
        $isAuthorizedOffice = $document->signatories->contains('office_id', $user->office_id);

        $isDisseminatedToMe = $document->logs()
            ->where('office_id', $user->office_id)
            ->where('action', 'DISSEMINATED')
            ->exists();

        if ($user->role !== 'superadmin' && !$isRecordsOffice && $document->uploader_id != $user->id && !$isAuthorizedOffice && !$isDisseminatedToMe) {
            abort(403, "Access Denied. You do not have permission to view this tracking record.");
        }

        // 3. Generate Public Receive Link in QR
        // Pointing to publicReceive allows users to confirm hard copy receipt without logging in
        $qrCode = QrCode::size(150)->color(128, 0, 0)->generate(route('documents.publicReceive', $document->tracking_id));

        return view('documents.view', compact('document', 'qrCode'));
    }
    

    /**
     * SIGN: Apply Digital Signature or Confirm Physical Receipt
     */
public function sign(Request $request, $id)
{
    $document = Document::where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();
    $signatory = Signatory::where('document_id', $document->id)
                          ->where('sign_order', $document->current_step)
                          ->firstOrFail();

    // Security: Only staff from the assigned office can take action
    if (Auth::user()->office_id !== $signatory->office_id) {
        return response()->json(['status' => 'error', 'message' => 'Your office is not authorized for this step.'], 403);
    }

    return DB::transaction(function () use ($request, $document, $signatory) {
        
        // --- PHASE 1: HARD COPY PHYSICAL RECEIPT ---
        // If it's a hard copy and the office hasn't confirmed they have the paper yet
        if ($document->is_hard_copy && !$signatory->is_physically_received) {
            $signatory->update([
                'is_physically_received' => true,
                'received_at' => now(), // Optional: if you added this column
            ]);

            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => Auth::id(),
                'action'      => 'HARD COPY RECEIVED',
                'office_id'   => Auth::user()->office_id,
                'remarks'     => Auth::user()->username . " from " . Auth::user()->office->office_name . " confirmed physical receipt of the hard copy."
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'Physical receipt confirmed. You can now affix the signature/stamp.'
            ]);
        }

        // --- PHASE 2: ACTUAL SIGNING / STAMPING ---
        // This runs for Soft Copies OR Hard Copies that have already been "Received"
        $signatory->update([
            'status' => 'signed',
            'user_id' => Auth::id(), 
            'signature_data' => $document->is_hard_copy ? 'PHYSICAL_STAMP_APPLIED' : $request->signature_data,
            'signed_at' => now(),
            'remarks' => $request->final_remarks
        ]);

        $nextSignatory = Signatory::where('document_id', $document->id)
                                  ->where('sign_order', $document->current_step + 1)
                                  ->first();

        $actionText = $document->is_hard_copy ? 'STAMPED & RELEASED' : 'DIGITAL SIGNATURE APPLIED';
        $logRemarks = Auth::user()->username . " from " . Auth::user()->office->office_name . " confirmed the record.";

        if ($nextSignatory) {
            // MOVE TO NEXT STEP
            $document->update([
                'current_step' => $document->current_step + 1, 
                'current_office_id' => $nextSignatory->office_id
            ]);

            // Notify next office
            $nextOfficeStaff = User::where('office_id', $nextSignatory->office_id)->whereNotNull('email')->get();
            foreach($nextOfficeStaff as $staff) {
                try { Mail::to($staff->email)->send(new UrgentDocumentAlert($document, false)); } catch(\Exception $e){}
            }
        } else {
            // FINAL STEP
            $document->update(['status' => 'accepted']);
            $actionText = 'FINALIZED & ARCHIVED';
            
            if ($request->filled('final_remarks')) {
                $logRemarks = "FINAL REMARKS: " . $request->final_remarks;
            }
        }

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => $actionText,
            'office_id'   => Auth::user()->office_id,
            'remarks'     => $logRemarks
        ]);

        return response()->json(['status' => 'success']);
    });
}
    /**
     * PUBLIC RECEIVE: Triggered by QR scan (No Login)
     */
public function publicReceive($trackingId)
{
    // 1. Fetch the document
    $document = Document::where('tracking_id', $trackingId)->firstOrFail();

    // 2. Security Check: Ensure user is logged in so we know WHICH office is receiving it
    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'Please login to confirm receipt via QR.');
    }

    $user = Auth::user();

    // 3. Status Check: Ensure document is actually in transit
    if (!in_array($document->status, ['pending', 'returned'])) {
        return view('documents.scan_status', ['msg' => 'This document is not in a trackable state.', 'type' => 'error']);
    }

    return DB::transaction(function () use ($document, $user) {
        // 4. Find the signatory record for the CURRENT step
        $currentSignatory = Signatory::where('document_id', $document->id)
            ->where('sign_order', $document->current_step)
            ->firstOrFail();

        // 5. Verification: Does the person scanning belong to the office assigned to this step?
        if ($user->office_id !== $currentSignatory->office_id) {
            return view('documents.scan_status', [
                'msg' => 'Unauthorized. This document is currently routed to the ' . ($currentSignatory->office->office_name ?? 'proper office') . ', not your office.',
                'type' => 'error'
            ]);
        }

        // 6. Update the signatory record (Assign the user who scanned it)
        $currentSignatory->update([
            'status' => 'signed', 
            'user_id' => $user->id, 
            'signature_data' => 'RECEIVED_VIA_QR_SCAN', 
            'signed_at' => now()
        ]);

        // 7. Advance to the next step
        $nextSigner = Signatory::where('document_id', $document->id)
            ->where('sign_order', '>', $document->current_step)
            ->orderBy('sign_order', 'asc')
            ->first();

        if ($nextSigner) {
            $document->update([
                'current_step' => $nextSigner->sign_order, 
                // FIX: Use the office_id from the nextSigner record, not a User search
                'current_office_id' => $nextSigner->office_id 
            ]);
        } else {
            // No more signers left
            $document->update(['status' => 'accepted']);
        }

        // 8. Log the transaction
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id' => $user->id, 
            'action' => 'RECEIVED VIA QR',
            'office_id' => $user->office_id,
            'remarks' => $user->username . " scanned the QR code and confirmed physical receipt."
        ]);

        return view('documents.scan_status', ['msg' => 'Receipt Confirmed!', 'type' => 'success', 'doc' => $document]);
    });
}

/**
 * PREVIEW: Render PDF with signatures and QR using FPDI
 */
public function previewWithSigs($id)
{
    ini_set('memory_limit', '1024M');
    $document = Document::with(['signatories.office', 'logs'])->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();
    
    $pdf = new Fpdi();
    $filePath = storage_path('app/public/' . $document->file_path);
    if (!file_exists($filePath)) abort(404);

    $pageCount = $pdf->setSourceFile($filePath);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);

    // --- 1. RENDER THE DRAGGABLE QR CODE ---
    if (floatval($document->qr_x) > 0 && intval($document->qr_page) == $pageNo) {
        $qrX = (floatval($document->qr_x) / 100) * $size['width'];
        $qrY = (floatval($document->qr_y) / 100) * $size['height'];
        
        try {
            // Generate the URL that the QR will point to
            $urlToEncode = route('documents.publicReceive', $document->tracking_id);

            // BULLETPR0OF FIX: Use a reliable API to get a PNG image of the QR code
            // This removes the need for 'imagick' or 'gd' extensions entirely
            $apiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&color=800000&data=" . urlencode($urlToEncode);
            
            $qrCodeData = file_get_contents($apiUrl);

            if ($qrCodeData) {
                $tempQrPath = storage_path('app/public/temp_qr_' . $document->id . '.png');
                file_put_contents($tempQrPath, $qrCodeData);
                
                // Place the QR on the PDF (18mm x 18mm)
                if (file_exists($tempQrPath)) {
                    $pdf->Image($tempQrPath, $qrX - 9, $qrY - 9, 18, 18);
                    unlink($tempQrPath); // Delete the temporary file
                }
            }
        } catch (\Exception $e) {
            \Log::error("QR API Fallback failed: " . $e->getMessage());
            // If internet is down, just draw a placeholder box so the system doesn't crash
            $pdf->SetDrawColor(128, 0, 0);
            $pdf->Rect($qrX - 9, $qrY - 9, 18, 18);
        }
    }

        // --- 2. RENDER SIGNATURE STAMPS ---
        foreach ($document->signatories as $sig) {
            if ($sig->x_pos && $sig->y_pos && intval($sig->page_num ?? 1) == $pageNo) {
                $x = ($sig->x_pos / 100) * $size['width'];
                $y = ($sig->y_pos / 100) * $size['height'];

                if ($sig->status == 'signed') {
                    $pdf->SetFillColor(255, 255, 255); $pdf->SetDrawColor(128, 0, 0); 
                    $pdf->Rect($x - 20, $y - 12, 40, 24, 'DF'); 

                    $pdf->SetTextColor(128, 0, 0); $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Text($x - 18, $y - 8, "RECEIVED");

                    $dateText = $sig->signed_at ? \Carbon\Carbon::parse($sig->signed_at)->format('M d, Y') : now()->format('M d, Y');
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->Text($x - 18, $y - 4, "DATE: " . $dateText);
                    
                    $offName = \Illuminate\Support\Str::limit($sig->office->office_name ?? 'OFFICE', 30);
                    $pdf->Text($x - 18, $y, "OFFICE: " . strtoupper($offName));

                    if ($sig->signature_data && !in_array($sig->signature_data, ['PHYSICAL_RECEIPT', 'RECEIVED_VIA_QR_SCAN'])) {
                        $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $sig->signature_data));
                        $tempSig = storage_path('app/public/temp_s_' . uniqid() . '.png');
                        file_put_contents($tempSig, $imgData);
                        $pdf->Image($tempSig, $x - 15, $y + 1, 30);
                        unlink($tempSig);
                    }
                }
            }
        }
    }
    return response($pdf->Output('S'), 200)->header('Content-Type', 'application/pdf');
}

public function saveQrTag(Request $request)
{
    $document = Document::findOrFail($request->doc_id);
    $document->update([
        'qr_x' => $request->x,
        'qr_y' => $request->y,
        'qr_page' => $request->page_num
    ]);
    return response()->json(['success' => true]);
}

    public function downloadFinal(Request $request, $id)
    {
        return $this->previewWithSigs($id);
    }

public function map($id)
{
    // We must load 'office' as well because 'user' will be null
    $document = Document::with(['signatories.user', 'signatories.office'])->findOrFail($id);
    return view('documents.map', compact('document'));
}

public function saveTag(Request $request)
{
    // Find by the ID of the signatory record (Primary Key)
    $sig = Signatory::find($request->signatory_id);
    
    if ($sig) {
        $sig->update([
            'x_pos' => $request->x,
            'y_pos' => $request->y,
            'page_num' => $request->page_num
        ]);
        return response()->json(['status' => 'OK']);
    }

    return response()->json(['status' => 'Error', 'message' => 'Signatory not found'], 404);
}

public function deleteTag(Request $request)
{
    $sig = Signatory::find($request->signatory_id);
    if ($sig) {
        $sig->update(['x_pos' => null, 'y_pos' => null, 'page_num' => null]);
        return response()->json(['status' => 'OK']);
    }
    return response()->json(['status' => 'Error'], 404);
}
    
public function return(Request $request, $id)
{
    $request->validate(['remarks' => 'required|string|max:500']);
    $document = Document::findOrFail($id);
    $user = Auth::user();

    // Possession check
    $currentSig = $document->signatories->where('sign_order', $document->current_step)->first();
    if (!$currentSig || $currentSig->office_id !== $user->office_id) {
        return response()->json(['message' => 'Unauthorized holder.'], 403);
    }

    return DB::transaction(function () use ($request, $document, $user) {
        $document->update(['status' => 'returned']);
        
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $user->id,
            'action'      => 'DOCUMENT RETURNED',
            'office_id'   => $user->office_id,
            'remarks'     => "REASON: " . $request->remarks
        ]);

        return response()->json(['status' => 'success'], 200); // CRITICAL: RETURN JSON
    });
}

    public function disseminate(Request $request, $id)
    {
        if (!str_contains(Auth::user()->office_id, '-REC-')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'office_ids' => 'required|array',
            'office_ids.*' => 'exists:offices,id'
        ]);

        $document = Document::findOrFail($id);

        return DB::transaction(function () use ($request, $document) {
            foreach ($request->office_ids as $officeId) {
                DocumentLog::create([
                    'document_id' => $document->id,
                    'user_id'     => Auth::id(),
                    'action'      => 'DISSEMINATED',
                    'office_id'   => $officeId,
                    'remarks'     => 'Officially shared with this office.'
                ]);
            }

            return back()->with('msg', 'Document disseminated successfully.');
        });
    }

    public function changeOwnPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Incorrect current password.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('msg', 'Password updated successfully!');
    }

    public function streamFile($id)
    {
        $document = Document::findOrFail($id);
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }
        return response()->file(storage_path('app/public/' . $document->file_path));
    }

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
            foreach ($document->attachments as $oldFile) {
                Storage::disk('public')->delete($oldFile->file_path);
                $oldFile->delete();
            }

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

            $document->update(['status' => 'pending', 'current_step' => 1]);
            $document->signatories()->update(['status' => 'pending', 'signed_at' => null, 'signature_data' => null]);

            $returnLog = $document->logs()->where('action', 'DOCUMENT RETURNED')->latest()->first();
            $reason = $returnLog ? $returnLog->remarks : 'Corrections requested.';

            $firstSigner = $document->signatories->where('sign_order', 1)->first();
            if ($firstSigner && !empty($firstSigner->user->email)) {
                try {
                    Mail::to($firstSigner->user->email)->send(new \App\Mail\UrgentDocumentAlert($document, true, $reason));
                } catch (\Exception $e) {
                    \Log::error("Resubmit mail failed: " . $e->getMessage());
                }
            }

            DocumentLog::create([
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'action' => 'RE-SUBMITTED',
                'office_id' => Auth::user()->office_id,
                'remarks' => 'Corrected document uploaded.'
            ]);

            return redirect()->route('documents.view', $document->tracking_id)->with('msg', 'Resubmitted successfully.');
        });
    }

    public function downloadQr($id)
    {
        $document = Document::where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();
        $qrCode = QrCode::size(500)->color(128, 0, 0)->margin(1)->generate(route('documents.view', $document->tracking_id));

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="QR_' . $document->id . '.svg"');
    }

    public function discard($id)
    {
        $document = Document::with(['signatories.user', 'attachments'])->findOrFail($id);
        if ($document->uploader_id !== Auth::id()) abort(403);

        $formData = [
            'classification' => $document->classification,
            'priority' => $document->priority,
            'is_hard_copy' => $document->is_hard_copy,
            'physical_description' => ($document->is_hard_copy) ? $document->title : null,
            'signatory_names' => $document->signatories->sortBy('sign_order')->pluck('user.username')->toArray()
        ];

        foreach ($document->attachments as $file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
        }

        $document->delete();

        return redirect()->route('documents.create')->withInput($formData);
    }

public function finalizeMapping($id)
{
    // Eager load signatories to check positions
    $document = Document::with(['signatories'])->findOrFail($id);
    
    // Check if at least one tag is saved in the database
    $hasTags = $document->signatories->contains(fn($sig) => !is_null($sig->x_pos));

    if (!$hasTags) {
        return redirect()->back()->with('error', 'Please place at least one signature tag.');
    }

    return DB::transaction(function () use ($document) {
        // If uploader is Admin/Records, they likely set priority already
        if (!is_null($document->priority)) {
            $document->update(['status' => 'pending']);
        } else {
            // If regular staff, it needs priority from Records
            $document->update(['status' => 'needs_review']);
        }

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'FINALIZED',
            'office_id'   => Auth::user()->office_id,
            'remarks'     => "Signature mapping completed."
        ]);

        return redirect()->route('dashboard')->with('msg', 'Document finalized.');
    });
}

    public function setPriority(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'superadmin' && !str_contains($user->office_id ?? '', '-REC-')) {
            abort(403);
        }

        $request->validate(['priority' => 'required|integer|in:1,2,3']);
        $document = Document::with('signatories.user')->findOrFail($id);
        $suffix = match ((int)$request->priority) { 3 => 'EXT', 2 => 'URG', default => 'NOR' };
        $newTrackingId = Str::replaceLast('REV', $suffix, $document->tracking_id);

        return DB::transaction(function () use ($request, $document, $newTrackingId) {
            $document->update([
                'priority' => $request->priority,
                'tracking_id' => $newTrackingId,
                'status' => 'pending'
            ]);

            $firstSigner = $document->signatories->where('sign_order', 1)->first();
            if ($firstSigner && !empty($firstSigner->user->email)) {
                try {
                    Mail::to($firstSigner->user->email)->send(new UrgentDocumentAlert($document, false));
                } catch (\Exception $e) {
                    \Log::error("Priority set mail failed: " . $e->getMessage());
                }
            }

            DocumentLog::create([
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'action' => 'PRIORITY ASSIGNED',
                'office_id' => Auth::user()->office_id,
                'remarks' => "Priority set to " . $request->priority
            ]);

            return redirect()->route('dashboard');
        });
    }

    public function revalidate(Request $request, $id)
    {
        $request->validate(['explanation' => 'required|string|max:1000']);
        $document = Document::with(['signatories.user', 'logs'])->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

        if ($document->uploader_id !== Auth::id()) abort(403);

        return DB::transaction(function () use ($request, $document) {
            $explanation = $request->explanation;
            $document->update(['status' => 'pending']);
            $currentSigner = $document->signatories->where('sign_order', $document->current_step)->first();

            if ($currentSigner && !empty($currentSigner->user->email)) {
                try {
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

            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => Auth::id(),
                'action'      => 'RE-VALIDATED',
                'office_id'   => Auth::user()->office_id,
                'remarks'     => "CREATOR EXPLANATION: " . $explanation
            ]);

            return redirect()->route('documents.view', $document->tracking_id)->with('msg', 'Revalidated.');
        });
    }

public function deleteQrTag(Request $request)
{
    $document = Document::findOrFail($request->doc_id);
    $document->update([
        'qr_x' => null,
        'qr_y' => null,
        'qr_page' => null
    ]);

    return response()->json(['success' => true]);
}
public function receiveHardCopy(Request $request, $tracking_id)
{
    $document = Document::where('tracking_id', $tracking_id)->firstOrFail();
    $user = Auth::user();

    // FIX: Check if any signatory record for this document matches the user's office
    // and is currently the active step.
    $isMyOfficeTurn = $document->signatories()
        ->where('office_id', $user->office_id)
        ->where('sign_order', $document->current_step)
        ->exists();

    if ($isMyOfficeTurn) {
        $document->logs()->create([
            'user_id' => $user->id,
            'office_id' => $user->office_id,
            'action' => 'HARD COPY RECEIVED',
            'remarks' => $user->username . ' physically received the hard copy at ' . $user->office->office_name
        ]);

        return response()->json(['success' => true, 'message' => 'Receipt acknowledged.']);
    }

    return response()->json(['success' => false, 'message' => 'Unauthorized. Your office is not the current holder.'], 403);
}
}