@extends('layouts.ispsc')

@section('title', 'Tracking: ' . $document->tracking_id)

@section('content')
@php
    $user = Auth::user();
    
    // Core Security & Role Logic
    $isAdminOrRecords = ($user->role === 'superadmin' || str_contains($user->office_id ?? '', '-REC-'));
    $userSig = $document->signatories->where('user_id', $user->id)->first();
    
    $isMyTurn = ($document->status == 'pending' && 
                 $userSig &&
                 $document->current_step == $userSig->sign_order && 
                 $userSig->status == 'pending');
    
    $isActuallyPhysical = ($document->is_hard_copy == 1 || $document->file_path == 'PHYSICAL_ITEM' || empty($document->file_path));

    // --- FIX FOR THE ERROR START ---
    // Fetch the latest explanation if this was maintained as original by the creator
    $revalidationLog = $document->logs
        ->where('action', 'RE-VALIDATED')
        ->sortByDesc('created_at')
        ->first();

    $uploaderExplanation = null;
    if ($revalidationLog) {
        // Strip prefix if you used it in the controller, otherwise just show remarks
        $uploaderExplanation = str_replace('CREATOR EXPLANATION: ', '', $revalidationLog->remarks);
    }
    // --- FIX FOR THE ERROR END ---
@endphp

<style>
    #sig-canvas { touch-action: none; cursor: crosshair; background-color: #ffffff; }
    .view-wrapper-fluid { width: 100% !important; max-width: 100% !important; padding: 0 15px; }

    /* TIMELINE STYLING */
    .journey-v-timeline { position: relative; padding-left: 50px; margin-top: 15px; }
    .journey-v-timeline::before {
        content: ''; position: absolute; left: 23px; top: 0; bottom: 0;
        width: 2px; background: #dee2e6; z-index: 1;
    }
    .j-item { position: relative; margin-bottom: 30px; z-index: 2; }
    .j-icon {
        position: absolute; left: -43px; width: 36px; height: 36px;
        background: #fff; border: 3px solid #dee2e6; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; color: #adb5bd; transition: all 0.3s ease;
    }
    .j-item.completed .j-icon { background: #800000; border-color: #800000; color: #fff; }
    .j-item.active .j-icon { 
        background: #ffc107; border-color: #800000; color: #000; 
        box-shadow: 0 0 10px rgba(255,193,7,0.5); 
    }
    .j-item.returned .j-icon { background: #dc3545; border-color: #dc3545; color: #fff; }
    
    .text-maroon { color: #800000 !important; }
    .bg-maroon { background-color: #800000 !important; color: white !important; }
    .bg-pro-grey { background-color: #525659; }
    .letter-spacing-1 { letter-spacing: 1px; }
</style>

<div class="view-wrapper-fluid py-3 animate__animated animate__fadeIn">   
    
    <!-- SECTION 1: TOP STATUS BAR -->
    <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0 align-items-center">
                <div class="col-md-auto bg-white p-3 border-end px-4 text-center">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Status</span>
                    @php
                        $statusColor = match($document->status) {
                            'accepted' => '#198754',
                            'returned' => '#dc3545',
                            'pending' => '#ffc107',
                            'needs_review' => '#0d6efd',
                            default => '#6c757d'
                        };
                        $displayText = ($document->status == 'needs_review') ? 'FOR REVIEW' : $document->status;
                    @endphp
                    <span class="badge px-4 py-2" style="background-color: {{ $statusColor }}; color: {{ ($document->status == 'pending' || $document->status == 'needs_review') ? '#000' : '#fff' }}; border-radius: 50px; font-size: 0.9rem; font-weight: 800;">
                        {{ strtoupper($displayText) }}
                    </span>
                </div>
                <div class="col p-3 px-4 bg-light bg-opacity-50">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Global Tracking ID</span>
                    <span class="text-maroon fw-bold font-monospace fs-4">{{ $document->tracking_id }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- SECTION 2: LEFT SIDEBAR (Creator & Journey) -->
        <div class="col-xl-3 col-lg-4">
            <!-- Creator Info -->
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-header bg-maroon py-2 text-center text-white">
                    <h6 class="mb-0 small fw-bold text-uppercase">Document Creator</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-maroon bg-opacity-10 text-maroon rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fa fa-user-tie fs-5"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 fw-bold text-dark">{{ $document->uploader->username }}</h6>
                            <small class="text-muted">{{ $document->uploader->office->office_name ?? 'RECORDS' }}</small>
                        </div>
                    </div>
                    <div class="mt-3 d-grid gap-2">
                        <button class="btn btn-dark btn-sm fw-bold py-2" data-bs-toggle="modal" data-bs-target="#trailModal"><i class="fa fa-history me-1"></i> VIEW AUDIT TRAIL</button>
                        <button class="btn btn-outline-dark btn-sm fw-bold py-2" data-bs-toggle="modal" data-bs-target="#qrModal"><i class="fa fa-qrcode me-1"></i> TRACKING QR CODE</button>
                        
                        @if($document->status == 'accepted' && $isAdminOrRecords)
                            <button class="btn btn-maroon btn-sm fw-bold py-2" data-bs-toggle="modal" data-bs-target="#disseminateModal">
                                <i class="fa fa-share-nodes me-1"></i> SHARE OFFICIAL COPIES
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-2">
                    <h6 class="fw-bold mb-0 text-dark small text-uppercase">Journey Status</h6>
                </div>
                <div class="card-body p-3">
                    <div class="journey-v-timeline">
                        <div class="j-item completed">
                            <div class="j-icon"><i class="fa fa-door-open"></i></div>
                            <div class="j-content">
                                <p class="fw-bold mb-0 small text-uppercase">Document Created</p>
                                <small class="text-muted">{{ $document->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>

                        @foreach($document->signatories->sortBy('sign_order') as $sig)
                            @php
                                $isDone = ($sig->status == 'signed');
                                $isCurrent = ($document->current_step == $sig->sign_order && $document->status == 'pending');
                                $isReturned = ($document->current_step == $sig->sign_order && $document->status == 'returned');
                                $itemClass = $isDone ? 'completed' : ($isReturned ? 'returned' : ($isCurrent ? 'active' : ''));
                            @endphp
                            <div class="j-item {{ $itemClass }}">
                                <div class="j-icon"><i class="fa {{ $isDone ? 'fa-check' : ($isReturned ? 'fa-rotate-left' : ($isCurrent ? 'fa-spinner fa-spin' : 'fa-circle')) }}"></i></div>
                                <div class="j-content">
                                    <p class="fw-bold mb-0 small text-uppercase {{ $isCurrent ? 'text-maroon' : 'text-dark' }}">{{ $sig->user->username }}</p>
                                    <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.6rem;">
                                        <i class="fa fa-id-badge me-1"></i> {{ strtoupper($sig->user->role_title ?? 'Authorized Personnel') }}
                                    </small>

                                    @if($isDone)
                                        <small class="text-success d-block fw-bold" style="font-size: 0.65rem;">SIGNED: {{ \Carbon\Carbon::parse($sig->signed_at)->format('M d, H:i') }}</small>
                                    @elseif($isCurrent)
                                        <small class="text-primary fw-bold" style="font-size: 0.65rem;">AWAITING ACTION</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="j-item {{ $document->status == 'accepted' ? 'completed' : '' }}">
                            <div class="j-icon"><i class="fa fa-flag-checkered"></i></div>
                            <div class="j-content"><p class="fw-bold mb-0 small text-uppercase">Process Finished</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: RIGHT CONTENT AREA (Viewer & Actions) -->
        <div class="col-xl-9 col-lg-8">
            
            {{-- 3A. PRIORITY ASSIGNMENT (ADMIN/RECORDS ONLY) --}}
            @if($document->status == 'needs_review' && $isAdminOrRecords)
                <div class="card border-primary shadow-sm mb-3 overflow-hidden">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0 fw-bold small text-uppercase"><i class="fa fa-tasks me-2"></i> Action Required: Priority Assignment</h6>
                    </div>
                    <div class="card-body bg-light p-4">
                        <p class="mb-3 text-dark">Document ready for sequence. Please assign the appropriate priority level.</p>
                        <form action="{{ route('documents.setPriority', $document->id) }}" method="POST" class="row g-3 align-items-center">
                            @csrf
                            <div class="col-md-6">
                                <select name="priority" class="form-select border-primary fw-bold" required>
                                    <option value="" selected disabled>Select priority...</option>
                                    <option value="1">Normal (NOR)</option>
                                    <option value="2">Urgent (URG)</option>
                                    <option value="3">Extremely Urgent (EXT)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary w-100 fw-black">CONFIRM & COMMENCE TRACKING <i class="fa fa-arrow-right ms-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- 3B. RETURN HANDLING (CREATOR ONLY) --}}
            @if($document->status == 'returned' && $document->uploader_id == Auth::id())
                <div class="card border-danger shadow-sm mb-3 overflow-hidden">
                    <div class="card-header bg-danger text-white py-2">
                        <h6 class="mb-0 fw-bold small text-uppercase">Correction Required</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-light border mb-4">
                            <h6 class="fw-bold text-danger">Return Reason:</h6>
                            <p class="mb-0 italic text-dark">"{{ $document->logs->where('action', 'DOCUMENT RETURNED')->last()->remarks ?? 'N/A' }}"</p>
                        </div>

                        <ul class="nav nav-tabs mb-3" id="returnTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#uploadTab">RE-UPLOAD REVISED</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#disputeTab">MAINTAIN ORIGINAL</button>
                            </li>
                        </ul>

                        <div class="tab-content border p-3 rounded bg-white">
                            <div class="tab-pane fade show active" id="uploadTab">
                                <form action="{{ route('documents.resubmit', $document->tracking_id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label class="form-label small fw-bold">Attach New File(s):</label>
                                    <input type="file" name="doc_files[]" class="form-control mb-3" required multiple>
                                    <button type="submit" class="btn btn-danger w-100 fw-bold">RESUBMIT & RESTART SEQUENCE</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="disputeTab">
                                <form action="{{ route('documents.revalidate', $document->tracking_id) }}" method="POST">
                                    @csrf
                                    <label class="form-label small fw-bold">Explain justification:</label>
                                    <textarea name="explanation" class="form-control mb-3" rows="3" placeholder="Explain why the current document is correct..." required></textarea>
                                    <button type="submit" class="btn btn-dark w-100 fw-bold">SEND BACK TO SIGNER WITH EXPLANATION</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 3C. MAIN DOCUMENT VIEWER -->
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-3">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 50%;">
                        <i class="fa {{ $isActuallyPhysical ? 'fa-box' : 'fa-file-pdf' }} me-2 text-maroon"></i>
                        {{ strtoupper($document->title) }}
                    </h6>
                    
                    <div class="d-flex gap-2">
                        @if(!$isActuallyPhysical)
                            <a href="{{ route('documents.download', $document->tracking_id) }}" target="_blank" class="btn btn-sm btn-outline-dark fw-bold">
                                <i class="fa fa-expand me-1"></i> FULLSCREEN
                            </a>

                            @if($document->status == 'accepted')
                                <a href="{{ route('documents.download', $document->tracking_id) }}?download=1" class="btn btn-sm btn-maroon fw-bold px-3">
                                    <i class="fa fa-download me-1"></i> DOWNLOAD FINAL PDF
                                </a>
                                <button onclick="printIframe()" class="btn btn-sm btn-dark fw-bold px-3">
                                    <i class="fa fa-print me-1"></i> PRINT
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="card-body p-0 bg-pro-grey" style="height: 70vh;">
                    @if($isActuallyPhysical)
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-white text-center p-5">
                            <i class="fa fa-box-open fa-5x text-maroon opacity-10 mb-4"></i>
                            <h2 class="fw-black text-dark text-uppercase mb-2">Physical Hard Copy</h2>
                            <p class="text-muted small">Digital signatures replaced with receipt confirmation.</p>
                        </div>
                    @else
                        <iframe id="doc-iframe" src="{{ url('/document/live-preview/' . $document->id) }}#toolbar=0" width="100%" height="100%" style="border: none;"></iframe>
                    @endif
                </div>
            </div>

            <!-- 3D. ACTION BUTTONS (SIGNATORY ONLY) -->
            @if($isMyTurn)
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-top border-warning border-5 animate__animated animate__pulse">
                    @if($uploaderExplanation)
                        <div class="alert shadow-sm border-0 mb-4" style="background-color: #f0f7ff; border-left: 5px solid #0056b3 !important;">
                            <div class="d-flex align-items-center">
                                <div class="p-2 rounded-circle bg-white text-primary border me-3"><i class="fa fa-info-circle fs-4"></i></div>
                                <div>
                                    <h6 class="mb-1 fw-black text-primary text-uppercase" style="letter-spacing: 0.5px;">Creator Disputed Return Reason</h6>
                                    <p class="mb-0 text-dark italic">"{{ $uploaderExplanation }}"</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($isActuallyPhysical)
                        <div class="text-center py-2">
                            <h4 class="fw-bold mb-3 text-dark">CONFIRM PHYSICAL RECEIPT</h4>
                            <button class="btn btn-success btn-lg fw-bold w-100 py-3 shadow-sm" id="btn-confirm-receipt">
                                I HAVE RECEIVED THIS ITEM <i class="fa fa-check-circle ms-2"></i>
                            </button>
                        </div>
                    @else
                        <div class="text-center mb-3">
                            <h5 class="fw-bold text-dark text-uppercase small mb-1">Your Action is Required</h5>
                            <p class="text-muted small">Verify details and apply digital signature.</p>
                        </div>
                        <div class="d-flex gap-3">
                            <button class="btn btn-success flex-fill py-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#signatureModal">SIGN DOCUMENT NOW</button>
                            <button class="btn btn-outline-danger px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#returnModal">RETURN</button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODALS BLOCK --}}

<!-- AUDIT TRAIL MODAL -->
<div class="modal fade" id="trailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold small text-uppercase ls-1">Document Audit Trail</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small">
                        <tr><th class="ps-4">TIMESTAMP</th><th>ACTION</th><th>PERSONNEL</th><th>REMARKS</th></tr>
                    </thead>
                    <tbody style="font-size: 0.85rem;">
                        @foreach($document->logs->sortByDesc('created_at') as $log)
                            @php
                                $rawAct = strtoupper($log->action);
                                $dispAct = ($rawAct === 'TIME OF HELLO' || $rawAct === 'CREATED') ? 'REGISTRATION' : (($isActuallyPhysical && $rawAct === 'DIGITAL SIGNATURE APPLIED') ? 'RECEIVED' : $rawAct);
                            @endphp
                            <tr>
                                <td class="ps-4 text-muted">{{ $log->created_at->format('M d, Y | h:i A') }}</td>
                                <td><span class="badge border text-dark">{{ $dispAct }}</span></td>
                                <td class="fw-bold">{{ $log->user->username }}</td>
                                <td class="small text-muted italic">{{ $log->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DISSEMINATE MODAL -->
<div class="modal fade" id="disseminateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('documents.disseminate', $document->id) }}" method="POST">
            @csrf
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-maroon text-white py-3">
                    <h5 class="modal-title fw-bold small text-uppercase">Share Official Copies</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    @foreach(\App\Models\Office::where('id', '!=', Auth::user()->office_id)->orderBy('office_name','asc')->get() as $off)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="office_ids[]" value="{{ $off->id }}" id="o{{ $off->id }}">
                            <label class="form-check-label small fw-bold" for="o{{ $off->id }}">{{ $off->office_name }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-maroon w-100 fw-bold py-2 shadow">OFFICIALLY DISSEMINATE</button></div>
            </div>
        </form>
    </div>
</div>

<!-- SIGNATURE MODAL -->
<div class="modal fade" id="signatureModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3"><h5 class="modal-title fw-bold small text-uppercase">Apply Digital Signature</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center p-4">
                <ul class="nav nav-pills nav-justified mb-3 bg-light p-1 rounded" role="tablist">
                    <li class="nav-item"><button class="nav-link active fw-bold small" data-bs-toggle="pill" data-bs-target="#draw-tab">DRAW</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold small" data-bs-toggle="pill" data-bs-target="#upload-tab">UPLOAD</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="draw-tab">
                        <canvas id="sig-canvas" class="border bg-white w-100 rounded" style="height: 200px; cursor: crosshair;"></canvas>
                        <button type="button" class="btn btn-sm text-danger mt-2 fw-bold" id="clear-sig"><i class="fa fa-eraser me-1"></i> CLEAR PAD</button>
                    </div>
                    <div class="tab-pane fade" id="upload-tab">
                        <input type="file" id="sig-file" class="form-control" accept="image/*">
                        <img id="sig-preview" class="d-none border rounded w-100 mt-2 shadow-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0"><button type="button" class="btn btn-success w-100 fw-black py-3 shadow-lg" id="btn-submit-signature">AFFIX SIGNATURE & PROCEED</button></div>
        </div>
    </div>
</div>

<!-- RETURN MODAL -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3"><h5 class="modal-title fw-bold small text-uppercase">Return for Correction</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4"><label class="form-label fw-bold small">Reason:</label><textarea id="return-remarks" class="form-control border-danger shadow-sm" rows="4" placeholder="Specify clearly why..."></textarea></div>
            <div class="modal-footer border-0 p-4 pt-0"><button type="button" class="btn btn-danger w-100 fw-bold py-2 shadow" id="confirm-return">NOTIFY CREATOR & RETURN</button></div>
        </div>
    </div>
</div>

<!-- QR MODAL -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold small text-uppercase">Tracking QR</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4" id="printableQR">
                {{-- Ensure the wrapper has a background for better PNG quality --}}
                <div class="p-3 bg-white d-inline-block border mb-3 rounded shadow-sm">
                    {!! $qrCode !!}
                </div>
                <h6 class="text-maroon fw-bold font-monospace small mb-0">{{ $document->tracking_id }}</h6>
            </div>
            <div class="modal-footer p-2 border-0">
                {{-- NEW: Trigger JavaScript PNG Download --}}
                <button type="button" onclick="downloadQRAsPNG()" class="btn btn-maroon btn-sm w-100 py-2 fw-bold shadow-sm">
                    <i class="fa fa-download me-1"></i> DOWNLOAD PNG
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('sig-canvas');
    const ctx = canvas.getContext('2d');
    const submitBtn = document.getElementById('btn-submit-signature');
    const clearBtn = document.getElementById('clear-sig');
    const receiptBtn = document.getElementById('btn-confirm-receipt');
    const fileInput = document.getElementById('sig-file');
    const sigPreview = document.getElementById('sig-preview');
    
    let drawing = false;
    let hasSigned = false;

    // --- BUG FIX: DPI / Resolution scaling for Canvas ---
    function initCanvas() {
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);
        ctx.strokeStyle = "#000000";
        ctx.lineWidth = 2;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
    }

    // Trigger resize when modal opens or tab switches
    document.getElementById('signatureModal').addEventListener('shown.bs.modal', initCanvas);
    document.querySelector('button[data-bs-target="#draw-tab"]').addEventListener('shown.bs.tab', initCanvas);

    // --- Drawing Coordinates Logic ---
    function getMousePos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: clientX - rect.left, y: clientY - rect.top };
    }

    function startDrawing(e) {
        drawing = true;
        const pos = getMousePos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!drawing) return;
        const pos = getMousePos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        hasSigned = true;
        e.preventDefault();
    }

    function stopDrawing() {
        drawing = false;
        ctx.closePath();
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);

    // Clear Pad
    clearBtn.addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasSigned = false;
    });

    // --- BUG FIX: File input listener to populate preview ---
    fileInput.addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = (event) => {
            sigPreview.src = event.target.result;
            sigPreview.classList.remove('d-none');
            hasSigned = true; 
        };
        if(e.target.files[0]) reader.readAsDataURL(e.target.files[0]);
    });

    // Handle Signature Submission (Digital)
    submitBtn.addEventListener('click', function() {
        if (!hasSigned) return Swal.fire('Error', 'Please provide a signature first.', 'error');

        const isDrawing = document.getElementById('draw-tab').classList.contains('active');
        const signatureData = isDrawing ? canvas.toDataURL('image/png') : sigPreview.src;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

        performSignAction(signatureData);
    });

    // --- BUG FIX: Physical Receipt Confirmation Listener ---
    if(receiptBtn) {
        receiptBtn.addEventListener('click', function() {
            receiptBtn.disabled = true;
            receiptBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Confirming...';
            performSignAction('PHYSICAL_RECEIPT');
        });
    }

    function performSignAction(data) {
        fetch("{{ route('documents.sign', $document->tracking_id) }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ signature_data: data })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ title: 'Success!', text: 'Action recorded successfully.', icon: 'success', timer: 2000, showConfirmButton: false })
                .then(() => window.location.reload());
            } else { throw new Error(); }
        })
        .catch(() => {
            Swal.fire('Error', 'Transaction failed.', 'error');
            if(receiptBtn) { receiptBtn.disabled = false; receiptBtn.innerHTML = 'I HAVE RECEIVED THIS ITEM'; }
            submitBtn.disabled = false; submitBtn.innerHTML = 'AFFIX SIGNATURE & PROCEED';
        });
    }

    // Return logic
    document.getElementById('confirm-return').addEventListener('click', function() {
        const remarks = document.getElementById('return-remarks').value;
        if (!remarks) return Swal.fire('Notice', 'Please provide a reason.', 'info');

        fetch("{{ route('documents.return', $document->tracking_id) }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ remarks: remarks })
        }).then(() => window.location.reload());
    });
});

function downloadQRAsPNG() {
    // 1. Get the SVG element from the modal
    const svgElement = document.querySelector('#printableQR svg');
    if (!svgElement) return;

    // 2. Serialize the SVG and create a standard image URL
    const svgData = new XMLSerializer().serializeToString(svgElement);
    const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
    const url = URL.createObjectURL(svgBlob);

    // 3. Setup Canvas (using a high scale for better quality)
    const canvas = document.createElement("canvas");
    const img = new Image();
    
    // Quality scaling: 1000x1000 makes a crisp print
    canvas.width = 1000;
    canvas.height = 1000;
    const ctx = canvas.getContext("2d");

    img.onload = function() {
        // A. Draw a White Background first (IMPORTANT for scanning)
        ctx.fillStyle = "white";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // B. Draw the QR image on top
        ctx.drawImage(img, 0, 0, 1000, 1000);

        // C. Trigger PNG Download
        const pngUrl = canvas.toDataURL("image/png");
        const downloadLink = document.createElement("a");
        
        // Dynamic Filename using tracking ID
        const filename = "QR_{{ $document->tracking_id }}".replace(/[\/\\?%*:|"<>\s]/g, '-');
        
        downloadLink.href = pngUrl;
        downloadLink.download = `${filename}.png`;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        
        // Clean up
        URL.revokeObjectURL(url);
    };

    img.src = url;
}
</script>
@endpush