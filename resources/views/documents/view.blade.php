@extends('layouts.ispsc')

@section('title', 'Tracking: ' . $document->tracking_id)

@section('content')
@php
    $user = Auth::user();
    
    // Core Logic (UNCHANGED)
    $isAdminOrRecords = ($user->role === 'superadmin' || str_contains($user->office_id ?? '', '-REC-'));
    $userSig = $document->signatories->where('user_id', $user->id)->first();
    
    $isMyTurn = ($document->status == 'pending' && 
                 $userSig &&
                 $document->current_step == $userSig->sign_order && 
                 $userSig->status == 'pending');
    
    $isActuallyPhysical = ($document->is_hard_copy == 1 || $document->file_path == 'PHYSICAL_ITEM' || empty($document->file_path));

    $revalidationLog = $document->logs
        ->where('action', 'RE-VALIDATED')
        ->sortByDesc('created_at')
        ->first();

    $uploaderExplanation = null;
    if ($revalidationLog) {
        $uploaderExplanation = str_replace('CREATOR EXPLANATION: ', '', $revalidationLog->remarks);
    }
@endphp

<style>
    :root { 
        --ispsc-maroon: #800000; 
        --ispsc-yellow: #FFCC00;
        --ispsc-blue: #0056b3;
    }

    body { font-size: 13px; color: #333; }
    .view-wrapper-fluid { width: 100%; padding: 0 40px; }

    /* Theme-aligned Cards */
    .tracer-card { 
        background: #fff; border: 1px solid #e1e8ed; border-radius: 12px; 
        margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; 
    }
    .tracer-card-header { 
        padding: 15px 25px; border-bottom: 1px solid #f1f1f1; 
        display: flex; justify-content: space-between; align-items: center; background: #fff; 
    }
    .tracer-card-header h6 { margin: 0; font-weight: 800; color: #000; text-transform: uppercase; font-size: 13px; }

    /* Timeline Styling */
    .timeline-wrapper { overflow-x: auto; padding: 10px 0; -webkit-overflow-scrolling: touch; }
    .h-timeline { display: flex; justify-content: space-between; position: relative; margin: 20px 0; padding: 0 10px; min-width: 600px; }
    .h-timeline::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: #e1e8ed; z-index: 1; }
    .t-node { position: relative; z-index: 2; text-align: center; width: 100%; }
    .t-dot { width: 30px; height: 30px; background: #fff; border: 3px solid #e1e8ed; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 10px; }
    .t-node.active .t-dot { border-color: var(--ispsc-maroon); background: var(--ispsc-maroon); color: #fff; }
    .t-node.completed .t-dot { border-color: var(--ispsc-blue); background: var(--ispsc-blue); color: #fff; }
    .t-node.returned .t-dot { border-color: #dc3545; background: #dc3545; color: #fff; }
    .t-label { font-size: 11px; font-weight: 800; display: block; color: #333; text-transform: uppercase; }

    /* Typography & Buttons */
    .tracking-id-text { font-family: monospace; font-weight: 900; color: var(--ispsc-maroon); font-size: 1.5rem; }
    .btn-docu { border-radius: 8px; font-weight: 800; text-transform: uppercase; font-size: 12px; padding: 10px 20px; transition: 0.3s; border: none; }
    .btn-maroon { background: var(--ispsc-maroon); color: #fff; }
    .btn-maroon:hover { background: #600000; transform: translateY(-2px); }
    .btn-dark { background: #111; color: #fff; }
    
    #sig-canvas { touch-action: none; cursor: crosshair; background-color: #ffffff; border: 2px solid #e1e8ed; border-radius: 8px; }
    .bg-pro-grey { background-color: #525659; }

    /* MODAL BOUNDARY & TABLE FIXES */
    @media (min-width: 1200px) {
        .modal, .modal-backdrop { 
            left: var(--sidebar-width) !important; 
            width: calc(100% - var(--sidebar-width)) !important; 
        }
    }
    .audit-table { table-layout: fixed; min-width: 800px !important; }
    .audit-table td { 
        white-space: normal !important; 
        word-wrap: break-word !important; 
        overflow-wrap: break-word !important;
        vertical-align: top;
        padding: 15px 10px !important;
    }

    @media (max-width: 768px) { .view-wrapper-fluid { padding: 0 15px; } }
</style>

<div class="view-wrapper-fluid py-4 animate__animated animate__fadeIn">   
    
    <!-- SECTION 1: TOP STATUS BAR -->
    <div class="tracer-card mb-4">
        <div class="card-body p-0">
            <div class="row g-0 align-items-center">
                <div class="col-md-auto p-4 border-end text-center" style="min-width: 180px;">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">State</span>
                    @php
                        $statusColor = match($document->status) {
                            'accepted' => '#e6f4ea', 'returned' => '#fdf2f2', 'pending' => '#fff4e5', 'needs_review' => '#e7f1ff', default => '#f3f4f6'
                        };
                        $textColor = match($document->status) {
                            'accepted' => '#1e7e34', 'returned' => '#dc3545', 'pending' => '#d97706', 'needs_review' => '#0d6efd', default => '#374151'
                        };
                    @endphp
                    <span class="badge px-3 py-2" style="background-color: {{ $statusColor }}; color: {{ $textColor }}; border-radius: 6px; font-weight: 900; font-size: 12px;">
                        {{ strtoupper($document->status == 'needs_review' ? 'FOR REVIEW' : $document->status) }}
                    </span>
                </div>
                <div class="col p-4 bg-light bg-opacity-50">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Global Tracking ID</span>
                    <span class="tracking-id-text">{{ $document->tracking_id }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-lg-8">
            
            <!-- SECTION 2: TIMELINE -->
            <div class="tracer-card">
                <div class="tracer-card-header"><h6>Movement Timeline</h6></div>
                <div class="card-body px-4">
                    <div class="timeline-wrapper">
                        <div class="h-timeline">
                            <div class="t-node completed">
                                <div class="t-dot"><i class="fa fa-door-open"></i></div>
                                <span class="t-label">Origin</span>
                            </div>
                            @foreach($document->signatories->sortBy('sign_order') as $sig)
                                @php
                                    $isDone = ($sig->status == 'signed');
                                    $isCurrent = ($document->current_step == $sig->sign_order && $document->status == 'pending');
                                    $isRet = ($document->current_step == $sig->sign_order && $document->status == 'returned');
                                    $state = $isDone ? 'completed' : ($isRet ? 'returned' : ($isCurrent ? 'active' : ''));
                                @endphp
                                <div class="t-node {{ $state }}">
                                    <div class="t-dot"><i class="fa {{ $isDone ? 'fa-check' : ($isRet ? 'fa-undo' : ($isCurrent ? 'fa-spinner fa-spin' : 'fa-circle')) }}"></i></div>
                                    <span class="t-label text-truncate d-block px-2">{{ $sig->user->username }}</span>
                                </div>
                            @endforeach
                            <div class="t-node {{ $document->status == 'accepted' ? 'completed' : '' }}">
                                <div class="t-dot"><i class="fa fa-flag-checkered"></i></div>
                                <span class="t-label">Final</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3A. PRIORITY ASSIGNMENT (FOR RECORDS) --}}
            @if($document->status == 'needs_review' && $isAdminOrRecords)
                <div class="tracer-card border-primary">
                    <div class="card-body bg-light p-4">
                        <h6 class="fw-black text-primary mb-3">ACTION REQUIRED: SET PRIORITY</h6>
                        <form action="{{ route('documents.setPriority', $document->id) }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-8"><select name="priority" class="form-select fw-bold" required><option value="1">Normal</option><option value="2">Urgent</option><option value="3">Extremely Urgent</option></select></div>
                            <div class="col-md-4"><button type="submit" class="btn btn-primary btn-docu w-100">Confirm & Start</button></div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- 3B. RETURN HANDLING (FOR CREATOR) --}}
            @if($document->status == 'returned' && $document->uploader_id == Auth::id())
                <div class="tracer-card border-danger">
                    <div class="card-body p-4">
                        <div class="alert alert-danger bg-opacity-10 border-0 mb-4 p-3">
                            <h6 class="fw-black text-danger text-uppercase small mb-1">Correction Required:</h6>
                            <p class="mb-0 italic">"{{ $document->logs->where('action', 'DOCUMENT RETURNED')->last()->remarks ?? 'N/A' }}"</p>
                        </div>
                        <ul class="nav nav-pills mb-4 gap-2" role="tablist">
                            <li class="nav-item"><button class="btn btn-outline-danger btn-docu active" data-bs-toggle="tab" data-bs-target="#uploadTab">Re-upload Revised</button></li>
                            <li class="nav-item"><button class="btn btn-outline-dark btn-docu" data-bs-toggle="tab" data-bs-target="#disputeTab">Maintain Original</button></li>
                        </ul>
                        <div class="tab-content pt-2">
                            <div class="tab-pane fade show active" id="uploadTab">
                                <form action="{{ route('documents.resubmit', $document->tracking_id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="doc_files[]" class="form-control mb-3" required multiple>
                                    <button type="submit" class="btn btn-danger btn-docu w-100">Resubmit & Restart Sequence</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="disputeTab">
                                <form action="{{ route('documents.revalidate', $document->tracking_id) }}" method="POST">
                                    @csrf
                                    <textarea name="explanation" class="form-control mb-3" rows="3" placeholder="Explain your justification..." required></textarea>
                                    <button type="submit" class="btn btn-dark btn-docu w-100">Send Explanation to Signer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 3C. MAIN VIEWER -->
            <div class="tracer-card">
                <div class="tracer-card-header">
                    <h6><i class="fa {{ $isActuallyPhysical ? 'fa-box-open' : 'fa-file-pdf' }} me-2 text-maroon"></i> {{ strtoupper($document->title) }}</h6>
                    <div class="d-flex gap-2">
                        @if(!$isActuallyPhysical)
                            <a href="{{ route('documents.download', $document->tracking_id) }}" target="_blank" class="btn btn-dark btn-docu py-1 px-3">Fullscreen</a>
                            @if($document->status == 'accepted')
                                <button onclick="printIframe()" class="btn btn-maroon btn-docu py-1 px-3"><i class="fa fa-print"></i></button>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="card-body p-0 bg-pro-grey" style="height: 65vh; min-height: 400px;">
                    @if($isActuallyPhysical)
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-white p-5 text-center">
                            <i class="fa fa-box-archive fa-5x text-maroon opacity-10 mb-3 animate__animated animate__pulse animate__infinite"></i>
                            <h2 class="fw-black text-uppercase mb-2">Physical Item Tracking</h2>
                            <p class="text-muted small fw-bold">Manual receipt confirmation required below.</p>
                        </div>
                    @else
                        <iframe id="doc-iframe" src="{{ url('/document/live-preview/' . $document->id) }}#toolbar=0" width="100%" height="100%" style="border: none;"></iframe>
                    @endif
                </div>
            </div>

            <!-- 3D. ACTION PANEL (FOR SIGNATORIES) -->
            @if($isMyTurn)
                <div class="tracer-card border-0" style="background-color: #fffbeb; border-top: 5px solid var(--ispsc-yellow) !important;">
                    <div class="card-body p-4 bg-white bg-opacity-25">
                        <div class="text-center mb-4"><h6 class="fw-black text-uppercase m-0">Your Action is Required</h6></div>
                        @if($uploaderExplanation)
                            <div class="alert border-0 bg-white mb-4 shadow-sm" style="border-left: 5px solid var(--ispsc-blue) !important;">
                                <h6 class="fw-black text-primary text-uppercase small mb-1">Creator Note:</h6>
                                <p class="mb-0 italic small text-dark">"{{ $uploaderExplanation }}"</p>
                            </div>
                        @endif
                        <div class="d-flex gap-3">
                            @if($isActuallyPhysical)
                                <button class="btn btn-success flex-fill py-3 fw-black shadow-sm fs-6" id="btn-confirm-receipt">
                                    I HAVE RECEIVED THE HARD COPY <i class="fa fa-check-circle ms-2"></i>
                                </button>
                            @else
                                <button class="btn btn-success flex-fill py-3 fw-black shadow-sm fs-6" data-bs-toggle="modal" data-bs-target="#signatureModal">APPLY DIGITAL SIGNATURE</button>
                            @endif
                            <button class="btn btn-outline-danger px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#returnModal">RETURN</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="col-lg-4">
            <div class="tracer-card text-center p-4">
                <h6>Record QR Code</h6>
                <div class="bg-light p-3 rounded mb-3 d-inline-block border mt-3" id="printableQR">{!! $qrCode !!}</div>
                <button type="button" onclick="downloadQRAsPNG()" class="btn btn-outline-dark btn-docu w-100"><i class="fa fa-download me-2"></i> Download PNG</button>
            </div>

            <div class="tracer-card">
                <div class="tracer-card-header"><h6>Record Info</h6></div>
                <div class="card-body p-4">
                    <div class="mb-3 d-flex justify-content-between"><span class="text-muted fw-bold">Office:</span><span class="fw-bold text-end" style="max-width: 150px;">{{ $document->uploader->office->office_name ?? 'RECORDS' }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted fw-bold">Priority:</span><span class="small fw-bold text-maroon">Level {{ $document->priority ?? '0' }}</span></div>
                </div>
                <div class="card-footer bg-light border-0 p-3 d-flex flex-column gap-2">
                    <button class="btn btn-dark btn-docu w-100 py-2" data-bs-toggle="modal" data-bs-target="#trailModal">Audit Trail</button>
                    @if($document->status == 'accepted' && $isAdminOrRecords)
                        <button class="btn btn-maroon btn-docu w-100" data-bs-toggle="modal" data-bs-target="#disseminateModal">Share Copies</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AUDIT TRAIL MODAL (WITH RESPONSIVE FIX) -->
<div class="modal fade" id="trailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-black text-uppercase m-0" style="font-size: 12px; letter-spacing: 1px;">
                    <i class="fa fa-history me-2"></i> Document Audit Trail
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover audit-table align-middle mb-0 w-100">
                        <thead class="bg-light">
                            <tr class="small text-secondary fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <th class="ps-4 py-3" style="width: 180px;">Timestamp</th>
                                <th class="py-3" style="width: 160px;">Action</th>
                                <th class="py-3" style="width: 200px;">Personnel</th>
                                <th class="py-3">Remarks / Comments</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 13px; color: #333;">
                            @forelse($document->logs->sortByDesc('created_at') as $log)
                            <tr>
                                <td class="ps-4 text-muted fw-medium">
                                    {{ $log->created_at->format('M d, Y') }} 
                                    <span class="opacity-50 mx-1">|</span> 
                                    <span class="text-dark">{{ $log->created_at->format('h:i A') }}</span>
                                </td>
                                <td>
                                    @php
                                        $act = strtoupper($log->action);
                                        $color = match(true) {
                                            str_contains($act, 'SIGNATURE') || str_contains($act, 'RECEIVED') => 'text-success border-success',
                                            str_contains($act, 'RETURN') || str_contains($act, 'DISCARD') => 'text-danger border-danger',
                                            str_contains($act, 'FINALIZED') || str_contains($act, 'CREATED') => 'text-primary border-primary',
                                            default => 'text-dark border-secondary'
                                        };
                                    @endphp
                                    <span class="badge border {{ $color }} px-2 py-1" style="font-size: 9px; font-weight: 800;">{{ $act }}</span>
                                </td>
                                <td class="fw-bold text-dark">{{ $log->user->username }}</td>
                                <td class="text-muted" style="line-height: 1.5;">{{ $log->remarks ?? '---' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted fw-bold">No activity logs recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-secondary fw-bold px-5 rounded-pill shadow-sm" data-bs-dismiss="modal" style="font-size: 12px;">CLOSE</button>
            </div>
        </div>
    </div>
</div>
<!-- AUDIT TRAIL MODAL -->
<div class="modal fade" id="trailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-black text-uppercase m-0" style="font-size: 12px; letter-spacing: 1px;">
                    <i class="fa fa-history me-2"></i> Document Audit Trail
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover audit-table align-middle mb-0 w-100">
                        <thead class="bg-light">
                            <tr class="small text-secondary fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <th class="ps-4 py-3" style="width: 220px;">Timestamp</th>
                                <th class="py-3" style="width: 180px;">Action</th>
                                <th class="py-3" style="width: 200px;">Personnel</th>
                                <th class="py-3" style="min-width: 300px;">Remarks / Comments</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 13px; color: #333;">
                            @forelse($document->logs->sortByDesc('created_at') as $log)
                            <tr>
                                <td class="ps-4 text-muted fw-medium">
                                    {{ $log->created_at->format('M d, Y') }} 
                                    <span class="opacity-50 mx-1">|</span> 
                                    <span class="text-dark">{{ $log->created_at->format('h:i A') }}</span>
                                </td>
                                <td>
                                    @php
                                        $act = strtoupper($log->action);
                                        $color = match(true) {
                                            str_contains($act, 'SIGNATURE') || str_contains($act, 'RECEIVED') => 'text-success border-success',
                                            str_contains($act, 'RETURN') || str_contains($act, 'DISCARD') => 'text-danger border-danger',
                                            str_contains($act, 'FINALIZED') || str_contains($act, 'CREATED') => 'text-primary border-primary',
                                            default => 'text-dark border-secondary'
                                        };
                                    @endphp
                                    <span class="badge border {{ $color }} px-2 py-1" style="font-size: 10px; font-weight: 800;">{{ $act }}</span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <i class="fa fa-user-circle opacity-25 me-1"></i> {{ $log->user->username }}
                                </td>
                                <td class="text-muted" style="line-height: 1.5; word-break: break-word; white-space: normal;">
                                    {{ $log->remarks ?? '---' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted fw-bold">No activity logs recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-secondary fw-bold px-5 rounded-pill shadow-sm" data-bs-dismiss="modal" style="font-size: 12px;">CLOSE TRAIL</button>
            </div>
        </div>
    </div>
</div>

<!-- DISSEMINATE MODAL -->
<div class="modal fade" id="disseminateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('documents.disseminate', $document->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0">
                <div class="modal-header bg-dark text-white py-2"><h6 class="modal-title fw-black small text-uppercase">Share Copies</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-3" style="max-height: 400px; overflow-y: auto;">
                    @foreach(\App\Models\Office::where('id', '!=', Auth::user()->office_id)->orderBy('office_name','asc')->get() as $off)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="office_ids[]" value="{{ $off->id }}" id="o{{ $off->id }}">
                            <label class="form-check-label small fw-bold" for="o{{ $off->id }}">{{ $off->office_name }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer bg-light p-2"><button type="submit" class="btn btn-maroon btn-docu w-100 py-2">SEND OFFICIAL COPIES</button></div>
            </div>
        </form>
    </div>
</div>

<!-- SIGNATURE MODAL -->
<div class="modal fade" id="signatureModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2"><h5 class="modal-title fw-black small text-uppercase">Affix Signature</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center p-3">
                <ul class="nav nav-pills nav-justified mb-2 bg-light p-1 rounded-pill" role="tablist">
                    <li class="nav-item"><button class="nav-link active fw-bold small rounded-pill py-1" data-bs-toggle="pill" data-bs-target="#draw-tab">DRAW</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold small rounded-pill py-1" data-bs-toggle="pill" data-bs-target="#upload-tab">UPLOAD</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="draw-tab">
                        <canvas id="sig-canvas" class="border bg-white w-100 rounded" style="height: 180px;"></canvas>
                        <button type="button" class="btn btn-sm text-danger mt-1 fw-bold" id="clear-sig">CLEAR</button>
                    </div>
                    <div class="tab-pane fade" id="upload-tab">
                        <input type="file" id="sig-file" class="form-control form-control-sm" accept="image/*">
                        <img id="sig-preview" class="d-none border rounded w-100 mt-2">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 pt-0"><button type="button" class="btn btn-success w-100 py-2 fw-bold" id="btn-submit-signature">CONFIRM SIGNATURE</button></div>
        </div>
    </div>
</div>

<!-- RETURN MODAL -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0"><div class="modal-header bg-danger text-white py-2"><h5 class="modal-title fw-black small text-uppercase">Return Record</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-3"><label class="form-label fw-bold small">Reason:</label><textarea id="return-remarks" class="form-control form-control-sm" rows="3" placeholder="Specify reason..."></textarea></div><div class="modal-footer border-0 p-3 pt-0"><button type="button" class="btn btn-danger w-100 py-2 fw-bold" id="confirm-return">SUBMIT RETURN</button></div></div></div>
</div>

@endsection

@push('scripts')
<script>
    function printIframe() {
        const iframe = document.getElementById('doc-iframe');
        if (iframe) { iframe.contentWindow.focus(); iframe.contentWindow.print(); }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('sig-canvas');
        const ctx = canvas.getContext('2d');
        const submitBtn = document.getElementById('btn-submit-signature');
        const clearBtn = document.getElementById('clear-sig');
        const fileInput = document.getElementById('sig-file');
        const sigPreview = document.getElementById('sig-preview');
        let drawing = false; let hasSigned = false;

        function initCanvas() { 
            const rect = canvas.getBoundingClientRect(); 
            const dpr = window.devicePixelRatio || 1; 
            canvas.width = rect.width * dpr; 
            canvas.height = rect.height * dpr; 
            ctx.strokeStyle = "#000"; ctx.lineWidth = 2; ctx.lineCap = "round"; 
        }

        document.getElementById('signatureModal').addEventListener('shown.bs.modal', initCanvas);
        
        function getMousePos(e) { 
            const rect = canvas.getBoundingClientRect(); 
            const clientX = e.touches ? e.touches[0].clientX : e.clientX; 
            const clientY = e.touches ? e.touches[0].clientY : e.clientY; 
            return { x: clientX - rect.left, y: clientY - rect.top }; 
        }

        function startDrawing(e) { drawing = true; const pos = getMousePos(e); ctx.beginPath(); ctx.moveTo(pos.x, pos.y); e.preventDefault(); }
        function draw(e) { if (!drawing) return; const pos = getMousePos(e); ctx.lineTo(pos.x, pos.y); ctx.stroke(); hasSigned = true; e.preventDefault(); }
        function stopDrawing() { drawing = false; ctx.closePath(); }

        canvas.addEventListener('mousedown', startDrawing); canvas.addEventListener('mousemove', draw); canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('touchstart', startDrawing); canvas.addEventListener('touchmove', draw); canvas.addEventListener('touchend', stopDrawing);
        
        clearBtn.addEventListener('click', () => { ctx.clearRect(0, 0, canvas.width, canvas.height); hasSigned = false; });
        
        fileInput.addEventListener('change', (e) => { 
            const reader = new FileReader(); 
            reader.onload = (event) => { sigPreview.src = event.target.result; sigPreview.classList.remove('d-none'); hasSigned = true; }; 
            if(e.target.files[0]) reader.readAsDataURL(e.target.files[0]); 
        });

        submitBtn.addEventListener('click', function() { 
            if (!hasSigned) return Swal.fire('Error', 'Sign first.', 'error'); 
            const isDrawing = document.getElementById('draw-tab').classList.contains('active'); 
            const signatureData = isDrawing ? canvas.toDataURL('image/png') : sigPreview.src; 
            submitBtn.disabled = true;
            fetch("{{ route('documents.sign', $document->tracking_id) }}", { 
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" }, 
                body: JSON.stringify({ signature_data: signatureData }) 
            }).then(() => window.location.reload());
        });

        document.getElementById('confirm-return').addEventListener('click', function() { 
            const remarks = document.getElementById('return-remarks').value; 
            if (!remarks) return; 
            fetch("{{ route('documents.return', $document->tracking_id) }}", { 
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" }, 
                body: JSON.stringify({ remarks: remarks }) 
            }).then(() => window.location.reload()); 
        });
    });

    function downloadQRAsPNG() { 
        const svgElement = document.querySelector('#printableQR svg'); 
        const svgData = new XMLSerializer().serializeToString(svgElement); 
        const canvas = document.createElement("canvas"); canvas.width = 800; canvas.height = 800;
        const img = new Image(); img.onload = function() { 
            canvas.getContext("2d").fillStyle = "white"; 
            canvas.getContext("2d").fillRect(0, 0, 800, 800); 
            canvas.getContext("2d").drawImage(img, 0, 0, 800, 800); 
            const a = document.createElement("a"); a.href = canvas.toDataURL("image/png"); a.download = "QR.png"; a.click(); 
        }; img.src = 'data:image/svg+xml;base64,' + btoa(svgData); 
    }
</script>
@endpush