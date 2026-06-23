<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\Office;
use App\Models\Signatory;
use App\Models\User;
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

        if (count($request->signatory_names) !== count(array_unique($request->signatory_names))) {
            return back()->withInput()->with('error', 'Duplicate individuals found in the signing sequence.');
        }

        return DB::transaction(function () use ($request, $isHardCopy, $isAdminOrRecords) {
            $title = ($isHardCopy) ? $request->physical_description : (($request->classification === 'Others') ? $request->custom_title : $request->classification);

            if ($isAdminOrRecords && $request->filled('priority')) {
                $suffix = match ((int)$request->priority) { 3 => 'EXT', 2 => 'URG', default => 'NOR' };
            } else {
                $suffix = 'REV';
            }

            $trackingId = "ISPSC-" . now()->format('m/d/Y-H:i:s') . "-" . $suffix;

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

            foreach ($request->signatory_names as $index => $name) {
                $signer = User::where('username', $name)->first();
                if ($signer) {
                    Signatory::create([
                        'document_id' => $document->id,
                        'user_id'     => $signer->id,
                        'sign_order'  => $index + 1,
                        'status'      => 'pending'
                    ]);
                }
            }

            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => Auth::id(),
                'action'      => 'CREATED',
                'office_id'   => Auth::user()->office_id,
                'remarks'     => "Phase 1: Registration Complete. Document in tag-mapping state."
            ]);

            return redirect()->route('documents.map', $document->id);
        });
    }

    public function show($id)
    {
        $document = Document::with(['logs.user', 'signatories.user', 'uploader'])
            ->where('id', $id)
            ->orWhere('tracking_id', $id)
            ->firstOrFail();

        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'superadmin');
        $isRecordsOffice = str_contains($user->office_id ?? '', '-REC-');
        $isUploader = ($document->uploader_id == $user->id);
        $isSignatory = $document->signatories->contains('user_id', $user->id);

        $isDisseminatedToMe = $document->logs()
            ->where('office_id', $user->office_id)
            ->where('action', 'DISSEMINATED')
            ->exists();

        if (!$isSuperAdmin && !$isRecordsOffice && !$isUploader && !$isSignatory && !$isDisseminatedToMe) {
            abort(403, "Access Denied. You do not have permission to view this tracking record.");
        }

        $qrCode = QrCode::size(150)->color(128, 0, 0)->generate(route('documents.view', $document->tracking_id));

        return view('documents.view', compact('document', 'qrCode'));
    }

public function sign(Request $request, $id)
{
    // Find document by ID or Tracking ID
    $document = Document::where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

    // Find the specific signatory record for the current user and current step
    $signatory = Signatory::where('document_id', $document->id)
        ->where('user_id', Auth::id())
        ->where('sign_order', $document->current_step)
        ->firstOrFail();

    return DB::transaction(function () use ($request, $document, $signatory) {
        // 1. Mark the signatory as signed
        $signatory->update([
            'status' => 'signed',
            'signature_data' => $document->is_hard_copy ? 'PHYSICAL_RECEIPT' : $request->signature_data,
            'signed_at' => now()
        ]);

        // 2. Find the NEXT signatory in line (even if the numbers aren't exactly +1)
        $nextSigner = Signatory::with('user')
            ->where('document_id', $document->id)
            ->where('sign_order', '>', $document->current_step) // Look for the next highest number
            ->orderBy('sign_order', 'asc')
            ->first();

        if ($nextSigner && $nextSigner->user) {
            // MOVE TO NEXT PERSON
            $document->current_step = $nextSigner->sign_order;
            $document->current_office_id = $nextSigner->user->office_id;
            $document->status = 'pending'; // Ensure it stays pending
            $document->save(); // Use save() to bypass fillable issues for testing or ensure it's fillable

            // Send Email
            if (!empty($nextSigner->user->email)) {
                try {
                    Mail::to($nextSigner->user->email)->send(new UrgentDocumentAlert($document, false));
                } catch (\Exception $e) {
                    \Log::error("Sequential mail failed: " . $e->getMessage());
                }
            }
        } else {
            // NO ONE LEFT -> MARK AS ACCEPTED
            $document->status = 'accepted';
            $document->save();
        }

        // 3. Log the action
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
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $document = Document::with(['signatories.user', 'logs'])
            ->where('id', $id)->orWhere('tracking_id', $id)->firstOrFail();

        $user = Auth::user();
        $isDisseminatedToMe = $document->logs->where('office_id', $user->office_id)->where('action', 'DISSEMINATED')->count() > 0;
        $isCreator = ($document->uploader_id == $user->id);
        $isSignatory = $document->signatories->contains('user_id', $user->id);
        $isAdminOrRecords = ($user->role === 'superadmin' || str_contains($user->office_id ?? '', '-REC-'));

        if (!$isAdminOrRecords && !$isCreator && !$isSignatory && !$isDisseminatedToMe) {
            abort(403, "You do not have access to view this document.");
        }

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
                if ($sig->x_pos && $sig->y_pos && intval($sig->page_num ?? 1) == $pageNo) {
                    $x = ($sig->x_pos / 100) * $size['width'];
                    $y = ($sig->y_pos / 100) * $size['height'];

                    if ($sig->status == 'signed' && $sig->signature_data && $sig->signature_data !== 'PHYSICAL_RECEIPT') {
                        $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $sig->signature_data));
                        $tempPath = storage_path('app/public/temp_sig_' . uniqid() . '.png');
                        file_put_contents($tempPath, $imgData);
                        $pdf->Image($tempPath, $x - 15, $y - 10, 30);
                        unlink($tempPath);
                    } elseif ($sig->status == 'pending' || $sig->status == 'returned') {
                        $pdf->SetFillColor(255, 204, 0);
                        $pdf->SetDrawColor(128, 0, 0);
                        $pdf->SetLineWidth(0.4);
                        $pdf->Rect($x - 17, $y - 6, 34, 12, 'DF');
                        $pdf->SetTextColor(128, 0, 0);
                        $pdf->SetFont('Arial', 'B', 7);
                        $pdf->Text($x - 15, $y - 1, "SIGN HERE");
                        $pdf->SetFont('Arial', '', 5);
                        $pdf->Text($x - 15, $y + 3, substr(strtoupper($sig->user->username), 0, 25));
                    }
                }
            }
        }

        return response($pdf->Output('S'), 200)->header('Content-Type', 'application/pdf');
    }

    public function downloadFinal(Request $request, $id)
    {
        return $this->previewWithSigs($id);
    }

    public function map($id)
    {
        $document = Document::with('signatories.user')->findOrFail($id);
        return view('documents.map', compact('document'));
    }

    public function saveTag(Request $request)
    {
        Signatory::where('document_id', $request->doc_id)->where('user_id', $request->user_id)
            ->update(['x_pos' => $request->x, 'y_pos' => $request->y, 'page_num' => $request->page_num]);
        return response()->json(['status' => 'OK']);
    }

    public function return(Request $request, $id)
    {
        $request->validate(['remarks' => 'required|string|max:500']);
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

            return response()->json(['status' => 'success']);
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
            'target_office_id' => $document->target_office_id,
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
    // Eager load uploader to provide context in the email
    $document = Document::with(['signatories.user', 'uploader'])->findOrFail($id);
    
    // Safety check: ensure tags are placed
    if (!$document->signatories()->whereNotNull('x_pos')->exists()) {
        return back()->with('error', 'Please place at least one signature tag.');
    }

    return DB::transaction(function () use ($document) {
        if (!is_null($document->priority)) {
            // Scenario: Admin/Records uploaded it, priority already exists
            $document->update(['status' => 'pending']);
            $firstSigner = $document->signatories->where('sign_order', 1)->first();
            if ($firstSigner && !empty($firstSigner->user->email)) {
                try {
                    Mail::to($firstSigner->user->email)->send(new \App\Mail\UrgentDocumentAlert($document, false));
                } catch (\Exception $e) {
                    \Log::error("Finalize mail failed: " . $e->getMessage());
                }
            }
        } else {
            // Scenario: Regular staff uploaded it, needs priority from Records Office
            $document->update(['status' => 'needs_review']);

            // --- NOTIFY ADMIN/RECORDS VIA EMAIL ---
            // Finds Superadmins OR any staff in an office containing "-REC-"
            $admins = \App\Models\User::where('role', 'superadmin')
                        ->orWhere('office_id', 'LIKE', '%-REC-%')
                        ->get();

            foreach ($admins as $admin) {
                if (!empty($admin->email)) {
                    try {
                        Mail::to($admin->email)->send(new \App\Mail\NeedsPriorityAlert($document));
                    } catch (\Exception $e) {
                        \Log::error("Priority Assignment notification failed for {$admin->username}: " . $e->getMessage());
                    }
                }
            }
        }

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'FINALIZED',
            'office_id'   => Auth::user()->office_id,
            'remarks'     => "Signature mapping completed. Awaiting Priority Assignment from Records Office."
        ]);

        return redirect()->route('dashboard')->with('msg', 'Document finalized. Records Office staff have been notified via email.');
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
}