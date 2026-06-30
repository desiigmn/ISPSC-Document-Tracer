@extends('layouts.ispsc')

@section('title', 'Tracking: ' . $document->tracking_id)

@section('content')
@php
    $user = Auth::user();
    
    // 1. Role Identification
    $isRecordsOffice = str_contains($user->office_id ?? '', '-REC-');
    $isAdminOrRecords = ($user->role === 'superadmin' || $isRecordsOffice);
    
    // 2. Possession Logic: Find the signatory record for the CURRENT step
    $currentOfficeSig = $document->signatories->where('sign_order', $document->current_step)->first();
    
    // 3. Authorization Logic: It is "My Turn" if my office is the current holder
    $isMyTurn = ($document->status == 'pending' && 
                 $currentOfficeSig && 
                 $currentOfficeSig->office_id == $user->office_id &&
                 $currentOfficeSig->status == 'pending');

    $isFinalStep = ($document->current_step == $document->signatories->max('sign_order'));
    
    // 4. Physical vs Soft Copy
    $isActuallyPhysical = ($document->is_hard_copy == 1 || $document->file_path == 'PHYSICAL_ITEM' || empty($document->file_path));

    // 5. Extraction: Creator Explanations (From re-validation)
    $revalidationLog = $document->logs->where('action', 'RE-VALIDATED')->sortByDesc('created_at')->first();
    $uploaderExplanation = $revalidationLog ? str_replace('CREATOR EXPLANATION: ', '', $revalidationLog->remarks) : null;

    // 6. Extraction: Final Remarks (For completed documents)
    $finalRemarks = null; 
    if ($document->status == 'accepted') {
        $finalLog = $document->logs->where('action', 'FINALIZED & ARCHIVED')->first();
        if ($finalLog && str_contains($finalLog->remarks ?? '', 'FINAL REMARKS:')) {
            $finalRemarks = str_replace('FINAL REMARKS: ', '', $finalLog->remarks);
        }
    }
@endphp

<style>
    :root { 
        --ispsc-maroon: #800000; 
        --ispsc-yellow: #FFCC00;
        --ispsc-blue: #0056b3;
    }

    body { font-size: 13px; color: #333; overflow-x: hidden; background-color: #f4f7f9; }
    .view-wrapper-fluid { width: 100%; padding: 0 10px; }
    @media (min-width: 992px) { .view-wrapper-fluid { padding: 0 40px; } }

    .tracer-card { background: #fff; border: 1px solid #e1e8ed; border-radius: 12px; margin-bottom: 15px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; }
    .tracer-card-header { padding: 12px 20px; border-bottom: 1px solid #f1f1f1; display: flex; justify-content: space-between; align-items: center; background: #fff; flex-wrap: wrap; gap: 10px; }
    .tracer-card-header h6 { margin: 0; font-weight: 800; color: #000; text-transform: uppercase; font-size: 12px; }

    /* Timeline Styling */
    .timeline-wrapper { overflow-x: auto; padding: 15px 0 !important; -webkit-overflow-scrolling: touch; }
    .h-timeline { display: flex; justify-content: space-between; position: relative; margin: 10px 0 !important; padding: 0 30px; min-width: 800px; }
    .h-timeline::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: #e1e8ed; z-index: 1; }
    .t-node { position: relative; z-index: 2; text-align: center; width: 100%; }
    .t-dot { width: 30px; height: 30px; background: #fff; border: 3px solid #e1e8ed; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 10px; transition: 0.3s; }
    
    .t-node.active .t-dot { border-color: var(--ispsc-yellow); background: var(--ispsc-yellow); color: #000; box-shadow: 0 0 0 4px rgba(255, 204, 0, 0.2); }
    .t-node.completed .t-dot { border-color: var(--ispsc-blue); background: var(--ispsc-blue); color: #fff; }
    .t-node.returned .t-dot { border-color: #dc3545; background: #dc3545; color: #fff; }
    .t-label { font-size: 10px; font-weight: 800; display: block; color: #333; text-transform: uppercase; line-height: 1.2; padding: 0 5px; word-break: break-word; }

    /* Modals & Buttons */
    #signatureModal .modal-content, #resubmitModal .modal-content, #returnModal .modal-content, #trailModal .modal-content, #disseminateModal .modal-content { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 50px rgba(0,0,0,0.3); }
    .modal-header { background-color: #1a1a1a; color: white; padding: 15px 25px; border: none; }
    .nav-pills { background: #f4f7f9; padding: 6px; border-radius: 12px; }
    .nav-link.active { background-color: var(--ispsc-maroon) !important; color: white !important; }
    
    #sig-canvas { background-color: #ffffff; border: 2px dashed #e1e8ed; border-radius: 12px; cursor: crosshair; touch-action: none; width: 100%; }
    #clear-sig { font-weight: 900; font-size: 11px; color: var(--ispsc-maroon) !important; text-transform: uppercase; text-decoration: none; }
    
    .btn-dark-theme { background-color: #1a1a1a; color: white; border: none; border-radius: 12px; padding: 12px; font-weight: 900; transition: 0.3s; }
    .btn-dark-theme:hover { background-color: var(--ispsc-maroon); transform: translateY(-2px); }
    .btn-docu { border-radius: 8px; font-weight: 800; text-transform: uppercase; font-size: 11px; padding: 8px 16px; border: none; }
    .btn-maroon { background-color: var(--ispsc-maroon); color: white; border-radius: 8px; font-weight: 800; text-transform: uppercase; font-size: 11px; padding: 8px 16px; border: none; }

    .viewer-container { height: 60vh; min-height: 400px; background-color: #525659; }
    @media (max-width: 768px) { .viewer-container { height: 50vh; min-height: 300px; } }
    
    .italic { font-style: italic; }
    .tracking-id-text { word-break: break-all; }

    /* Responsive Status Bar */
    .status-bar-row { display: flex; flex-wrap: wrap; }
    .status-badge-col { flex: 0 0 150px; }
    @media (max-width: 576px) {
        .status-badge-col { flex: 1 1 100%; border-end: none !important; border-bottom: 1px solid #eee; padding: 15px !important; }
        .tracer-card-header { flex-direction: column; align-items: flex-start; }
        .tracer-card-header .d-flex { width: 100%; justify-content: space-between; }
    }

    /* Table Responsiveness for Trail */
    .audit-table thead { background: #f8f9fa; }
    @media (max-width: 768px) {
        .audit-table thead { display: none; }
        .audit-table tr { display: block; border-bottom: 2px solid #eee; margin-bottom: 10px; }
        .audit-table td { display: flex; justify-content: space-between; padding: 10px !important; border: none !important; text-align: right; }
        .audit-table td::before { content: attr(data-label); font-weight: 800; text-transform: uppercase; font-size: 10px; color: #888; float: left; text-align: left; }
    }
    /* NATIVE MODAL STYLES (No Bootstrap Required) */
.native-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.native-modal-content {
    background: white;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.native-modal-header {
    background: #1a1a1a;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.native-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex-grow: 1;
}
.close-native {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
}
</style>

<div class="view-wrapper-fluid py-3 py-md-4 animate__animated animate__fadeIn">   
    
    <!-- SECTION 1: STATUS BAR -->
    <div class="tracer-card mb-4">
        <div class="card-body p-0">
            <div class="row g-0 align-items-center status-bar-row">
                <div class="col-md-auto p-4 border-end text-center status-badge-col">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">State</span>
                    @php
                        $statusColor = match($document->status) {
                            'accepted' => '#e6f4ea', 'returned' => '#fdf2f2', 'pending' => '#fffbeb', 'needs_review' => '#e7f1ff', default => '#f3f4f6'
                        };
                        $textColor = match($document->status) {
                            'accepted' => '#1e7e34', 'returned' => '#dc3545', 'pending' => '#856404', 'needs_review' => '#0d6efd', default => '#374151'
                        };
                    @endphp
                    <span class="badge px-3 py-2" style="background-color: {{ $statusColor }}; color: {{ $textColor }}; border-radius: 6px; font-weight: 900; font-size: 12px;">
                        {{ strtoupper($document->status == 'needs_review' ? 'FOR REVIEW' : $document->status) }}
                    </span>
                </div>
                <div class="col p-4 bg-light bg-opacity-50">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Global Tracking ID</span>
                    <span class="tracking-id-text fw-black text-maroon h4 mb-0" style="font-family: monospace;">{{ $document->tracking_id }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-lg-4">
        <div class="col-lg-8">
            <!-- SECTION 2: TIMELINE -->
            <div class="tracer-card">
                <div class="tracer-card-header">
                    <h6>Movement Timeline</h6>
                    <span class="d-md-none text-muted" style="font-size: 10px;"><i class="fa fa-arrow-right me-1"></i> Swipe to view all steps</span>
                </div>
                <div class="card-body px-0 px-md-4">
                    <div class="timeline-wrapper">
                        <div class="h-timeline">
                            <div class="t-node completed">
                                <div class="t-dot"><i class="fa fa-door-open"></i></div>
                                <span class="t-label">Origin</span>
                            </div>
                            @foreach($document->signatories->sortBy('sign_order') as $sig)
                                @php
                                    $isCurrentStep = ($document->current_step == $sig->sign_order && $document->status == 'pending');
                                    $isDone = ($sig->status == 'signed');
                                    $isRet = ($document->current_step == $sig->sign_order && $document->status == 'returned');
                                    $state = $isDone ? 'completed' : ($isRet ? 'returned' : ($isCurrentStep ? 'active' : ''));
                                    $icon = $isDone ? 'fa-check' : ($isRet ? 'fa-undo' : ($isCurrentStep ? 'fa-spinner fa-spin' : 'fa-circle'));
                                @endphp
                                <div class="t-node {{ $state }}">
                                    <div class="t-dot"><i class="fa {{ $isCurrentStep && $document->status == 'pending' ? 'fa-hand-holding' : $icon }}"></i></div>
                                    <span class="t-label {{ $isCurrentStep ? 'text-maroon fw-black' : '' }}">
                                        {{ $sig->user->username ?? ($sig->office->office_name ?? 'Designated Office') }}
                                    </span>
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

            {{-- 3A. CORRECTION REQUIRED (FOR UPLOADER) --}}
            @if($document->status == 'returned' && $document->uploader_id == Auth::id())
                <div class="tracer-card border-danger mb-3">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-danger fw-black text-uppercase small mb-1" style="font-size: 11px;">Correction Required</h6>
                        <p class="small text-muted italic mb-3">"REASON: {{ $document->logs->where('action', 'DOCUMENT RETURNED')->last()->remarks ?? 'N/A' }}"</p>
                        <button class="btn btn-danger btn-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#resubmitModal">
                            <i class="fa fa-upload me-2"></i>Provide Correction
                        </button>
                    </div>
                </div>
            @endif

            {{-- 3B. FINAL REMARKS (FOR CREATOR) --}}
            @if($document->status == 'accepted' && $finalRemarks)
                <div class="tracer-card border-0 shadow-sm animate__animated animate__fadeInDown mb-3" style="background: #f0fdf4; border-left: 5px solid #16a34a !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa fa-check-circle text-success me-2"></i>
                            <h6 class="fw-black text-success text-uppercase m-0" style="font-size: 11px; letter-spacing: 0.5px;">Final Remarks / Conclusion</h6>
                        </div>
                        <div class="p-3 bg-white bg-opacity-50 rounded border border-success border-opacity-10">
                            <p class="mb-0 text-dark fw-bold italic" style="font-size: 14px; line-height: 1.6;">"{{ $finalRemarks }}"</p>
                        </div>
                        <div class="mt-2 text-muted small" style="font-size: 10px;">Completed on: {{ $document->updated_at->format('M d, Y | h:i A') }}</div>
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
                <div class="card-body p-0 bg-pro-grey viewer-container">
                    @if($isActuallyPhysical)
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-white p-4 p-md-5 text-center">
                            <i class="fa fa-box-archive fa-4x text-muted opacity-25 mb-3"></i>
                            <h5 class="fw-black text-uppercase">Hard Copy Item</h5>
                            <p class="text-muted small">Physical possession confirmation required below.</p>
                        </div>
                    @else
                        <iframe id="doc-iframe" src="{{ url('/document/live-preview/' . $document->id) }}#toolbar=0" width="100%" height="100%" style="border: none;"></iframe>
                    @endif
                </div>
            </div>

            <!-- 3D. ACTION PANEL -->
            @if($isMyTurn)
                <div class="tracer-card border-0 shadow-sm animate__animated animate__pulse" style="background-color: #fffbeb; border-top: 5px solid var(--ispsc-yellow) !important;">
                    <div class="card-body p-3 p-md-4 text-center">
                        @if($uploaderExplanation)
                            <div class="alert border-0 bg-white mb-4 shadow-sm text-start" style="border-left: 5px solid var(--ispsc-blue) !important;">
                                <h6 class="fw-black text-primary text-uppercase small mb-1">Creator Note:</h6>
                                <p class="mb-0 italic small text-dark">"{{ $uploaderExplanation }}"</p>
                            </div>
                        @endif

                        @if($document->is_hard_copy && !$currentOfficeSig->is_physically_received)
                            <div class="mb-3">
                                <i class="fa fa-truck-loading fa-3x text-muted opacity-50 mb-3"></i>
                                <h6 class="fw-black text-uppercase m-0">Physical Receipt Required</h6>
                                <p class="text-muted small mt-2">Confirm once the paper is at your desk.</p>
                            </div>
                            <button class="btn btn-primary w-100 py-3 fw-black shadow-sm" id="btn-confirm-receipt">
                                <i class="fa fa-hand-holding me-2"></i> I HAVE RECEIVED THE HARD COPY
                            </button>
                        @else
                            <div class="mb-3"><h6 class="fw-black text-uppercase m-0">Confirm & Process Document</h6></div>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <button class="btn btn-success flex-fill py-3 fw-black shadow-sm" data-bs-toggle="modal" data-bs-target="#signatureModal">
                                    <i class="fa fa-stamp me-2"></i> {{ $isFinalStep ? 'FINALIZE & ARCHIVE' : 'APPLY SIGNATURE / STAMP' }}
                                </button>
                                <button class="btn btn-outline-danger px-4 py-3 py-sm-0 fw-bold" data-bs-toggle="modal" data-bs-target="#returnModal">
                                    <i class="fa fa-undo me-1"></i> RETURN
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="col-lg-4">
            <div class="tracer-card text-center p-4">
                <h6 class="mb-3">Document QR Code</h6>
                <div class="bg-light p-3 rounded mb-3 d-inline-block border" id="printableQR" style="background: #fff !important; max-width: 100%;">
                    {!! $qrCode !!}
                </div>
                <button type="button" onclick="downloadQRAsPNG()" class="btn btn-outline-dark btn-docu w-100"><i class="fa fa-download me-2"></i> Download PNG</button>
            </div>

            <div class="tracer-card">
                <div class="tracer-card-header"><h6>Tracking Info</h6></div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Current Possession:</span>
                        <div class="p-2 rounded bg-light border-start border-4 border-maroon">
                            <span class="fw-black text-dark" style="font-size: 12px;">
                                <i class="fa fa-map-marker-alt text-maroon me-1"></i>
                                @if($document->status == 'accepted')
                                    RECORDS OFFICE (ARCHIVED)
                                @elseif($document->status == 'returned')
                                    CREATOR (FOR CORRECTION)
                                @else
                                    {{ strtoupper($currentOfficeSig->office->office_name ?? 'RECORDS OFFICE') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="mb-2 d-flex justify-content-between gap-2">
                        <span class="text-muted fw-bold">Origin:</span>
                        <span class="fw-bold small text-end">{{ $document->uploader->office->office_name ?? 'RECORDS' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted fw-bold">Priority:</span>
                        @php $pMap=[1=>'Normal',2=>'Urgent',3=>'Ex. Urgent']; $pCol=match((int)$document->priority){2=>'#d97706',3=>'#dc3545',default=>'#333'}; @endphp
                        <span class="small fw-bold" style="color:{{$pCol}}">{{$pMap[$document->priority]??'NOR'}}</span>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 p-3 d-flex flex-column gap-2">
                    <button class="btn btn-dark btn-docu w-100 py-2" onclick="toggleModal('trailModal', true)">Audit Trail</button>
                    @if($document->status == 'accepted' && $isAdminOrRecords)
                        <button class="btn btn-maroon btn-docu w-100" onclick="toggleModal('disseminateModal', true)">Share Copies</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS SECTION --}}
<!-- 1. NATIVE AUDIT TRAIL MODAL -->
<div id="trailModal" class="native-modal-overlay">
    <div class="native-modal-content" style="max-width: 1000px;">
        <div class="native-modal-header">
            <h6 class="m-0 fw-bold">DOCUMENT AUDIT TRAIL</h6>
            <button class="close-native" onclick="toggleModal('trailModal', false)">&times;</button>
        </div>
        <div class="native-modal-body p-0">
            <table class="table audit-table align-middle mb-0 w-100">
                <thead class="bg-light">
                    <tr class="small text-secondary fw-bold text-uppercase">
                        <th class="ps-4 py-3">Timestamp</th>
                        <th class="py-3">Action</th>
                        <th class="py-3">Personnel</th>
                        <th class="py-3">Remarks</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    @foreach($document->logs->sortByDesc('created_at') as $log)
                    <tr>
                        <td class="ps-4 text-muted" data-label="Timestamp">{{ $log->created_at->format('M d, Y | h:i A') }}</td>
                        <td data-label="Action"><span class="badge border border-dark text-dark">{{ strtoupper($log->action) }}</span></td>
                        <td class="fw-bold" data-label="Personnel">{{ $log->user->username ?? 'SYSTEM' }}</td>
                        <td class="text-muted italic" data-label="Remarks">{{ $log->remarks ?? '---' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 2. NATIVE SHARE COPIES MODAL -->
<div id="disseminateModal" class="native-modal-overlay">
    <div class="native-modal-content" style="max-width: 500px;">
        <form action="{{ route('documents.disseminate', $document->id) }}" method="POST">
            @csrf
            <div class="native-modal-header">
                <h6 class="m-0 fw-bold">DISSEMINATE RECORDS</h6>
                <button type="button" class="close-native" onclick="toggleModal('disseminateModal', false)">&times;</button>
            </div>
            <div class="native-modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="small text-muted fw-bold">Select target offices:</span>
                    <button type="button" class="btn btn-sm btn-link text-maroon p-0 fw-bold" id="selectAllOffices" style="text-decoration:none; font-size:11px;">SELECT ALL</button>
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach(\App\Models\Office::where('id', '!=', Auth::user()->office_id)->orderBy('office_name','asc')->get() as $off)
                        <div style="margin-bottom: 8px;">
                            <input type="checkbox" class="disseminate-check" name="office_ids[]" value="{{ $off->id }}" id="off{{ $off->id }}">
                            <label for="off{{ $off->id }}" class="small fw-bold">{{ $off->office_name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="p-3">
                <button type="submit" class="btn btn-maroon w-100 py-2 fw-black">SEND OFFICIAL COPIES</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. RESUBMIT / CORRECTION MODAL -->
<div class="modal fade" id="resubmitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h6 class="modal-title fw-black text-uppercase small">Provide Correction</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-justified mb-4" id="correctionTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold small" data-bs-toggle="pill" data-bs-target="#tab-reupload">RE-UPLOAD PDF</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold small" data-bs-toggle="pill" data-bs-target="#tab-explain">EXPLAIN / DISPUTE</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-reupload">
                        <form action="{{ route('documents.resubmit', $document->tracking_id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label small fw-bold mb-2">Attach Revised Document(s)</label>
                            <input type="file" name="doc_files[]" class="form-control mb-3" required multiple accept=".pdf,.jpg,.jpeg,.png">
                            <p class="text-muted" style="font-size: 10px;">This will replace old files and restart the signature sequence.</p>
                            <button type="submit" class="btn btn-danger w-100 fw-black text-uppercase mt-2">Submit Revised Document</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab-explain">
                        <form action="{{ route('documents.revalidate', $document->tracking_id) }}" method="POST">
                            @csrf
                            <label class="form-label small fw-bold mb-2">Justification / Explanation</label>
                            <textarea name="explanation" class="form-control mb-3" rows="4" placeholder="Explain why the current document is correct..." required></textarea>
                            <button type="submit" class="btn btn-dark w-100 fw-black text-uppercase">Send Explanation to Signer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. SIGNATURE MODAL -->
<div class="modal fade" id="signatureModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title text-uppercase" style="font-size: 14px;">
                    <i class="fa {{ $isActuallyPhysical ? 'fa-check-circle' : 'fa-pen-fancy' }} me-2"></i> 
                    {{ $isActuallyPhysical ? 'Confirm Action' : 'Record Authentication' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-3 p-md-4">
                @if(!$isActuallyPhysical)
                    <ul class="nav nav-pills nav-justified" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-2" data-bs-toggle="pill" data-bs-target="#draw-tab">Draw Signature</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2" data-bs-toggle="pill" data-bs-target="#upload-tab">Upload Image</button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="draw-tab">
                            <canvas id="sig-canvas" style="height: 200px;"></canvas>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-link p-0" id="clear-sig">Clear Canvas</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="upload-tab">
                            <div class="p-4 border rounded-3 text-center bg-light">
                                <input type="file" id="sig-file" class="form-control form-control-sm" accept="image/*">
                                <img id="sig-preview" class="d-none border rounded w-100 mt-2 shadow-sm">
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fa fa-file-alt fa-3x text-muted mb-3"></i>
                        <h6 class="fw-bold">Physical Document Confirmation</h6>
                        <p class="text-muted small">You are processing a **Hard Copy**. Digital signature is not required as the physical document is signed manually.</p>
                    </div>
                @endif

                <div class="mt-4 pt-3 border-top">
                    <label class="form-label fw-bold small text-uppercase mb-2">Conclusion & Final Remarks</label>
                    <textarea id="final-remarks" class="form-control" rows="3" placeholder="Enter instructions or summary before archiving..."></textarea>
                    <p class="text-muted mt-2 mb-0" style="font-size: 10px; font-weight: 700;">
                        <i class="fa fa-lock me-1"></i> This action will finalize and archive the document record.
                    </p>
                </div>
            </div>

            <div class="p-3 p-md-4 pt-0">
                <button type="button" class="btn btn-dark-theme w-100 text-uppercase" id="btn-submit-signature">
                    {{ $isFinalStep ? 'Finalize & Archive Record' : 'Confirm & Process' }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 5. RETURN MODAL -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-danger text-white py-2">
                <h5 class="modal-title fw-black small text-uppercase">Return Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <label class="form-label fw-bold small mb-2">Reason for returning:</label>
                <textarea id="return-remarks" class="form-control form-control-sm" rows="3" placeholder="Specify why the document is being returned..."></textarea>
            </div>
            <div class="modal-footer border-0 p-3 pt-0">
                <button type="button" class="btn btn-danger w-100 py-2 fw-bold" id="confirm-return">SUBMIT RETURN</button>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
    /**
     * 1. NATIVE MODAL TOGGLE
     * This opens the windows even if Bootstrap is blocked.
     */
    function toggleModal(modalId, show) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = show ? 'flex' : 'none';
        }
    }

    /**
     * 2. MAIN LOGIC
     */
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- SELECT ALL LOGIC (SHARE COPIES) ---
        const selectAllBtn = document.getElementById('selectAllOffices');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const checkboxes = document.querySelectorAll('.disseminate-check');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => cb.checked = !allChecked);
                this.innerText = !allChecked ? 'DESELECT ALL' : 'SELECT ALL';
            });
        }

        // --- CLOSE MODALS IF CLICKED OUTSIDE ---
        window.onclick = function(event) {
            if (event.target.classList.contains('native-modal-overlay')) {
                event.target.style.display = "none";
            }
        };

        // --- SIGNATURE CANVAS LOGIC ---
        let hasSigned = false;
        const canvas = document.getElementById('sig-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let drawing = false; 

            function initCanvas() { 
                const rect = canvas.getBoundingClientRect(); 
                const dpr = window.devicePixelRatio || 1; 
                canvas.width = rect.width * dpr; canvas.height = rect.height * dpr; 
                ctx.setTransform(1, 0, 0, 1, 0, 0); ctx.scale(dpr, dpr);
                ctx.strokeStyle = "#000"; ctx.lineWidth = 2; ctx.lineCap = "round"; 
            }
            
            // Try to init if the modal is already open, or when Bootstrap triggers it
            initCanvas();
            document.getElementById('signatureModal')?.addEventListener('shown.bs.modal', initCanvas);
            
            function getPos(e) { 
                const rect = canvas.getBoundingClientRect(); 
                const cx = e.touches ? e.touches[0].clientX : e.clientX; 
                const cy = e.touches ? e.touches[0].clientY : e.clientY; 
                return { x: cx - rect.left, y: cy - rect.top }; 
            }

            canvas.addEventListener('mousedown', (e) => { drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); });
            canvas.addEventListener('mousemove', (e) => { if(!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasSigned = true; });
            canvas.addEventListener('mouseup', () => drawing = false);
            
            document.getElementById('clear-sig')?.addEventListener('click', (e) => { 
                e.preventDefault(); ctx.clearRect(0, 0, canvas.width, canvas.height); hasSigned = false; 
            });
        }

        // --- SUBMIT SIGNATURE ---
        document.getElementById('btn-submit-signature')?.addEventListener('click', function() { 
            const isHardCopy = @json($isActuallyPhysical);
            if (!isHardCopy && !hasSigned) return Swal.fire('Required', 'Please sign.', 'error'); 
            
            this.disabled = true;
            let signatureData = null;
            if (!isHardCopy && canvas) {
                const isDrawing = document.getElementById('draw-tab').classList.contains('active'); 
                signatureData = isDrawing ? canvas.toDataURL('image/png') : document.getElementById('sig-preview').src;
            }

            fetch("{{ route('documents.sign', $document->tracking_id) }}", { 
                method: "POST", 
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" }, 
                body: JSON.stringify({ signature_data: signatureData, final_remarks: document.getElementById('final-remarks')?.value || null }) 
            }).then(res => res.ok ? window.location.reload() : Swal.fire('Error', 'Failed', 'error'));
        });

        // --- RETURN LOGIC ---
        document.getElementById('confirm-return')?.addEventListener('click', function() { 
            const remarks = document.getElementById('return-remarks')?.value.trim(); 
            if (!remarks) return Swal.fire('Required', 'Provide a reason.', 'warning');
            fetch('/document/return/{{ $document->id }}', { 
                method: "POST", 
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" }, 
                body: JSON.stringify({ remarks: remarks }) 
            }).then(res => res.ok ? window.location.href = "/" : Swal.fire('Error', 'Failed', 'error'));
        });
    });

    /**
     * 3. GLOBAL UTILITIES
     */
    function printIframe() {
        const iframe = document.getElementById('doc-iframe');
        if (iframe) { iframe.contentWindow.focus(); iframe.contentWindow.print(); }
    }

    function downloadQRAsPNG() { 
        const svgElement = document.querySelector('#printableQR svg'); 
        if(!svgElement) return;
        const svgData = new XMLSerializer().serializeToString(svgElement); 
        const canvas = document.createElement("canvas"); 
        canvas.width = 1000; canvas.height = 1000;
        const img = new Image(); 
        img.onload = function() { 
            const ctx = canvas.getContext("2d");
            ctx.fillStyle = "white"; ctx.fillRect(0, 0, 1000, 1000); 
            ctx.drawImage(img, 0, 0, 1000, 1000); 
            const a = document.createElement("a"); 
            a.href = canvas.toDataURL("image/png"); 
            a.download = "Document-QR-{{ $document->tracking_id }}.png"; 
            a.click(); 
        }; 
        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData))); 
    }
</script>
@endpush