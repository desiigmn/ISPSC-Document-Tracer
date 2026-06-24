@extends('layouts.ispsc')

@section('title', 'System HUB | ISPSC ONWARDS UIP')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    :root { 
        --ispsc-maroon: #800000; 
        --ispsc-yellow: #FFCC00;
        --ispsc-blue: #0056b3;
        --bg-light: #f4f7f9;
    }

    body { background-color: var(--bg-light); font-size: 14px; color: #333; }
    .main-content-fluid { width: 100%; padding: 0 15px; }
    @media (min-width: 992px) { .main-content-fluid { padding: 0 40px; } }

    /* CARD STYLING */
    .stat-card {
        background: #fff; border: 1px solid #e1e8ed; border-radius: 12px;
        padding: 15px; transition: 0.3s; display: flex; align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02); height: 100%;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    .stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #fff; margin-right: 15px; flex-shrink: 0; }
    .stat-card h1 { font-size: 1.6rem !important; margin: 0; font-weight: 800; color: #000; }
    .stat-card h6 { font-size: 11px !important; margin: 0; color: #888; text-transform: uppercase; font-weight: 700; }

    .tracer-card { background: #fff; border: 1px solid #e1e8ed; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; }
    .tracer-card-header { padding: 15px 20px; border-bottom: 1px solid #f1f1f1; display: flex; justify-content: space-between; align-items: center; }
    .tracer-card-header h6 { margin: 0; font-weight: 800; color: #000; text-transform: uppercase; font-size: 13px; }

    .table thead th { background: #f8f9fa; color: #888; font-size: 11px; text-transform: uppercase; font-weight: 800; border: none; padding: 12px 20px; }
    .ledger-row td { padding: 15px 20px !important; border-bottom: 1px solid #f1f1f1 !important; font-size: 13px; vertical-align: middle; }
    .tracking-id-text { font-family: monospace; font-weight: 800; color: var(--ispsc-maroon); font-size: 14px; word-break: break-all; }

    .status-badge-hub { padding: 3px 10px; border-radius: 4px; font-weight: 800; font-size: 10px; text-transform: uppercase; }
    
    /* Dynamic Badge Colors */
    .cat-proc-bg { background: #fff4e5; color: #d97706; } 
    .cat-fin-bg  { background: #e6f4ea; color: #1e7e34; } 
    .cat-rev-bg  { background: #e7f1ff; color: #0d6efd; } 
    .cat-shr-bg  { background: #eef2ff; color: #4338ca; } 

    .btn-action-track-hub-pill { background: #111; color: #fff; font-weight: 800; font-size: 10px; padding: 6px 15px; border-radius: 5px; text-decoration: none; }
    .btn-action-track-hub-pill:hover { background: var(--ispsc-maroon); color: #fff; }

    .search-container { position: relative; width: 100%; max-width: 400px; }
    .search-input { width: 100%; padding: 8px 15px 8px 40px; border-radius: 8px; border: 1.5px solid #e1e8ed; font-size: 14px; transition: 0.3s; }
    .search-input:focus { border-color: var(--ispsc-maroon); outline: none; }
    .search-icon { position: absolute; left: 15px; top: 11px; color: #adb5bd; font-size: 14px; }

    .btn-maroon { background: var(--ispsc-maroon); color: #fff; border-radius: 8px; font-weight: 800; font-size: 12px; border: none; padding: 8px 20px; }
    .btn-dark { background: #1a1a1a; color: #fff; border-radius: 8px; font-weight: 800; font-size: 12px; border: none; padding: 8px 20px; }

    /* SCANNER MODAL FIXES */
    .pane-wrapper {
        min-height: 300px;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    #reader { width: 100% !important; background: #000; border: none !important; }
    #reader__scan_region { background: #000; }
    
    #qr-tab-nav .nav-link {
        border-radius: 5px;
        margin: 5px;
        padding: 12px;
        color: #0d6efd;
        background: #fff;
        border: 1px solid #0d6efd;
    }
    #qr-tab-nav .nav-link.active {
        background: #0d6efd !important;
        color: #fff !important;
    }
</style>
@endpush

@section('content')
<div class="main-content-fluid py-4">
@php
    $filter = request('filter');
    $currUser = Auth::user();
    $isAdminOrRecords = ($currUser->role === 'superadmin' || str_contains($currUser->office_id ?? '', '-REC-'));
    
    $docsRev = $documents->where('status', 'needs_review')->sortByDesc('created_at');
    $docsP3  = $documents->where('priority', 3)->whereIn('status', ['pending', 'returned', 'mapping'])->sortByDesc('created_at');
    $docsP2  = $documents->where('priority', 2)->whereIn('status', ['pending', 'returned', 'mapping'])->sortByDesc('created_at');
    $docsP1  = $documents->where('priority', 1)->whereIn('status', ['pending', 'returned', 'mapping'])->sortByDesc('created_at');
    
    $grSha = $documents->filter(function($doc) {
        return $doc->logs->where('action', 'DISSEMINATED')->count() > 0;
    })->sortByDesc('created_at')->values();

    $docsFin = $documents->where('status', 'accepted')->filter(function($doc) {
        return $doc->logs->where('action', 'DISSEMINATED')->count() == 0;
    })->sortByDesc('created_at');

    if($isAdminOrRecords) {
        $dashboardCards = [
            ['Priority Assignment', $countReview ?? 0, '#0d6efd', 'review', 'fa-tasks'], 
            ['On Process', $countPending ?? 0, '#800000', 'pending', 'fa-spinner'], 
            ['Finished', $countFinished ?? 0, '#198754', 'accepted', 'fa-check-double'], 
            ['Shared Copies', $countShared ?? 0, '#0056b3', 'shared', 'fa-copy']
        ];
    } else {
        $dashboardCards = [
            ['On Process', $countPending ?? 0, '#800000', 'pending', 'fa-bolt'], 
            ['Completed', $countFinished ?? 0, '#198754', 'accepted', 'fa-archive'], 
            ['Received Shares', $countShared ?? 0, '#0056b3', 'shared', 'fa-share-alt']
        ];
    }

    $unifiedHubTable = [
        ['d' => $docsRev, 'l' => 'ASSIGNMENT', 'p' => 'cat-rev-bg',  'k' => 'review'],
        ['d' => $docsP3,  'l' => 'EX. URGENT', 'p' => 'cat-proc-bg', 'k' => 'pending'],
        ['d' => $docsP2,  'l' => 'URGENT',     'p' => 'cat-proc-bg', 'k' => 'pending'],
        ['d' => $docsP1,  'l' => 'NORMAL',     'p' => 'cat-proc-bg', 'k' => 'pending'],
        ['d' => $grSha,   'l' => 'RELEASED',   'p' => 'cat-shr-bg',  'k' => 'shared'],
        ['d' => $docsFin, 'l' => 'FINISHED',    'p' => 'cat-fin-bg',  'k' => 'accepted'], 
    ];
@endphp

    <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-4">
        </div>
        <div class="col-lg-8 d-flex justify-content-lg-end align-items-center flex-wrap gap-2 header-actions-wrap">
            <form action="{{ route('dashboard') }}" method="GET" class="search-container">
                <i class="fa fa-search search-icon"></i>
                <input type="text" name="search" class="search-input" placeholder="Search Tracking ID..." value="{{ request('search') }}">
            </form>
            <div class="d-flex gap-2">
                <a href="{{ route('documents.create') }}" class="btn btn-dark">+ NEW DOCUMENT</a>
                <button class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#scanQrModal">SCAN QR</button>
            </div>
        </div>
    </div>

    <div class="row g-2 g-md-3 mb-4">
        @foreach($dashboardCards as $c)
        <div class="col-6 col-lg-3">
            <a href="{{ route('dashboard', ['filter' => $c[3]]) }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: {{ $c[2] }};"><i class="fa {{ $c[4] }}"></i></div>
                    <div><h6>{{ $c[0] }}</h6><h1>{{ $c[1] }}</h1></div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <div class="row g-0">
        <div class="col-12">
            <div class="tracer-card">
                <div class="tracer-card-header"><h6>Document Registry</h6></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th class="ps-4">Tracking ID</th>
                                <th>Description</th>
                                <th>Creator</th>
                                <th>Officeholder</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hubCheckVar = false; @endphp
                            @foreach($unifiedHubTable as $rowGroup)
                                @if(!$filter || $filter == $rowGroup['k'] || $filter == 'dashboard')
                                    @foreach($rowGroup['d'] as $doc)
                                        @php $hubCheckVar = true; @endphp
                                        <tr class="ledger-row">
                                            <td class="ps-4">
                                                <div class="tracking-id-text">{{ $doc->tracking_id }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark text-uppercase small">{{ $doc->title }}</div>
                                                <span class="badge {{ $rowGroup['p'] }}" style="font-size: 10px;">{{ $rowGroup['l'] }}</span>
                                            </td>
                                            <td class="small fw-bold text-muted">{{ $doc->uploader->username }}</td>
                                            <td>
                                                @php $now = $doc->signatories->where('sign_order', $doc->current_step)->first(); @endphp
                                                <span class="small fw-bold">
                                                    @if($doc->status == 'accepted') <span class="text-success"><i class="fa fa-check-circle"></i> DONE</span> @else {{ $now->user->username ?? 'SYSTEM' }} @endif
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="status-badge-hub {{ $rowGroup['p'] }}">
                                                    {{ $doc->status == 'accepted' ? 'Finished' : 'Processing' }}
                                                </span>
                                            </td>
                                            <td class="text-center pe-4"><a href="{{ route('documents.view', $doc->tracking_id) }}" class="btn-action-track-hub-pill shadow-sm">TRACE</a></td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                            @if(!$hubCheckVar) <tr><td colspan="6" class="text-center py-5 opacity-25">No matching records found.</td></tr> @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-center py-2">{{ $documents->links() }}</div>
        </div>
    </div>
</div>

{{-- QR SCAN MODAL --}}
<div class="modal fade" id="scanQrModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-black small text-uppercase mb-0 ls-1">Record Authentication Hub</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopScanner()"></button>
            </div>
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
            <div class="modal-body p-0 qr-tab-content">
                <div class="tab-content h-100">
                    <div class="tab-pane fade show active h-100" id="camera-pane" role="tabpanel">
                        <div class="pane-wrapper"><div id="reader"></div></div>
                    </div>
                    <div class="tab-pane fade h-100" id="upload-pane" role="tabpanel">
                        <div class="pane-wrapper" style="background: #fff;">
                            <label for="qr-input-file" class="upload-zone-full p-4 text-center cursor-pointer">
                                <i class="fa fa-qrcode fa-5x text-maroon opacity-10 mb-4"></i>
                                <h4 class="fw-black text-dark text-uppercase mb-1">Select Document QR</h4>
                                <p class="text-muted small px-5">The system will analyze your image and redirect you to the record.</p>
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

    function handleScanSuccess(text) {
        stopScanner();
        const modal = bootstrap.Modal.getInstance(document.getElementById('scanQrModal'));
        if (modal) modal.hide();
        
        Swal.fire({
            title: '<span class="text-maroon">Verified Hub</span>',
            html: `Item Identifier: <b>${text.split('/').pop()}</b><br>Proceed to Transaction details?`,
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#800000',
            confirmButtonText: 'OPEN',
            reverseButtons: true
        }).then((res) => { 
            if (res.isConfirmed) window.location.href = text; 
        });
    }

    async function startCamera() {
        try {
            if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
            if (html5QrCode.isScanning) await html5QrCode.stop();
            
            const config = { fps: 10, qrbox: {width: 250, height: 250} };
            
            // Try back camera first, fallback to user camera if environment fails
            html5QrCode.start({ facingMode: "environment" }, config, handleScanSuccess)
                .catch(() => {
                    html5QrCode.start({ facingMode: "user" }, config, handleScanSuccess);
                });
        } catch (e) {
            console.error(e);
        }
    }

    async function stopScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            try { await html5QrCode.stop(); } catch(e) {}
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('scanQrModal');
        const fileInput = document.getElementById('qr-input-file');

        modalEl.addEventListener('shown.bs.modal', startCamera);
        modalEl.addEventListener('hidden.bs.modal', stopScanner);

        document.getElementById('tab-upload-btn').addEventListener('shown.bs.tab', stopScanner);
        document.getElementById('tab-camera-btn').addEventListener('shown.bs.tab', startCamera);

        fileInput.addEventListener('change', e => {
            if (e.target.files.length === 0) return;
            if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
            
            Swal.fire({ title: 'Processing Image...', didOpen: () => Swal.showLoading() });
            
            html5QrCode.scanFile(e.target.files[0], true)
                .then(decodedText => { 
                    Swal.close(); 
                    handleScanSuccess(decodedText); 
                })
                .catch(err => {
                    Swal.fire({ title: 'Scan Error', text: 'QR not detected in image.', icon: 'error' });
                    fileInput.value = "";
                });
        });
    });
</script>
@endsection