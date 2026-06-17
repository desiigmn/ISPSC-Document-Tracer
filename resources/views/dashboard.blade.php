@extends('layouts.ispsc')

@section('title', 'Administrative HUB | ISPSC ONWARDS UIP')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    :root { --ispsc-maroon: #800000; --ispsc-gold: #FFB000; }
    .main-content-fluid { width: 100%; padding: 0 45px; background: #fff; }

    /* HIGH-DENSITY ADMINISTRATIVE THEME */
    h1 { font-size: 2.8rem !important; font-weight: 900; color: var(--ispsc-maroon); letter-spacing: -1.5px; }
    
    .stat-card {
        min-height: 155px; border: none; border-radius: 8px; transition: 0.3s;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06); display: flex; flex-direction: column; justify-content: center;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
    .stat-card h1 { font-size: 4rem !important; color: #fff !important; margin: 0; line-height: 1; }
    .stat-card h6 { font-size: 0.9rem !important; color: #fff !important; letter-spacing: 1px; font-weight: 800; opacity: 0.85; margin-bottom: 5px; }

    /* UNIFIED TABLE STYLE */
    .table thead th { 
        font-size: 0.75rem !important; font-weight: 900; letter-spacing: 0.8px;
        padding: 18px 12px !important; text-transform: uppercase; color: #111;
        border-bottom: 3px solid var(--ispsc-maroon) !important; background: #fff;
    }

    /* LEFT-BORDER SEQUENCES */
    .row-p3 { border-left: 6px solid #dc3545 !important; } /* RED */
    .row-p2 { border-left: 6px solid #fd7e14 !important; } /* ORANGE */
    .row-p1 { border-left: 6px solid #FFCC00 !important; } /* YELLOW */
    .row-accepted { border-left: 6px solid #198754 !important; } /* GREEN */
    .row-shared { border-left: 6px solid var(--ispsc-maroon) !important; } /* MAROON */

    .ledger-row td { padding: 18px 12px !important; font-size: 0.95rem; border-bottom: 1px solid #f2f2f2 !important; vertical-align: middle; }

    /* BADGES AND PILLS */
    .id-font { font-family: monospace; font-weight: 900; color: #800000; font-size: 0.95rem; }
    .pill-status-transit { background: var(--ispsc-maroon) !important; color: white !important; font-weight: 800; padding: 6px 15px; border-radius: 4px; font-size: 0.6rem; width: 110px; display: inline-block; text-align: center; }
    
    /* HUB BUTTON */
    .btn-action-hub { 
        border: 1.5px solid var(--ispsc-maroon) !important; color: var(--ispsc-maroon) !important; 
        background: transparent !important; font-weight: 900; border-radius: 50px; 
        padding: 7px 22px; font-size: 0.75rem; text-decoration: none; display: inline-flex; align-items: center; 
    }
    .btn-action-hub:hover { background: var(--ispsc-maroon) !important; color: #fff !important; }
</style>
@endpush

@section('content')
<div class="main-content-fluid py-5">
    @php
        $filter = request('filter');
        $currUser = Auth::user();
        $isAdminOrRecords = ($currUser->role === 'superadmin' || str_contains($currUser->office_id ?? '', '-REC-'));

        // REBUILT CARDS DATA (Prevent Key 3 Error)
        if($isAdminOrRecords) {
            $cards = [['PRIORITY ASSIGNMENT', $countReview ?? 0, '#0d6efd', 'review'], ['ON PROCESS', $countPending, '#800000', 'pending'], ['FINISHED', $countFinished, '#198754', 'accepted'], ['SHARED COPIES', $countShared, '#0056b3', 'shared']];
            $grid = "col-6 col-xl-3";
        } else {
            $cards = [['ON PROCESS', $countPending, '#800000', 'pending'], ['FINISHED', $countFinished, '#198754', 'accepted'], ['SHARED COPIES', $countShared, '#0056b3', 'shared']];
            $grid = "col-12 col-md-4";
        }

        // --- SEQUENCE ORDER DEFINITION ---
        $listReview  = $documents->where('status', 'needs_review');
        $listExtUrge = $documents->where('priority', 3)->whereIn('status', ['pending', 'returned']);
        $listUrgent  = $documents->where('priority', 2)->whereIn('status', ['pending', 'returned']);
        $listNormal  = $documents->where('priority', 1)->whereIn('status', ['pending', 'returned']);
        $listAccepted = $documents->where('status', 'accepted');
        
        $listShared = $documents->filter(function($doc) use ($currUser, $isAdminOrRecords) {
            $logCheck = $doc->logs->where('office_id', $currUser->office_id)->where('action', 'DISSEMINATED')->count() > 0;
            return $logCheck;
        })->diff($listAccepted);

        // Map groups to loops
        $masterFlow = [
            ['data' => $listReview,  'cls' => 'row-ex-urgent', 'l' => 'REVIEW', 'h' => '#0d6efd'],
            ['data' => $listExtUrge, 'cls' => 'row-ex-urgent', 'l' => 'EX. URGENT', 'h' => '#dc3545'],
            ['data' => $listUrgent,  'cls' => 'row-urgent',    'l' => 'URGENT',    'h' => '#fd7e14'],
            ['data' => $listNormal,  'cls' => 'row-normal',    'l' => 'NORMAL',    'h' => '#FFD700'],
            ['data' => $listAccepted,'cls' => 'row-accepted',  'l' => 'FINISHED',  'h' => '#198754'],
            ['data' => $listShared,  'cls' => 'row-shared',    'l' => 'SHARED',    'h' => '#800000'],
        ];
    @endphp

    <!-- UPDATED HEADER: Display Office instead of Username as "Station" -->
    <div class="row align-items-center mb-5 animate__animated animate__fadeIn">
        <div class="col-12 col-lg-7">
            <h1 class="fw-black mb-0 ls-n1">Institutional Dashboard</h1>
            <p class="text-muted fw-bold mb-0 text-uppercase small ls-1"><span class="mx-1"></span> Office: <strong>{{ strtoupper($currUser->office->office_name ?? 'SYSTEM STATION') }}</strong></p>
        </div>
        <div class="col-12 col-lg-5 text-lg-end mt-4">
            <a href="{{ route('documents.create') }}" class="btn btn-dark fw-black p-3 px-5 rounded-pill shadow-sm"><i class="fa fa-plus me-1 text-warning"></i> NEW TRANSACTION</a>
            <button class="btn btn-maroon fw-black p-3 px-5 rounded-pill shadow-sm text-white" style="background:#800000" data-bs-toggle="modal" data-bs-target="#scanQrModal">SCAN SYSTEM <i class="fa fa-qrcode ms-1"></i></button>
        </div>
    </div>

    <!-- CARDS SECTION -->
    <div class="row g-4 mb-5">
        @foreach($cards as $c)
        <div class="{{ $grid }}">
            <a href="{{ route('dashboard', ['filter' => $c[3]]) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-center text-white" style="background-color: {{ $c[2] }};">
                    <h6>{{ $c[0] }}</h6><h1>{{ $c[1] }}</h1>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- SEARCH & TITLE -->
    <div class="d-flex justify-content-between align-items-end border-bottom border-dark border-3 pb-3 mb-0">
        <h4 class="fw-black text-dark text-uppercase m-0 ls-1"><i class="fa fa-folder-tree me-2 text-maroon"></i> Transaction Records</h4>
        <form action="{{ route('dashboard') }}" method="GET" class="d-flex w-100" style="max-width: 400px;">
            <input type="text" name="search" class="form-control rounded-0 border-dark" placeholder="Tracking code / reference..." value="{{ request('search') }}">
            <button class="btn btn-dark rounded-0 px-4"><i class="fa fa-search"></i></button>
        </form>
    </div>

    <!-- ONE TABLE - THE SOLUTION -->
    <div class="bg-white border-bottom shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-5">Tracking ID</th>
                        <th>Description</th>
                        <th>Creator</th>
                        <th>Currently At (Hub)</th> {{-- Preserving "Custodian" logic --}}
                        <th>Final Hub</th>
                        <th class="text-center">Urgency</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Hub</th>
                    </tr>
                </thead>
                <tbody>
                    @php $dataFound = false; @endphp
                    @foreach($masterFlow as $layer)
                        @foreach($layer['data'] as $doc)
                            @php $dataFound = true; @endphp
                            <tr class="ledger-row {{ $layer['cls'] }}">
                                <td class="ps-5"><span class="id-font">{{ $doc->tracking_id }}</span></td>
                                <td>
                                    <div class="fw-black text-dark text-uppercase small ls-n1">{{ $doc->title }}</div>
                                    <small class="text-muted fw-bold">{{ $doc->created_at->format('M d, Y') }}</small>
                                </td>
                                <td class="small fw-bold text-dark text-uppercase">{{ $doc->uploader->username }}</td>
                                <td>
                                    @php $now = $doc->signatories->where('sign_order', $doc->current_step)->first(); @endphp
                                    <div class="small fw-bold text-muted text-truncate" style="max-width: 150px;">
                                        @if($doc->status == 'accepted') <i class="fa fa-check-double text-success"></i> COMPLETED 
                                        @elseif($doc->status == 'needs_review') <i class="fa fa-magnifying-glass"></i> RECORDS Hub 
                                        @else <i class="fa fa-user-circle opacity-50"></i> {{ $now->user->username ?? 'Next Point' }} @endif
                                    </div>
                                </td>
                                <td><div class="small fw-bold text-muted text-truncate" style="max-width: 140px;"><i class="fa fa-building-columns opacity-25"></i> {{ $doc->targetOffice->office_name ?? 'Station' }}</div></td>
                                <td class="text-center"><span class="badge text-white px-2 py-1 fw-bold shadow-sm" style="font-size:0.55rem; background-color: {{ $layer['h'] }}">{{ $layer['l'] }}</span></td>
                                <td class="text-center"><span class="pill-status-transit">{{ $doc->status == 'accepted' ? 'FINISHED' : 'ON PROCESS' }}</span></td>
                                <td class="text-center px-4"><a href="{{ route('documents.view', $doc->tracking_id) }}" class="btn-action-hub shadow-sm">TRACK</a></td>
                            </tr>
                        @endforeach
                    @endforeach

                    @if(!$dataFound)
                        <tr><td colspan="8" class="text-center py-5 opacity-25 fw-bold fs-4">HUB VOID: NO RECORDS MATCH FILTER</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center py-5">{{ $documents->links() }}</div>
</div>

<div class="modal fade" id="scanQrModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <!-- Header -->
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-black small text-uppercase mb-0 ls-1">Record Authentication Hub</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopScanner()"></button>
            </div>
            
            <!-- Navigation -->
            <div class="bg-light p-2 border-bottom">
                <ul class="nav nav-pills nav-justified" id="qr-tab-nav" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold small" id="tab-camera-btn" data-bs-toggle="pill" data-bs-target="#camera-pane" type="button" role="tab"><i class="fa fa-camera me-1"></i> VIEW-PORT</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold small" id="tab-upload-btn" data-bs-toggle="pill" data-bs-target="#upload-pane" type="button" role="tab"><i class="fa fa-image me-1"></i> PHOTO-UPLOAD</button>
                    </li>
                </ul>
            </div>

            <!-- Identical Height Body -->
            <div class="modal-body p-0 qr-tab-content">
                <div class="tab-content h-100">
                    
                    <!-- CAMERA Pane -->
                    <div class="tab-pane fade show active h-100" id="camera-pane" role="tabpanel">
                        <div class="pane-wrapper">
                            <div id="reader"></div>
                        </div>
                    </div>

                    <!-- UPLOAD Pane (SAME DIMENSIONS AS ABOVE) -->
                    <div class="tab-pane fade h-100" id="upload-pane" role="tabpanel">
                        <div class="pane-wrapper" style="background: #fff;">
                            <label for="qr-input-file" class="upload-zone-full p-4 text-center">
                                <i class="fa fa-qrcode fa-5x text-maroon opacity-10 mb-4"></i>
                                <h4 class="fw-black text-dark text-uppercase mb-1">Select Document QR</h4>
                                <p class="text-muted small px-5">The Hub will analyze your image and confirm before directing you to the ledgerhub hub.</p>
                                <input type="file" id="qr-input-file" accept="image/*" class="d-none">
                            </label>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="modal-footer bg-light border-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-danger w-100 py-2 fw-black text-uppercase shadow-sm" data-bs-dismiss="modal" onclick="stopScanner()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrCode = null;

    // Successful Scan Handling
    function handleScanSuccess(text) {
        stopScanner();
        bootstrap.Modal.getInstance(document.getElementById('scanQrModal')).hide();
        
        Swal.fire({
            title: '<span class="text-maroon">Verified Hub</span>',
            html: `Item Identifier: <b>${text.split('/').pop()}</b><br>Proceed to Transaction details?`,
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#800000',
            confirmButtonText: 'OPEN',
            reverseButtons: true
        }).then((res) => { if (res.isConfirmed) window.location.href = text; });
    }

    // Start Live Camera
    async function startCamera() {
        if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
        if (html5QrCode.isScanning) await html5QrCode.stop();
        
        const config = { fps: 20, qrbox: 250, aspectRatio: 1.0 };
        html5QrCode.start({ facingMode: "environment" }, config, handleScanSuccess).catch(e => console.warn(e));
    }

    // Stop all processes
    async function stopScanner() {
        if (html5QrCode && html5QrCode.isScanning) await html5QrCode.stop();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('qr-input-file');

        // Logic: Re-init camera on Modal open
        document.getElementById('scanQrModal').addEventListener('shown.bs.modal', startCamera);

        // Logic: Stop camera if user switches to the "Upload" tab to save resources
        document.getElementById('tab-upload-btn').addEventListener('shown.bs.tab', stopScanner);

        // Logic: Re-start camera if they switch back to Camera tab
        document.getElementById('tab-camera-btn').addEventListener('shown.bs.tab', startCamera);

        // --- NEW: UPLOAD FILE PROCESSING ---
        fileInput.addEventListener('change', e => {
            if (e.target.files.length === 0) return;
            
            if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
            
            // Visually notify user we are processing
            Swal.fire({ title: 'Processing Image...', didOpen: () => Swal.showLoading() });

            html5QrCode.scanFile(e.target.files[0], true)
                .then(decodedText => {
                    Swal.close();
                    handleScanSuccess(decodedText);
                })
                .catch(err => {
                    Swal.fire({ title: 'Scan Error', text: 'QR not detected in image. Ensure the code is clear.', icon: 'error' });
                    fileInput.value = ""; // reset input
                });
        });
        
        // Cleanup on hidden
        document.getElementById('scanQrModal').addEventListener('hidden.bs.modal', stopScanner);
    });
</script>
@endsection