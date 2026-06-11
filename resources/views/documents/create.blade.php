@extends('layouts.ispsc')

@section('title', 'New Virtual Action Slip')

@push('css')
<style>
    .ispsc-card { border-radius: 12px; border: none; }
    .card-header-ispsc { 
        background-color: var(--ispsc-maroon); 
        color: white; 
        border-radius: 12px 12px 0 0 !important;
        font-weight: bold;
        letter-spacing: 1px;
    }
    .form-label { font-weight: 600; color: #444; }
    .section-title {
        border-left: 5px solid var(--ispsc-maroon);
        padding-left: 10px;
        margin-bottom: 20px;
        color: var(--ispsc-maroon);
        font-weight: 800;
        text-transform: uppercase;
    }
    .signer-block {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid #dee2e6;
    }
    .btn-maroon { background-color: var(--ispsc-maroon); color: var(--ispsc-yellow); font-weight: bold; }
    .btn-maroon:hover { background-color: #600000; color: white; }
    
    .search-container { position: relative; }
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0 0 8px 8px;
        z-index: 1050; 
        max-height: 250px;
        overflow-y: auto;
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .search-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
        transition: background 0.2s;
    }
    .search-item:hover { background-color: #fff4f4; }
    .search-item:last-child { border-bottom: none; }

    .border-maroon-focus:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.25rem rgba(128, 0, 0, 0.25);
    }
    .text-maroon {
        color: #800000;
    }
    #classSelect {
        font-size: 0.9rem;
    }
    #classSelect optgroup {
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #666;
    }
    .btn-group .btn { flex: 1; border-width: 2px; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm border-0">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><i class="fa fa-exclamation-triangle me-2"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- CARD 1: CREATOR DETAILS -->
                <div class="card ispsc-card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-title">
                            <i class="fa fa-user-edit me-2"></i> Details of Creator
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="creator_name" class="form-control bg-light" value="{{ Auth::user()->username }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Campus</label>
                                @php
                                    $campuses = [
                                        '0001' => 'MAIN', '0010' => 'CANDON', '0011' => 'SANTIAGO',
                                        '0100' => 'STA MARIA', '0101' => 'TAGUDIN', '0110' => 'NARVACAN', '0111' => 'CERVANTES'
                                    ];
                                    $userCode = Auth::user()->campus_code;
                                    $campusName = $campuses[$userCode] ?? 'Not Set';
                                @endphp
                                <input type="text" class="form-control bg-light" value="{{ $campusName }} ({{ $userCode ?? 'N/A' }})" readonly>
                                <input type="hidden" name="campus_code" value="{{ $userCode }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Office</label>
                                <input type="text" class="form-control bg-light" value="{{ Auth::user()->office->office_name ?? 'No Office Assigned' }}" readonly>
                                <input type="hidden" name="creator_office_id" value="{{ Auth::user()->office_id }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: CLASSIFICATION & PRIORITY -->
                <div class="card ispsc-card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="section-title mb-4">
                            <i class="fa fa-tags me-2"></i> Classification & Priority
                        </div>
                        
                        <div class="row g-4 align-items-start">
                            <!-- Classification Dropdown -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-secondary">Document/Item Type</label>
                                <select name="classification" id="classSelect" class="form-select border-maroon-focus" onchange="toggleOthers(this.value)" required>
                                    <option value="" disabled {{ !old('classification') ? 'selected' : '' }}>Choose classification...</option>
                                    @foreach(['Memorandum', 'Special Order', 'Communication Letter', 'Purchase Request', 'Device/Equipment', 'Others'] as $opt)
                                        <option value="{{ $opt }}" {{ old('classification') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>

                                <!-- Hidden Input: Only appears if "Others" is selected -->
                                <div id="otherInputContainer" class="mt-3 {{ old('classification') == 'Others' ? '' : 'd-none' }}">
                                    <label class="form-label small fw-bold text-uppercase text-maroon" style="font-size: 0.75rem;">Specify Type</label>
                                    <input type="text" name="custom_title" id="otherClassification" value="{{ old('custom_title') }}" class="form-control form-control-sm border-maroon-focus" placeholder="e.g. Clearance Form">
                                </div>
                            </div>

                            <!-- Priority Level -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-secondary">Priority Level</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="priority" id="prio3" value="3" {{ old('priority') == 3 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger py-2 fw-bold" for="prio3"><i class="fa fa-bolt me-1"></i> Extreme</label>

                                    <input type="radio" class="btn-check" name="priority" id="prio2" value="2" {{ old('priority') == 2 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning py-2 fw-bold" for="prio2"><i class="fa fa-exclamation-triangle me-1"></i> Urgent</label>

                                    <input type="radio" class="btn-check" name="priority" id="prio1" value="1" {{ old('priority', 1) == 1 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary py-2 fw-bold" for="prio1"><i class="fa fa-check me-1"></i> Normal</label>
                                </div>
                            </div>

                            <!-- Hard Copy Toggle -->
                            <div class="col-12">
                                <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <div class="p-3 bg-white rounded-circle border me-3">
                                            <i class="fa fa-box text-maroon fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-maroon">Track as Physical/Hard Copy Item</h6>
                                            <small class="text-muted">Offices will confirm physical receipt instead of signing digitally.</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input custom-switch" type="checkbox" name="is_hard_copy" id="hardCopyToggle" value="1" {{ old('is_hard_copy') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: SIGNATORIES -->
                <div class="card ispsc-card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-title">
                            <i class="fa fa-pen-fancy me-2"></i> Signatories & Sequence
                        </div>

                        <div id="signatory-container">
                            @php $oldSigs = old('signatory_names', [null]); @endphp
                            @foreach($oldSigs as $index => $oldName)
                                <div class="signer-block mb-3 {{ $index > 0 ? 'border-top pt-3' : '' }}">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold">Signatory Name</label>
                                            <div class="search-container">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                                    <input type="text" name="signatory_names[]" 
                                                        class="form-control user-search" 
                                                        placeholder="Select or type name..." 
                                                        value="{{ $oldName }}"
                                                        autocomplete="off" required>
                                                </div>
                                                <div class="search-results d-none"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Order of Signing</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control bg-light" value="{{ $index + 1 }}" readonly>
                                                @if($index > 0)
                                                    <button type="button" class="btn btn-outline-danger" onclick="this.closest('.signer-block').remove()">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2 fw-bold px-3 rounded-pill" onclick="addSigner()">
                            <i class="fa fa-plus-circle me-1"></i> Add Another Signer
                        </button>
                    </div>
                </div>

                <!-- CARD 4: ROUTING & FILE/ITEM -->
                <div class="card ispsc-card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-title">
                            <i class="fa fa-paper-plane me-2"></i> Final Routing & Item Details
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Final Destination Office</label>
                                <select name="target_office_id" class="form-select" required>
                                    <option value="" disabled {{ !old('target_office_id') ? 'selected' : '' }}>Select the destination office...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ old('target_office_id') == $office->id ? 'selected' : '' }}>{{ $office->office_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <!-- Digital Section -->
                                <div id="softCopySection">
                                    <label class="form-label">Upload PDF/Document(s)</label>
                                    <input type="file" name="doc_files[]" id="fileInput" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx" multiple>
                                    <div class="form-text text-muted">Upload the soft copy for digital signing.</div>
                                </div>

                                <!-- Hard Copy Section -->
                                <div id="hardCopySection" class="d-none">
                                    <label class="form-label text-danger fw-bold text-uppercase" style="font-size:0.75rem;">Physical Item / Hard Copy Description</label>
                                    <input type="text" name="physical_description" id="physicalInput" value="{{ old('physical_description') }}" class="form-control border-danger" placeholder="e.g. Acer Projector (SN: 12345)">
                                    <div class="form-text text-danger small">Required for physical tracking.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5 pb-5">
                    <button type="submit" class="btn btn-maroon btn-lg shadow-lg px-5 py-3 rounded-pill">
                        SUBMIT TRANSACTION <i class="fa fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const users = @json($users->map(fn($u) => ['name' => $u->username, 'office' => $u->office->office_name ?? 'N/A']));

    document.addEventListener('DOMContentLoaded', function () {
        // Fix: Ensure toggle states are correct if we return from discard
        const toggle = document.getElementById('hardCopyToggle');
        if(toggle.checked) updateLayout(true);
        toggle.addEventListener('change', (e) => updateLayout(e.target.checked));
    });

    function updateLayout(isHard) {
        document.getElementById('softCopySection').classList.toggle('d-none', isHard);
        document.getElementById('hardCopySection').classList.toggle('d-none', !isHard);
        document.getElementById('fileInput').required = !isHard;
        document.getElementById('physicalInput').required = isHard;
    }

    function renderDropdown(input) {
        const container = input.closest('.search-container');
        const resultsDiv = container.querySelector('.search-results');
        const query = input.value.toLowerCase();
        const matches = users.filter(u => u.name.toLowerCase().includes(query));
        resultsDiv.innerHTML = matches.map(u => `<div class="search-item" onclick="selectUser(this, '${u.name}')"><div class="fw-bold">${u.name}</div><div class="small text-muted">${u.office}</div></div>`).join('') || '<div class="p-2 small">No matches.</div>';
        resultsDiv.classList.remove('d-none');
    }

    document.addEventListener('focusin', e => e.target.classList.contains('user-search') && renderDropdown(e.target));
    document.addEventListener('input', e => e.target.classList.contains('user-search') && renderDropdown(e.target));
    document.addEventListener('click', e => !e.target.closest('.search-container') && document.querySelectorAll('.search-results').forEach(d => d.classList.add('d-none')));

    window.selectUser = function(el, name) {
        const parent = el.closest('.search-container');
        parent.querySelector('.user-search').value = name;
        parent.querySelector('.search-results').classList.add('d-none');
    };

    function addSigner() {
        const container = document.getElementById('signatory-container');
        const count = container.children.length + 1;
        const html = `<div class="signer-block mb-3 border-top pt-3 animate__animated animate__fadeIn"><div class="row align-items-center g-3"><div class="col-md-8"><label class="form-label small fw-bold">Signatory Name</label><div class="search-container"><div class="input-group"><span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span><input type="text" name="signatory_names[]" class="form-control user-search" required autocomplete="off"></div><div class="search-results d-none"></div></div></div><div class="col-md-4"><label class="form-label small fw-bold">Order</label><div class="input-group"><input type="number" class="form-control bg-light" value="${count}" readonly><button type="button" class="btn btn-outline-danger" onclick="this.closest('.signer-block').remove()"><i class="fa fa-trash"></i></button></div></div></div></div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function toggleOthers(val) {
        document.getElementById('otherInputContainer').classList.toggle('d-none', val !== 'Others');
    }
</script>
@endpush