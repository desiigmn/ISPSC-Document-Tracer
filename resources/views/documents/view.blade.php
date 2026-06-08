@extends('layouts.ispsc')

@section('title', 'Document Tracking Hub')

@section('content')
@php
    $userSig = $document->signatories->where('user_id', Auth::id())->first();
    $isMyTurn = ($document->status == 'pending' && 
                 $userSig &&
                 $document->current_step == $userSig->sign_order && 
                 $userSig->status == 'pending');
    
    // Check if it is a physical item
    $isActuallyPhysical = ($document->is_hard_copy == 1 || $document->file_path == 'PHYSICAL_ITEM' || empty($document->file_path));
@endphp

<style>
    /* 1. FLUID LAYOUT SETTINGS */
    .fluid-wrapper { 
        width: 100% !important; 
        padding: 0 20px; 
    }

    /* 2. JOURNEY STATUS TIMELINE (FIXED ALIGNMENT) */
    .journey-v-timeline {
        position: relative;
        padding-left: 45px;
        margin-top: 10px;
    }
    .journey-v-timeline::before {
        content: '';
        position: absolute;
        left: 14px;
        top: 0;
        bottom: 0;
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
        left: -40px;
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
    }
    .j-item.completed .j-icon { background: #800000; border-color: #800000; color: #fff; }
    .j-item.active .j-icon { 
        background: #ffc107; border-color: #800000; color: #000; 
        box-shadow: 0 0 8px rgba(255,193,7,0.6);
    }
    .j-item.returned .j-icon { background: #dc3545; border-color: #dc3545; color: #fff; }

    .text-maroon { color: #800000 !important; }
    .bg-maroon { background-color: #800000 !important; }
</style>

<div class="fluid-wrapper py-3">   
    
    <!-- TOP STATUS BAR -->
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
                            default => '#6c757d'
                        };
                    @endphp
                    <span class="badge px-4 py-2" style="background-color: {{ $statusColor }}; color: {{ $document->status == 'pending' ? '#000' : '#fff' }}; border-radius: 50px; font-size: 0.85rem;">
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
        <!-- LEFT COLUMN: SIDEBAR -->
        <div class="col-xl-3 col-lg-4">
            
            <!-- CREATOR CARD -->
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-header bg-maroon text-white py-2 text-center">
                    <h6 class="mb-0 small fw-bold text-uppercase">Document Creator</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-maroon bg-opacity-10 text-maroon rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fa fa-user-tie fs-5"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 fw-bold text-dark text-uppercase">{{ $document->uploader->username }}</h6>
                            <small class="text-muted">Office Personnel</small>
                        </div>
                    </div>
                    <div class="small border-top pt-3">
                        <div class="mb-1 text-muted">Campus: <span class="fw-bold text-dark">{{ $document->uploader->campus_code ?? '0001' }}</span></div>
                        <div class="text-muted">Office: <span class="fw-bold text-maroon text-uppercase">{{ $document->uploader->office->office_name ?? 'Records' }}</span></div>
                    </div>
                    <button class="btn btn-dark btn-sm w-100 mt-3 fw-bold" data-bs-toggle="modal" data-bs-target="#trailModal">
                        <i class="fa fa-history me-1"></i> VIEW AUDIT TRAIL
                    </button>
                </div>
            </div>

            <!-- JOURNEY STATUS -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-2">
                    <h6 class="fw-bold mb-0 text-dark small text-uppercase">Journey Status</h6>
                </div>
                <div class="card-body p-3">
                    <div class="journey-v-timeline">
                        <div class="j-item completed">
                            <div class="j-icon"><i class="fa fa-door-open"></i></div>
                            <div class="j-content">
                                <p class="fw-bold mb-0 small">CREATED</p>
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
                                        <small class="text-success fw-bold" style="font-size: 0.65rem;">Done {{ \Carbon\Carbon::parse($sig->signed_at)->format('M d, H:i') }}</small>
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

        <!-- RIGHT COLUMN: PREVIEW & ACTIONS -->
        <div class="col-xl-9 col-lg-8">
            <div class="card shadow-lg border-0 rounded-3 overflow-hidden bg-white mb-3" style="height: 75vh;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa {{ $isActuallyPhysical ? 'fa-box' : 'fa-file-pdf' }} me-2"></i>{{ $document->title }}</h6>
                    @if(!$isActuallyPhysical)
                        <a href="{{ route('documents.download', $document->id) }}" target="_blank" class="btn btn-sm btn-light border shadow-sm px-3 fw-bold">
                            <i class="fa fa-expand me-1"></i> FULLSCREEN
                        </a>
                    @endif
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
                        <!-- ABSOLUTE URL FIX -->
                        <iframe src="{{ url('/document/live-preview/' . $document->id) }}#toolbar=0" width="100%" height="100%" style="border: none;"></iframe>
                    @endif
                </div>
            </div>

<!-- ACTION AREA: FINALIZED -->
@if($document->status == 'accepted')
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white border-top border-success border-5">
        <div class="card-body p-4 text-center">
            <h6 class="fw-bold text-success mb-3 small text-uppercase">
                <i class="fa fa-check-circle me-1"></i> Tracking Process Finalized
            </h6>

            <div class="row g-2 justify-content-center align-items-center">
                @if($isActuallyPhysical)
                    {{-- UI FOR HARD COPIES --}}
                    <div class="col-12 mb-3">
                        <h5 class="fw-bold text-success mb-1 text-uppercase">Possession Verified</h5>
                        <p class="text-muted small mb-0">The physical item has been received and confirmed by all signatories.</p>
                    </div>
                    
                    @if(str_contains(Auth::user()->office_id, '-REC-'))
                        <div class="col-md-6 col-lg-4">
                            <button class="btn btn-dark w-100 fw-bold py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#disseminateModal">
                                <i class="fa fa-share-alt me-2"></i> SHARE TRANSACTION RECORD
                            </button>
                        </div>
                    @endif
                @else
                    {{-- UI FOR DIGITAL COPIES (Download, Print, and Share aligned) --}}
                    <div class="col-md-4">
                        <a href="{{ route('documents.download', $document->id) }}" class="btn btn-success w-100 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center">
                            <i class="fa fa-download me-2"></i> DOWNLOAD
                        </a>
                    </div>
                    
                    <div class="col-md-4">
                        <button type="button" onclick="printSignedDocument('{{ route('documents.download', ['id' => $document->id, 'mode' => 'print']) }}')" class="btn btn-primary w-100 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center">
                            <i class="fa fa-print me-2"></i> PRINT
                        </button>
                    </div>

                    @if(str_contains(Auth::user()->office_id, '-REC-'))
                        <div class="col-md-4">
                            <button class="btn btn-dark w-100 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#disseminateModal">
                                <i class="fa fa-share-alt me-2"></i> SHARE RECORD
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endif

            <!-- ACTION AREA: PENDING ACTIONS -->
            @if($isMyTurn)
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-top border-warning border-5">
                    @if($isActuallyPhysical)
                        <div class="text-center">
                            <h4 class="fw-bold mb-3 text-dark">CONFIRM PHYSICAL RECEIPT</h4>
                            <button class="btn btn-success btn-lg fw-bold w-100 py-3 shadow border-0" id="btn-confirm-receipt">
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

            <!-- CREATOR ACTION: RESUBMIT -->
            @if($document->status == 'returned' && $document->uploader_id == Auth::id())
                <div class="alert alert-danger shadow-sm border-0 border-start border-5 border-danger rounded-3 p-4 mb-4">
                    <h5 class="fw-bold text-dark"><i class="fa fa-undo-alt me-2"></i> RESUBMIT REQUIRED</h5>
                    <p class="small text-muted mb-3">Upload corrected files to restart the tracking cycle.</p>
                    <form action="{{ route('documents.resubmit', $document->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="doc_files[]" class="form-control" multiple required>
                            <button type="submit" class="btn btn-danger fw-bold px-4">RE-SUBMIT</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- AUDIT MODAL -->
<div class="modal fade" id="trailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white py-3"><h5 class="modal-title fw-bold small text-uppercase">Full Audit Trail</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small"><tr><th class="ps-4">TIMESTAMP</th><th>ACTION</th><th>PERSONNEL</th><th>OFFICE ID</th><th>REMARKS</th></tr></thead>
                    <tbody style="font-size: 0.85rem;">
                        @foreach($document->logs->sortByDesc('created_at') as $log)
                        @php
                            $rawAction = strtoupper($log->action);
                            $displayAction = ($rawAction === 'TIME OF HELLO') ? 'CREATED' : (($isActuallyPhysical && $rawAction === 'DIGITAL SIGNATURE APPLIED') ? 'RECEIVED' : $rawAction);
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted small">{{ $log->created_at->timezone('Asia/Manila')->format('M d, Y | h:i A') }}</td>
                            <td><span class="badge border text-dark">{{ $displayAction }}</span></td>
                            <td class="fw-bold text-dark">{{ $log->user->username }}</td>
                            <td><code class="small text-danger">{{ $log->user->office->id ?? $log->office_id }}</code></td>
                            <td class="small text-muted italic">
                                @if($rawAction === 'TIME OF HELLO') {{ $isActuallyPhysical ? 'Physical item registered.' : 'Digital document uploaded.' }}
                                @elseif($rawAction === 'DIGITAL SIGNATURE APPLIED') {{ $isActuallyPhysical ? 'Possession confirmed.' : 'Signature applied.' }}
                                @else {{ $log->remarks }} @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SIGNATURE MODAL -->
<div class="modal fade" id="signatureModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white"><h5>Apply Signature</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center">
                <ul class="nav nav-pills nav-justified mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#draw-tab">Draw</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#upload-tab">Upload</button></li>
                </ul>
                <div class="tab-content bg-light p-3 rounded border">
                    <div class="tab-pane fade show active" id="draw-tab">
                        <canvas id="sig-canvas" class="border bg-white w-100 rounded" style="height: 200px;"></canvas>
                        <button type="button" class="btn btn-sm text-danger mt-2 fw-bold" id="clear-sig">CLEAR PAD</button>
                    </div>
                    <div class="tab-pane fade" id="upload-tab">
                        <input type="file" id="sig-file" class="form-control" accept="image/*">
                        <img id="sig-preview" class="d-none border rounded w-100 mt-2 shadow-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-success w-100 fw-bold shadow-sm" id="btn-submit-signature">CONFIRM & SIGN</button></div>
        </div>
    </div>
</div>

<!-- RETURN MODAL -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white"><h5>Return to Creator</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <textarea id="return-remarks" class="form-control" rows="4" placeholder="Explain the reason for returning..."></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-danger w-100 fw-bold shadow-sm" id="confirm-return">CONFIRM RETURN</button></div>
        </div>
    </div>
</div>

<!-- DISSEMINATE MODAL -->
<!-- Modal 4: Disseminate -->
<div class="modal fade" id="disseminateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documents.disseminate', $document->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-maroon text-white">
                    <h5 class="modal-title fw-bold small text-uppercase">Share Finalized Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Select the offices to receive a digital copy:</p>
                    <div class="border rounded p-3 bg-light shadow-sm" style="max-height: 350px; overflow-y: auto;">
                        @foreach(\App\Models\Office::where('id', '!=', Auth::user()->office_id)->orderBy('office_name', 'asc')->get() as $off)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="office_ids[]" value="{{ $off->id }}" id="o{{ $off->id }}">
                                <label class="form-check-label small fw-bold text-dark" for="o{{ $off->id }}">
                                    {{ $off->office_name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-maroon w-100 fw-bold shadow-sm py-2">
                        <i class="fa fa-paper-plane me-1"></i> SEND COPIES NOW
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let isUploadMode = false;
        let uploadedBase64 = null;
        let signaturePad = null;
        const canvas = document.getElementById('sig-canvas');
        const signatureModal = document.getElementById('signatureModal');

        if (signatureModal) {
            signatureModal.addEventListener('shown.bs.modal', function () {
                if (!signaturePad && canvas) { signaturePad = new SignaturePad(canvas); }
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                if (signaturePad) signaturePad.clear();
            });
        }

        const clearBtn = document.getElementById('clear-sig');
        if (clearBtn) { clearBtn.addEventListener('click', () => signaturePad && signaturePad.clear()); }

        document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                isUploadMode = e.target.getAttribute('data-bs-target') === '#upload-tab';
            });
        });

        document.getElementById('btn-submit-signature')?.addEventListener('click', function() {
            const data = isUploadMode ? uploadedBase64 : (signaturePad ? signaturePad.toDataURL() : null);
            if(!data || (!isUploadMode && signaturePad.isEmpty())) return alert("Provide signature");
            submitAction("{{ route('documents.sign', $document->id) }}", { signature_data: data });
        });

        document.getElementById('btn-confirm-receipt')?.addEventListener('click', function() {
            if(!confirm("Confirm physical receipt?")) return;
            submitAction("{{ route('documents.sign', $document->id) }}", { signature_data: 'PHYSICAL_RECEIPT' });
        });

        document.getElementById('confirm-return')?.addEventListener('click', function() {
            const remarks = document.getElementById('return-remarks').value;
            if(!remarks) return alert("Please provide a reason.");
            submitAction("{{ route('documents.return', $document->id) }}", { remarks: remarks });
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

        function submitAction(url, bodyData) {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(bodyData)
            }).then(() => window.location.reload());
        }
    });

    function printSignedDocument(url) {
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none'; iframe.src = url;
        document.body.appendChild(iframe);
        iframe.onload = () => { iframe.contentWindow.focus(); iframe.contentWindow.print(); };
    }
</script>
@endpush