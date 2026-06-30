@extends('layouts.ispsc')

@section('title', 'New Virtual Action Slip')

@push('css')
<style>
    :root { 
        --ispsc-maroon: #800000; 
        --ispsc-yellow: #FFCC00;
        --bg-light: #f4f7f9;
    }

    body { background-color: var(--bg-light); font-size: 14px; color: #333; overflow-x: hidden; }
    
    /* Responsive Wrapper */
    .main-content-fluid { width: 100%; padding: 10px 15px; }
    @media (min-width: 992px) { .main-content-fluid { padding: 20px 40px; } }

    .tracer-card { 
        background: #fff; border: 1px solid #e1e8ed; border-radius: 12px; 
        margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
        position: relative;
    }
    .tracer-card-header { 
        padding: 12px 15px; border-bottom: 1px solid #f1f1f1; 
        display: flex; justify-content: space-between; align-items: center; background: #fff; 
        border-radius: 12px 12px 0 0;
    }
    .tracer-card-header h6 { margin: 0; font-weight: 800; color: #000; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }

    .form-label { font-weight: 700; color: #666; font-size: 10px; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }

    .form-control, .form-select {
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        padding: 12px 15px; /* Slightly larger for touch */
        font-size: 14px;
        color: #1a1a1a;
        background-color: #ffffff;
        transition: 0.3s;
    }
    .form-control:focus, .form-select:focus { border-color: var(--ispsc-maroon); outline: none; box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.05); }

    /* Signer Blocks */
    .signer-block { background-color: #fcfcfc; border: 2px solid #e1e8ed; border-radius: 10px; padding: 12px; margin-bottom: 12px; }
    @media (min-width: 768px) { .signer-block { padding: 20px; } }

    .btn-docu { border-radius: 10px; font-weight: 800; text-transform: uppercase; font-size: 13px; padding: 14px 30px; transition: 0.3s; border: none; }
    .btn-maroon { background: var(--ispsc-maroon); color: #fff; }
    .btn-maroon:hover { background: #600000; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(128,0,0,0.1); }
    
    /* Urgency Buttons Fix */
    @media (max-width: 576px) {
        .urgency-group { flex-direction: column; display: flex; }
        .urgency-group label { border-radius: 8px !important; margin-bottom: 5px; border: 2px solid #e1e8ed !important; }
        .urgency-group .btn-check:checked + .btn { background-color: var(--ispsc-maroon); color: white; border-color: var(--ispsc-maroon) !important; }
    }

    /* Floating Submit for Mobile */
    @media (max-width: 768px) {
        .submit-container { position: sticky; bottom: 0; z-index: 1000; padding: 15px; background: rgba(244, 247, 249, 0.9); backdrop-filter: blur(10px); border-top: 1px solid #e1e8ed; margin-left: -15px; margin-right: -15px; }
        .btn-submit-mobile { width: 100%; }
    }
</style>
@endpush

@section('content')
@php 
    $isAdminOrRecords = (Auth::user()->role === 'superadmin' || str_contains(Auth::user()->office_id ?? '', '-REC-'));
@endphp

<div class="main-content-fluid animate__animated animate__fadeIn">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="mainDocForm">
                @csrf

                <!-- SECTION 1: ORIGIN -->
                <div class="tracer-card">
                    <div class="tracer-card-header"><h6>Creator Information</h6></div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">User Handle</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->username }}" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Home Office Hub</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->office->office_name ?? 'No Office Assigned' }}" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Active Campus</label>
                                <input type="text" class="form-control" value="MAIN CAMPUS" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: CLASSIFICATION -->
                <div class="tracer-card">
                    <div class="tracer-card-header"><h6>Transaction Details</h6></div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-12 @if($isAdminOrRecords) col-md-6 @endif">
                                <label class="form-label">Classification Type</label>
                                <select name="classification" id="classSelect" class="form-select" onchange="toggleOthers(this.value)" required>
                                    <option value="" disabled {{ !old('classification') ? 'selected' : '' }}>Select classification...</option>
                                    @foreach(['Memorandum', 'Special Order', 'Communication Letter', 'Purchase Request', 'Device/Equipment', 'Others'] as $opt)
                                        <option value="{{ $opt }}" {{ old('classification') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                <div id="otherInputContainer" class="mt-3 {{ old('classification') == 'Others' ? '' : 'd-none' }}">
                                    <label class="form-label text-maroon">Manual Specification</label>
                                    <input type="text" name="custom_title" value="{{ old('custom_title') }}" class="form-control" placeholder="Enter title...">
                                </div>
                            </div>

                            @if($isAdminOrRecords)
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Sequence Urgency</label>
                                    <div class="btn-group w-100 urgency-group" role="group">
                                        <input type="radio" class="btn-check" name="priority" id="prio3" value="3" {{ old('priority') == 3 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger" for="prio3">Ex. Urgent</label>
                                        
                                        <input type="radio" class="btn-check" name="priority" id="prio2" value="2" {{ old('priority') == 2 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning text-dark" for="prio2">Urgent</label>
                                        
                                        <input type="radio" class="btn-check" name="priority" id="prio1" value="1" {{ old('priority', 1) == 1 || !old('priority') ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="prio1">Normal</label>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <div class="custom-switch-wrapper d-flex align-items-center justify-content-between p-3 border rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white p-2 rounded border me-3 d-none d-sm-block"><i class="fa fa-box-open text-muted"></i></div>
                                        <div>
                                            <h6 class="mb-0 fw-bold" style="font-size: 13px;">Hard Copy Tracking</h6>
                                            <small class="text-muted d-block" style="font-size: 10px;">Enable physical receipt logs instead of digital signatures.</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-3">
                                        <input class="form-check-input" type="checkbox" name="is_hard_copy" id="hardCopyToggle" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: PERSONNEL CHAIN -->
                <div class="tracer-card">
                    <div class="tracer-card-header">
                        <h6>Approval / Office Chain</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div id="signatory-container">
                            <div class="signer-block">
                                <div class="row align-items-end g-3">
                                    <div class="col-12 col-md-9">
                                        <label class="form-label">Sequence #1: Responsible Office</label>
                                        <select name="signatory_offices[]" class="form-select signatory-dropdown" required>
                                            <option value="" disabled selected>Select Office Hub...</option>
                                            @foreach($offices as $off)
                                                <option value="{{ $off->id }}">{{ $off->office_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <button type="button" class="btn btn-light w-100 py-2 fw-bold text-muted border" style="font-size: 10px;" disabled>PRIMARY</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm mt-2 fw-bold px-4 shadow-sm" onclick="addSigner()">
                            <i class="fa fa-plus-circle me-1"></i> ADD OFFICE
                        </button>
                    </div>
                </div>

                <!-- SECTION 4: ROUTING & SOURCE -->
                <div class="tracer-card">
                    <div class="tracer-card-header"><h6>Source Attachment</h6></div>
                    <div class="card-body p-3 p-md-4">
                        <div id="softCopySection">
                            <label class="form-label">Digital Document (PDF/Images)</label>
                            <input type="file" name="doc_files[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple>
                        </div>
                        <div id="hardCopySection" class="d-none">
                            <label class="form-label text-maroon">Physical Item Description</label>
                            <input type="text" name="physical_description" class="form-control border-maroon" placeholder="e.g. 1 unit laptop, 3 folders of PR...">
                        </div>
                    </div>
                </div>

                <div class="submit-container text-center mb-5">
                    <button type="submit" class="btn-docu btn-maroon btn-submit-mobile">
                        SUBMIT TRANSACTION <i class="fa fa-paper-plane ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const officeOptions = `{!! $offices->map(fn($o) => "<option value='{$o->id}'>{$o->office_name}</option>")->join('') !!}`;

    function addSigner() {
        const container = document.getElementById('signatory-container');
        const count = container.children.length + 1;
        const html = `
            <div class="signer-block animate__animated animate__fadeInUp">
                <div class="row align-items-end g-3">
                    <div class="col-12 col-md-9">
                        <label class="form-label">Sequence #${count}: Responsible Office</label>
                        <select name="signatory_offices[]" class="form-select signatory-dropdown" required>
                            <option value="" disabled selected>Select Office Hub...</option>
                            ${officeOptions}
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="button" class="btn btn-outline-danger w-100 py-2 fw-bold" style="font-size: 10px;" onclick="this.closest('.signer-block').remove()">REMOVE STEP</button>
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function toggleOthers(val) { 
        document.getElementById('otherInputContainer').classList.toggle('d-none', val !== 'Others'); 
    }

    document.getElementById('hardCopyToggle').addEventListener('change', function() {
        document.getElementById('hardCopySection').classList.toggle('d-none', !this.checked);
        document.getElementById('softCopySection').classList.toggle('d-none', this.checked);
        const fileInput = document.querySelector('input[type="file"]');
        fileInput.required = !this.checked;
    });
</script>
@endpush