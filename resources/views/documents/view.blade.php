@extends('layouts.ispsc')

@section('title', 'Document Tracking Hub')

@section('content')
@php
    $userSig = $document->signatories->where('user_id', Auth::id())->first();
    $isMyTurn = ($document->status == 'pending' && 
                 $userSig &&
                 $document->current_step == $userSig->sign_order && 
                 $userSig->status == 'pending');
    
    $isActuallyPhysical = ($document->is_hard_copy == 1 || $document->file_path == 'PHYSICAL_ITEM' || empty($document->file_path));
@endphp

<style>
    /* 1. LAYOUT FLUIDITY - Force full width */
    .main-content-wrapper { 
        width: 100% !important; 
        max-width: 100% !important; 
        padding: 0 15px;
    }

    /* 2. JOURNEY STATUS FIX - Precise Alignment */
    .journey-v-timeline {
        position: relative;
        padding-left: 40px; /* Space for line and icons */
        margin-top: 10px;
    }
    .journey-v-timeline::before {
        content: '';
        position: absolute;
        left: 14px; /* Exact center for the vertical line */
        top: 5px;
        bottom: 5px;
        width: 2px;
        background: #e9ecef;
        z-index: 1;
    }
    .j-item {
        position: relative;
        margin-bottom: 25px;
        z-index: 2;
    }
    .j-icon {
        position: absolute;
        left: -40px; /* Pulls icon into the padding area */
        width: 30px;
        height: 30px;
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #adb5bd;
        transition: all 0.3s ease;
    }
    .j-item.completed .j-icon { background: #800000; border-color: #800000; color: #fff; }
    .j-item.active .j-icon { 
        background: #ffc107; border-color: #800000; color: #000; 
        box-shadow: 0 0 10px rgba(255,193,7,0.5); 
    }
    .j-item.returned .j-icon { background: #dc3545; border-color: #dc3545; color: #fff; }
    
    .j-content { padding-top: 3px; }

    /* Utility */
    .text-maroon { color: #800000 !important; }
    .bg-maroon { background-color: #800000 !important; }
</style>

<div class="main-content-wrapper py-3">   
    
    <!-- TOP STATUS RIBBON (Full Width) -->
    <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0 align-items-center">
                <div class="col-md-auto bg-white p-3 border-end px-4 text-center">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Current Status</span>
                    @php
                        $statusColor = match($document->status) {
                            'accepted' => '#198754',
                            'returned' => '#dc3545',
                            'pending' => '#ffc107',
                            default => '#6c757d'
                        };
                    @endphp
                    <span class="badge px-4 py-2 shadow-sm" style="background-color: {{ $statusColor }}; color: {{ $document->status == 'pending' ? '#000' : '#fff' }}; border-radius: 50px;">
                        {{ strtoupper($document->status) }}
                    </span>
                </div>
                <div class="col p-3 px-4 bg-light bg-opacity-50">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Tracking ID</span>
                    <span class="text-maroon fw-bold font-monospace fs-5">{{ $document->tracking_id }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- LEFT COLUMN: INFO (3 Units) -->
        <div class="col-xl-3 col-lg-4">
            
            <!-- CREATOR INFO -->
<div class="card shadow-sm border-0 rounded-3 mb-3">
    <div class="card-header bg-maroon text-white py-2">
        <h6 class="mb-0 small fw-bold text-uppercase">Creator Details</h6>
    </div>
    <div class="card-body p-3">
        <!-- Removed the square div and alignment classes -->
        <div class="mb-3">
            <h6 class="mb-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ $document->uploader->username }}</h6>
            <small class="text-muted">Office Head / Staff</small>
        </div>

        <div class="small border-top pt-2">
            <div class="mb-1 text-muted">Office: <span class="fw-bold text-maroon text-uppercase">{{ $document->uploader->office->office_name ?? 'Records' }}</span></div>
            <div class="text-muted">Campus Code: <span class="fw-bold text-dark">{{ $document->uploader->campus_code ?? '0001' }}</span></div>
        </div>
        
        <button class="btn btn-dark btn-sm w-100 mt-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#trailModal">
            <i class="fa fa-history me-1"></i> VIEW AUDIT TRAIL
        </button>
    </div>
</div>

            <!-- JOURNEY STATUS (FIXED) -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-2">
                    <h6 class="fw-bold mb-0 text-dark small text-uppercase">Journey Status</h6>
                </div>
                <div class="card-body p-3">
                    <div class="journey-v-timeline">
                        <div class="j-item completed">
                            <div class="j-icon"><i class="fa fa-door-open"></i></div>
                            <div class="j-content">
                                <p class="fw-bold mb-0 small text-uppercase">Created</p>
                                <small class="text-muted" style="font-size: 0.65rem;">{{ $document->created_at->format('M d, h:i A') }}</small>
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
                                <div class="j-icon">
                                    <i class="fa {{ $isDone ? 'fa-check' : ($isReturned ? 'fa-undo' : ($isCurrent ? 'fa-spinner fa-spin' : 'fa-circle')) }}"></i>
                                </div>
                                <div class="j-content">
                                    <p class="fw-bold mb-0 small text-uppercase">{{ $sig->user->username }}</p>
                                    @if($isDone) 
                                        <small class="text-success fw-bold" style="font-size: 0.65rem;">Signed {{ \Carbon\Carbon::parse($sig->signed_at)->format('M d, H:i') }}</small>
                                    @elseif($isCurrent) 
                                        <small class="text-primary fw-bold" style="font-size: 0.65rem;">Awaiting Action</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="j-item {{ $document->status == 'accepted' ? 'completed' : '' }}">
                            <div class="j-icon"><i class="fa fa-flag-checkered"></i></div>
                            <div class="j-content"><p class="fw-bold mb-0 small text-uppercase">Finished</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: PREVIEW (9 Units) -->
        <div class="col-xl-9 col-lg-8">
            <div class="card shadow-lg border-0 rounded-3 overflow-hidden bg-white mb-3" style="height: 75vh;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa {{ $isActuallyPhysical ? 'fa-box' : 'fa-file-pdf' }} me-2"></i>{{ $document->title }}</h6>
                </div>
                <div class="card-body p-0" id="preview-body">
                    @if($isActuallyPhysical)
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-white text-center p-5">
                            <i class="fa fa-box-open fa-5x text-maroon opacity-10 mb-4"></i>
                            <h1 class="fw-black text-dark text-uppercase mb-2" style="font-weight: 900; letter-spacing: 2px;">THIS IS A HARD COPY</h1>
                            <div class="badge bg-maroon p-2 px-5 rounded-pill mb-3 fs-6">ITEM: {{ $document->title }}</div>
                            <p class="text-muted small">This is a physical transaction. No digital preview is available.</p>
                        </div>
                    @else
                        <iframe src="{{ url('/document/live-preview/' . $document->id) }}#toolbar=0" width="100%" height="100%" style="border: none;"></iframe>
                    @endif
                </div>
            </div>

            <!-- ACTION AREA: FINALIZED -->
            @if($document->status == 'accepted')
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white border-top border-success border-5">
                    <div class="card-body p-3 text-center">
                        @if($isActuallyPhysical)
                            <h5 class="fw-bold text-success mb-1 text-uppercase"><i class="fa fa-check-circle me-1"></i> Transaction Completed</h5>
                            <p class="text-muted small mb-0">The physical item has been successfully verified by all parties.</p>
                        @else
                            <div class="row g-2 justify-content-center">
                                <div class="col-md-4">
                                    <a href="{{ route('documents.download', $document->id) }}" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                                        <i class="fa fa-download me-2"></i> DOWNLOAD FINAL
                                    </a>
                                </div>
                            </div>
                        @endif
                        
                        @if(str_contains(Auth::user()->office_id, '-REC-'))
                            <div class="mt-3">
                                <button class="btn btn-dark fw-bold py-2 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#disseminateModal">
                                    <i class="fa fa-share-alt me-2"></i> SHARE RECORD
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- ACTION AREA: SIGNING -->
            @if($isMyTurn)
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-top border-warning border-5">
                    @if($isActuallyPhysical)
                        <div class="text-center">
                            <h4 class="fw-bold mb-3 text-dark">CONFIRM PHYSICAL RECEIPT</h4>
                            <button class="btn btn-success btn-lg fw-bold w-100 py-3 shadow-sm border-0" id="btn-confirm-receipt">
                                I HAVE RECEIVED THIS ITEM <i class="fa fa-check-circle ms-1"></i>
                            </button>
                        </div>
                    @else
                        <div class="d-flex gap-3">
                            <button class="btn btn-success flex-fill py-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#signatureModal">SIGN NOW</button>
                            <button class="btn btn-outline-danger px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#returnModal">RETURN</button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
<!-- MODALS SECTION -->
<div class="modal fade" id="trailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold small text-uppercase ls-1"><i class="fa fa-history me-2"></i>Audit History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small">
                        <tr><th>TIMESTAMP</th><th>ACTION</th><th>PERSONNEL</th><th>OFFICE ID</th><th>REMARKS</th></tr>
                    </thead>
                    <tbody style="font-size: 0.85rem;">
                        @foreach($document->logs->sortByDesc('created_at') as $log)
                        @php
                            $rawAction = strtoupper($log->action);
                            $displayAction = $rawAction;
                            if ($rawAction === 'TIME OF HELLO') $displayAction = 'CREATED';
                            elseif ($rawAction === 'DIGITAL SIGNATURE APPLIED' || $rawAction === 'PHYSICAL ITEM RECEIVED' || $rawAction === 'PHYSICAL RECEIPT CONFIRMED') $displayAction = $isActuallyPhysical ? 'RECEIVED' : 'SIGNED';
                            elseif ($rawAction === 'DOCUMENT RETURNED') $displayAction = 'RETURNED';
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted">{{ $log->created_at->timezone('Asia/Manila')->format('M d, Y | h:i A') }}</td>
                            <td><span class="badge border text-dark fw-bold">{{ $displayAction }}</span></td>
                            <td class="fw-bold">{{ $log->user->username }}</td>
                            <td><code class="small text-danger">{{ $log->user->office->id ?? $log->office_id }}</code></td>
                            <td class="small text-muted italic">
                                @if($rawAction === 'TIME OF HELLO')
                                    {{ $isActuallyPhysical ? 'Physical item registered.' : 'Digital document uploaded.' }}
                                @elseif($rawAction === 'DIGITAL SIGNATURE APPLIED' || $rawAction === 'PHYSICAL RECEIPT CONFIRMED')
                                    {{ $isActuallyPhysical ? 'Document received and verified.' : 'Digital signature applied.' }}
                                @else
                                    {{ $log->remarks }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- SHARE MODAL --}}
<div class="modal fade" id="disseminateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('documents.disseminate', $document->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-maroon text-white"><h5 class="modal-title fw-bold small">SHARE FINALIZED DOCUMENT</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="border rounded p-3 bg-light shadow-sm" style="max-height: 300px; overflow-y: auto;">
                        @foreach(\App\Models\Office::where('id', '!=', Auth::user()->office_id)->orderBy('office_name', 'asc')->get() as $off)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="office_ids[]" value="{{ $off->id }}" id="o{{ $off->id }}">
                                <label class="form-check-label small fw-bold text-dark" for="o{{ $off->id }}">{{ $off->office_name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-maroon w-100 fw-bold shadow-sm">SEND COPIES</button></div>
            </div>
        </form>
    </div>
</div>

{{-- SIGNATURE MODAL --}}
<div class="modal fade" id="signatureModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white"><h5 class="modal-title fw-bold small">APPLY DIGITAL SIGNATURE</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center">
                <ul class="nav nav-pills nav-justified mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#draw-tab" type="button">Draw</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#upload-tab" type="button">Upload File</button></li>
                </ul>
                <div class="tab-content bg-light p-3 rounded border">
                    <div class="tab-pane fade show active" id="draw-tab">
                        <canvas id="sig-canvas" class="border bg-white w-100 rounded" style="height: 200px; cursor: crosshair;"></canvas>
                        <button type="button" class="btn btn-sm text-danger mt-2 fw-bold" id="clear-sig">CLEAR PAD</button>
                    </div>
                    <div class="tab-pane fade" id="upload-tab">
                        <input type="file" id="sig-file" class="form-control mb-2" accept="image/*">
                        <img id="sig-preview" class="d-none border rounded w-100 shadow-sm" style="max-height: 150px; object-fit: contain;">
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-success w-100 fw-bold shadow-sm" id="btn-submit-signature">CONFIRM & SIGN</button></div>
        </div>
    </div>
</div>

{{-- RETURN MODAL --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title fw-bold small text-uppercase">Return to Creator</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <textarea id="return-remarks" class="form-control" rows="4" placeholder="Explain what needs to be fixed..."></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-danger w-100 fw-bold shadow-sm" id="confirm-return">CONFIRM RETURN</button></div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    .journey-v-timeline { position: relative; padding-left: 35px; border-left: 2px solid #dee2e6; margin-left: 15px; }
    .j-item { position: relative; margin-bottom: 20px; }
    .j-icon { position: absolute; left: -49px; width: 26px; height: 26px; background: #fff; border: 2px solid #dee2e6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #adb5bd; z-index: 2; }
    .j-item.completed .j-icon { background: #800000; border-color: #800000; color: #fff; }
    .j-item.active .j-icon { background: #ffc107; border-color: #800000; color: #000; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let isUploadMode = false;
        let uploadedBase64 = null;
        const canvas = document.getElementById('sig-canvas');
        const signaturePad = canvas ? new SignaturePad(canvas) : null;

        document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (e) {
                isUploadMode = e.target.getAttribute('data-bs-target') === '#upload-tab';
            });
        });

        document.getElementById('btn-submit-signature')?.addEventListener('click', function() {
            const data = isUploadMode ? uploadedBase64 : signaturePad.toDataURL();
            if(!data || (!isUploadMode && signaturePad.isEmpty())) return alert("Provide signature");
            
            fetch("{{ route('documents.sign', $document->id) }}", {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ signature_data: data })
            }).then(() => window.location.reload());
        });

        document.getElementById('btn-confirm-receipt')?.addEventListener('click', function() {
            if(!confirm("Confirm physical receipt?")) return;
            fetch("{{ route('documents.sign', $document->id) }}", {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ signature_data: 'PHYSICAL_RECEIPT' })
            }).then(() => window.location.reload());
        });

        document.getElementById('confirm-return')?.addEventListener('click', function() {
            fetch("{{ route('documents.return', $document->id) }}", {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ remarks: document.getElementById('return-remarks').value })
            }).then(() => window.location.reload());
        });

        document.getElementById('sig-file')?.addEventListener('change', function(e) {
            const reader = new FileReader(); 
            reader.onload = (ev) => { 
                uploadedBase64 = ev.target.result; 
                document.getElementById('sig-preview').src = uploadedBase64; 
                document.getElementById('sig-preview').classList.remove('d-none'); 
            }; 
            reader.readAsDataURL(e.target.files[0]);
        });
    });

    function printSignedDocument(url) {
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none'; iframe.src = url;
        document.body.appendChild(iframe);
        iframe.onload = () => { iframe.contentWindow.print(); };
    }
</script>
@endpush