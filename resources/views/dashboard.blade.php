@extends('layouts.ispsc')

@section('title', 'Dashboard | ISPSC ONWARDS UIP')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    :root { 
        --ispsc-maroon: #800000; 
        --ispsc-yellow: #FFCC00; 
        --ispsc-gold: #d4a017; 
        --ispsc-green: #1b5e20;
        --ispsc-blue: #0056b3;
    }
    .main-content-fluid { width: 100%; max-width: 100%; padding: 0 40px; }
    .stat-card { min-height: 160px; border: none; border-radius: 15px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); }
    
    /* Dynamic Table Header Class */
    .dynamic-header { color: white !important; }
    .dynamic-header h5, .dynamic-header i { color: white !important; }
    
    /* Search Bar Styling */
    .search-input-custom { height: 45px; font-size: 0.95rem; border: none !important; }
    .search-btn-custom { background-color: rgba(0,0,0,0.2); color: white; border: none; }
    .search-btn-custom:hover { background-color: rgba(0,0,0,0.4); }

    .table thead th { border-top: none; font-size: 0.85rem; letter-spacing: 1px; padding: 18px; background-color: #f8f9fa; }
    .table tbody td { padding: 18px; font-size: 1rem; }
    nav svg { max-height: 20px; }
    .nav-pills .nav-link { color: #800000; border-radius: 4px; }
.nav-pills .nav-link.active { background-color: #800000 !important; color: #fff !important; }
.border-dashed { border: 2px dashed #dee2e6 !important; }
    /* Ensure Scanner Tabs are high-contrast */
    #scanQrModal .nav-pills .nav-link {
        color: #800000 !important; /* Maroon text for the inactive tab */
        background-color: #ffffff; /* White background */
        border: 1px solid #dee2e6; /* Gray border so it's defined */
        transition: 0.3s;
        font-weight: 700;
    }

    /* Selected Tab Styling */
    #scanQrModal .nav-pills .nav-link.active {
        background-color: #800000 !important; /* Maroon background */
        color: #ffffff !important; /* White text */
        border-color: #800000 !important;
    }

    #scanQrModal .nav-pills .nav-link:hover:not(.active) {
        background-color: #fff9e6; /* Light yellow hover for inactive tab */
    }
        /* SCANNER MODAL UI FIXES */
    #scanQrModal .modal-content { border-radius: 15px; }
    #scanQrModal .tab-pane { background-color: #ffffff; min-height: 320px; }
    
    /* Upload Zone Styling */
    .qr-upload-box {
        border: 2px dashed #dee2e6;
        background-color: #f8f9fa;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .qr-upload-box:hover {
        border-color: #800000;
        background-color: #fff9f9;
    }
    .qr-upload-box i { color: #800000; opacity: 0.3; }
    .qr-upload-box:hover i { opacity: 0.8; transform: translateY(-5px); transition: 0.3s; }

    /* Customizing the "Choose File" text logic */
    #qr-input-file { opacity: 0; position: absolute; z-index: -1; }

    /* Footer Button Fix */
    .btn-close-scanner {
        border: 2px solid #800000;
        color: #800000;
        font-weight: 800;
        letter-spacing: 1px;
    }
    .btn-close-scanner:hover {
        background-color: #800000;
        color: #ffffff;
    }
    /* SCANNER MODAL UI FIXES */
    #scanQrModal .modal-content { border-radius: 15px; }
    #scanQrModal .tab-pane { background-color: #ffffff; min-height: 320px; }
    
    /* Upload Zone Styling */
    .qr-upload-box {
        border: 2px dashed #dee2e6;
        background-color: #f8f9fa;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 250px;
    }
    .qr-upload-box:hover {
        border-color: #800000;
        background-color: #fff9f9;
    }
    .nav-pills .nav-link { color: #800000; border-radius: 4px; transition: 0.3s; }
    .nav-pills .nav-link.active { background-color: #800000 !important; color: #fff !important; }
    .qr-upload-box i { color: #800000; opacity: 0.3; }
    .qr-upload-box:hover i { opacity: 0.8; transform: translateY(-5px); transition: 0.3s; }

    /* Customizing the "Choose File" text logic */
    #qr-input-file { opacity: 0; position: absolute; z-index: -1; }

    /* Footer Button Fix */
    .btn-close-scanner {
        border: 2px solid #800000;
        color: #800000;
        font-weight: 800;
        letter-spacing: 1px;
    }
    .btn-close-scanner:hover {
        background-color: #800000;
        color: #ffffff;
    }
</style>
@endpush

@section('content')
@php
    /** 
     * DEFINE GLOBAL VARIABLES AT THE TOP 
     * This prevents "Undefined variable" errors 
     */
    $filter = request('filter');
    $currUser = Auth::user();
    
    // 1. Logic for Card Colors
    $activeColor = match($filter) {
        'pending' => '#800000',  // Maroon
        'accepted' => '#1b5e20', // Green
        'shared'   => '#0056b3', // Blue
        default    => '#d4a017'  // Gold
    };

    // 2. Priority Logic for "In Transit" tables
    $priorities = [
        ['level' => 3, 'title' => 'Extremely Urgent', 'icon' => 'fa-bolt', 'color' => '#dc3545', 'text' => 'white'], // Red
        ['level' => 2, 'title' => 'Urgent', 'icon' => 'fa-exclamation-triangle', 'color' => '#fd7e14', 'text' => 'white'], // Orange
        ['level' => 1, 'title' => 'Normal / Regular', 'icon' => 'fa-list-ul', 'color' => '#FFCC00', 'text' => 'dark'] // Yellow
    ];

    // 3. Logic for the bottom table (Archive)
    $archiveDocs = $documents->filter(function($doc) use ($currUser, $isAdminOrRecords, $filter) {
        $isFinished = ($doc->status == 'accepted');
        $isSharedToMe = $doc->logs->where('office_id', $currUser->office_id)->where('action', 'DISSEMINATED')->count() > 0;
        $isSharedSystemWide = $isAdminOrRecords && ($doc->logs->where('action', 'DISSEMINATED')->count() > 0);
        if($filter == 'shared') return $isSharedToMe || $isSharedSystemWide;
        if($filter == 'accepted') return $isFinished;
        return ($isFinished || $isSharedToMe || $isSharedSystemWide);
    })->sortByDesc('created_at');
@endphp

<div class="main-content-fluid">
<!-- HEADER SECTION: Fully Fluid and Aligned -->
<div class="d-flex justify-content-between align-items-end mb-4 pt-2">
    
    <!-- LEFT SIDE: TITLE & GREETING -->
    <div>
        <h1 class="fw-bold mb-1" style="color: #800000; font-size: 2.5rem;">System Dashboard</h1>
        <p class="text-muted mb-0 fs-5">
            Welcome back, <strong>{{ $currUser->username }}</strong> | 
            <span class="text-maroon fw-bold text-uppercase">
                {{ $currUser->office->office_name ?? 'Global Staff' }}
            </span>
        </p>
    </div>

    <!-- RIGHT SIDE: ACTION BUTTONS (NEW DOC & SCAN QR) -->
    <div class="d-flex gap-2 pb-1">
        <!-- 1. NEW DOCUMENT -->
        <a href="{{ route('documents.create') }}" class="btn btn-dark shadow-sm fw-bold px-4 py-3">
            <i class="fa fa-plus-circle me-2"></i> NEW DOCUMENT
        </a>

        <!-- 2. SCAN QR -->
        <button class="btn btn-dark shadow-sm fw-bold px-4 py-3" data-bs-toggle="modal" data-bs-target="#scanQrModal">
            <i class="fa fa-qrcode me-2"></i> SCAN QR
        </button>
    </div>
</div>

    <!-- STATISTICS CARDS -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #d4a017;">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase fw-bold opacity-80">All Records</h6>
                        <h1 class="display-3 fw-bold mb-0">{{ $countTotal }}</h1>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('dashboard', ['filter' => 'pending']) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #800000;">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase fw-bold opacity-80">In Transit</h6>
                        <h1 class="display-3 fw-bold mb-0">{{ $countPending }}</h1>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('dashboard', ['filter' => 'accepted']) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #1b5e20;">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase fw-bold opacity-80">Finished</h6>
                        <h1 class="display-3 fw-bold mb-0">{{ $countFinished }}</h1>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('dashboard', ['filter' => 'shared']) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #0056b3;">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase fw-bold opacity-80">Shared Copies</h6>
                        <h1 class="display-3 fw-bold mb-0">{{ $countShared }}</h1>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- DYNAMIC TITLE -->
    <div class="mb-4 d-flex align-items-center">
        <div style="width: 5px; height: 30px; background-color: {{ $activeColor }}; margin-right: 15px;"></div>
        <h3 class="fw-bold text-dark text-uppercase mb-0">
            @if($filter == 'shared') Shared Copies @elseif($filter == 'accepted') Finished Transactions @elseif($filter == 'pending') In Transit @else Active Records @endif
        </h3>
    </div>

    {{-- SECTION 1: PRIORITY TABLES (PENDING ONLY) --}}
    @if(!$filter || $filter == 'pending')
        @foreach($priorities as $prio)
            @php $prioDocs = $documents->where('priority', $prio['level'])->whereIn('status', ['pending', 'returned']); @endphp
            @if($prioDocs->count() > 0)
                <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
                    {{-- DYNAMIC PRIORITY COLORING --}}
                    <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center {{ $prio['text'] == 'dark' ? 'header-text-dark' : 'text-white' }}" 
                         style="background-color: {{ $prio['color'] }};">
                        <h5 class="mb-0 fw-bold text-uppercase tracking-wider">
                            <i class="fa {{ $prio['icon'] }} me-2"></i> {{ $prio['title'] }} (In Transit)
                        </h5>
                        <span class="badge bg-white {{ $prio['text'] == 'dark' ? 'text-dark' : 'text-danger' }} rounded-pill px-3 py-2 shadow-sm">
                            {{ $prioDocs->count() }} Items
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @include('documents.partials.table_content', [
                            'tableDocs' => $prioDocs, 
                            'color' => $prio['color'], // Buttons match priority color
                            'showRecipients' => false
                        ])
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    {{-- SECTION 2: CONSOLIDATED ARCHIVE TABLE --}}
    @if(!$filter || $filter == 'accepted' || $filter == 'shared')
        @if($archiveDocs->count() > 0)
            <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
                {{-- This header still follows the clicked Card Color (Gold/Green/Blue) --}}
                <div class="card-header py-4 px-4 d-flex flex-wrap justify-content-between align-items-center dynamic-header" style="background-color: {{ $activeColor }};">
                    <h5 class="mb-0 fw-bold text-uppercase tracking-wider">
                        <i class="fa {{ ($filter == 'shared') ? 'fa-share-nodes' : 'fa-archive' }} me-2"></i> 
                        {{ ($filter == 'shared') ? 'Dissemination Logs' : 'Finished & Shared Records' }}
                    </h5>
                    
                    <form action="{{ route('dashboard') }}" method="GET" class="mt-2 mt-lg-0">
                        @if($filter) <input type="hidden" name="filter" value="{{ $filter }}"> @endif
                        <div class="input-group shadow-sm" style="width: 550px;">
                            <input type="text" name="search" class="form-control border-0 px-3 search-input-custom" placeholder="Search archive..." value="{{ request('search') }}">
                            <button class="btn px-4 search-btn-custom" type="submit" style="background-color: #800000; color: #FFCC00;"><i class="fa fa-search"></i></button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0">
                    @include('documents.partials.table_content', [
                        'tableDocs' => $archiveDocs, 
                        'color' => $activeColor, 
                        'showRecipients' => ($isAdminOrRecords && $filter == 'shared')
                    ])
                </div>
            </div>
        @endif
    @endif

<!-- Modal: QR Scanner -->
<div class="modal fade" id="scanQrModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title fw-bold small text-uppercase">Tracking QR Code</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopScanner()"></button>
            </div>
            
            <!-- NAV PILLS -->
            <ul class="nav nav-pills nav-justified p-1 bg-light border-bottom" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active small py-2 fw-bold" data-bs-toggle="pill" data-bs-target="#camera-tab" type="button" id="tab-camera-btn">
                        <i class="fa fa-camera me-1"></i> USE CAMERA
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link small py-2 fw-bold" data-bs-toggle="pill" data-bs-target="#upload-tab" type="button" id="tab-upload-btn">
                        <i class="fa fa-file-image me-1"></i> UPLOAD IMAGE
                    </button>
                </li>
            </ul>

            <div class="modal-body p-0">
                <div class="tab-content">
                    <!-- Tab 1: CAMERA -->
                    <div class="tab-pane fade show active bg-dark" id="camera-tab" style="min-height: 350px;">
                        <div id="reader" style="width: 100%;"></div>
                        <div class="p-3 text-center">
                             <small class="text-white opacity-50 fw-bold small text-uppercase tracking-wider">Scanning live via camera...</small>
                        </div>
                    </div>

                    <!-- Tab 2: UPLOAD -->
                    <div class="tab-pane fade p-4" id="upload-tab" style="min-height: 350px; background: #fff;">
                        <label for="qr-input-file" class="qr-upload-box w-100 d-flex flex-column align-items-center justify-content-center p-5 text-center">
                            <i class="fa fa-cloud-upload-alt fa-4x mb-3 text-muted" style="opacity: 0.3;"></i>
                            <h5 class="fw-bold text-dark">Click to select image</h5>
                            <p class="text-muted small mb-0 px-4">Upload a screenshot or photo of the document's tracking QR code to start.</p>
                            <input type="file" id="qr-input-file" accept="image/*" class="d-none">
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-white border-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-danger w-100 py-2 fw-bold" data-bs-dismiss="modal">
                    CLOSE SCANNER
                </button>
            </div>
        </div>
    </div>
</div>

    {{-- EMPTY STATE --}}
    @if($documents->count() == 0)
        <div class="text-center py-5 bg-white rounded-3 shadow-sm mb-5 border border-dashed">
            <i class="fa fa-folder-open fa-5x text-muted opacity-10 mb-3"></i>
            <h4 class="text-muted">No documents found.</h4>
        </div>
    @endif

    @if(method_exists($documents, 'hasPages') && $documents->hasPages())
        <div class="d-flex justify-content-center pb-5">{{ $documents->links() }}</div>
    @endif
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrCode = null;

    // Restart/Start Function
    function initiateCamera() {
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }
        
        // Stop any active scan before restarting
        if (html5QrCode.isScanning) {
            html5QrCode.stop().then(() => startScanning());
        } else {
            startScanning();
        }
    }

    function startScanning() {
        const config = { fps: 15, qrbox: { width: 250, height: 250 } };
        html5QrCode.start(
            { facingMode: "environment" }, 
            config,
            (decodedText) => {
                window.location.href = decodedText;
                stopScanner();
            }
        ).catch(err => console.error("Camera access error:", err));
    }

    function stopScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(err => console.error(err));
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('scanQrModal');

        // Logic for first open
        modalElement.addEventListener('shown.bs.modal', function () {
            initiateCamera();
        });

        // Logic for TAB SWITCHING
        const cameraBtn = document.getElementById('tab-camera-btn');
        const uploadBtn = document.getElementById('tab-upload-btn');

        cameraBtn.addEventListener('shown.bs.tab', function () {
            initiateCamera(); // Restart camera when this tab is selected
        });

        uploadBtn.addEventListener('shown.bs.tab', function () {
            stopScanner(); // Pause camera while uploading file
        });

        // Handle File Selection
        document.getElementById('qr-input-file').addEventListener('change', function(e) {
            if (e.target.files.length === 0) return;
            const tempScanner = new Html5Qrcode("reader");
            tempScanner.scanFile(e.target.files[0], true)
                .then(decodedText => { window.location.href = decodedText; })
                .catch(err => { alert("No valid QR code found."); });
        });

        // Full cleanup when closing modal
        modalElement.addEventListener('hidden.bs.modal', stopScanner);
    });
</script>
@endsection