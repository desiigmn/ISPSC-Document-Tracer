@extends('layouts.ispsc')

@section('title', 'New Virtual Action Slip')

@push('css')
<style>
    /* CLEAN MINIMALIST STATION THEME */
    :root {
        --subtle-border: #e9ecef;
        --soft-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    body {
        background-color: #f8fafc;
        color: #334155;
    }

    /* CARD MODERNIZATION */
    .ispsc-card { 
        border-radius: 8px; 
        border: 1px solid var(--subtle-border); 
        box-shadow: var(--soft-shadow);
        background-color: #ffffff;
        transition: transform 0.2s ease;
    }

    /* TYPOGRAPHY & SECTIONS */
    .section-title {
        border-left: 3px solid var(--ispsc-maroon);
        padding-left: 12px;
        margin-bottom: 25px;
        color: var(--ispsc-maroon);
        font-weight: 800;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .form-label { 
        font-weight: 700; 
        color: #64748b; 
        font-size: 0.75rem; 
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    /* INPUT SLEEKNESS */
    .form-control, .form-select {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.6rem 0.85rem;
        font-size: 0.95rem;
        color: #1e293b;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--ispsc-maroon);
        box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        outline: none;
    }

    .form-control[readonly] {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }

    /* SIGNER BLOCK REFACTOR */
    .signer-block {
        background-color: #ffffff;
        border: 1px solid var(--subtle-border);
        border-left: 3px solid #cbd5e1; /* Subtle default accent */
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 15px;
        transition: border-color 0.2s ease;
    }

    .signer-block:hover {
        border-left-color: var(--ispsc-maroon);
    }

    /* BUTTONS */
    .btn-maroon { 
        background-color: var(--ispsc-maroon); 
        color: #ffffff; 
        font-weight: 700; 
        border: none;
        border-radius: 6px; /* Refined rectangle */
        padding: 12px 30px;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .btn-maroon:hover { 
        background-color: #600000; 
        color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .btn-outline-secondary {
        border: 1px solid #d1d5db;
        color: #475569;
        font-weight: 600;
        border-radius: 6px;
    }

    .btn-outline-danger {
        border-radius: 6px;
        font-weight: 600;
    }

    /* SEARCH ENGINE DESIGN */
    .search-container { position: relative; }
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--subtle-border);
        border-radius: 6px;
        z-index: 1050; 
        max-height: 250px;
        overflow-y: auto;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }

    .search-item {
        padding: 12px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
    }
    .search-item:hover { background-color: #fff9f9; }
    
    .badge.bg-light.text-maroon {
        background-color: #fff1f1 !important;
        color: var(--ispsc-maroon) !important;
        border: 1px solid #fee2e2;
    }

    /* PRIORITY TOGGLE CLEANUP */
    .btn-group .btn { 
        border-width: 1px;
        padding: 10px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    /* HARD COPY TOGGLE */
    .custom-switch-wrapper {
        border: 1px solid var(--subtle-border);
        background-color: #ffffff;
    }

</style>
@endpush

@section('content')
@php 
    // Logic Preserved
    $isAdminOrRecords = (Auth::user()->role === 'superadmin' || str_contains(Auth::user()->office_id ?? '', '-REC-'));
@endphp

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm border-0 mb-4" style="border-radius: 8px;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="small fw-bold"><i class="fa fa-exclamation-triangle me-2"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- CARD 1: CREATOR DETAILS -->
                <div class="card ispsc-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-title">
                            Creator Identification
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">User Handle</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->username }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-muted">Active Campus</label>
                                @php
                                    $campuses = ['0001'=>'MAIN','0010'=>'CANDON','0011'=>'SANTIAGO','0100'=>'STA MARIA','0101'=>'TAGUDIN','0110'=>'NARVACAN','0111'=>'CERVANTES'];
                                    $userCode = Auth::user()->campus_code;
                                    $campusName = $campuses[$userCode] ?? 'Not Set';
                                @endphp
                                <input type="text" class="form-control" value="{{ $campusName }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-muted">Institutional Office</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->office->office_name ?? 'No Office Assigned' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: CLASSIFICATION & PRIORITY -->
                <div class="card ispsc-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-title">
                            Item Classification
                        </div>
                        
                        <div class="row g-4 align-items-start">
                            <div class="{{ $isAdminOrRecords ? 'col-md-6' : 'col-md-12' }}">
                                <label class="form-label">Classification Type</label>
                                <select name="classification" id="classSelect" class="form-select" onchange="toggleOthers(this.value)" required>
                                    <option value="" disabled {{ !old('classification') ? 'selected' : '' }}>Select classification...</option>
                                    @foreach(['Memorandum', 'Special Order', 'Communication Letter', 'Purchase Request', 'Device/Equipment', 'Others'] as $opt)
                                        <option value="{{ $opt }}" {{ old('classification') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>

                                <div id="otherInputContainer" class="mt-3 {{ old('classification') == 'Others' ? '' : 'd-none' }}">
                                    <label class="form-label text-maroon">Specification</label>
                                    <input type="text" name="custom_title" id="otherClassification" value="{{ old('custom_title') }}" class="form-control" placeholder="Type document title here...">
                                </div>
                            </div>

                            @if($isAdminOrRecords)
                                <div class="col-md-6">
                                    <label class="form-label">Sequence Urgency</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="priority" id="prio3" value="3" {{ old('priority') == 3 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger" for="prio3">Extremely Urgent</label>

                                        <input type="radio" class="btn-check" name="priority" id="prio2" value="2" {{ old('priority') == 2 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning text-dark" for="prio2">Urgent</label>

                                        <input type="radio" class="btn-check" name="priority" id="prio1" value="1" {{ old('priority', 1) == 1 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="prio1">Normal</label>
                                    </div>
                                    <small class="text-muted d-block mt-1 fw-bold">Manual Assignment Active (Admin)</small>
                                </div>
                            @endif

                            <div class="col-12">
                                <div class="p-3 rounded-3 custom-switch-wrapper d-flex align-items-center justify-content-between shadow-sm border">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 bg-light rounded-3 border me-3">
                                            <i class="fa fa-box text-muted"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Hard Copy Tracking</h6>
                                            <small class="text-muted">Disable digital signing for physical hand-carry items.</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="is_hard_copy" id="hardCopyToggle" value="1" {{ old('is_hard_copy') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: SIGNATORIES -->
                <div class="card ispsc-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-title">
                            Personnel Chain
                        </div>
                        <div id="signatory-container">
                            @php $oldSigs = old('signatory_names', [null]); @endphp
                            @foreach($oldSigs as $index => $oldName)
                                <div class="signer-block animate__animated animate__fadeIn">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-8">
                                            <label class="form-label">Identify Personnel</label>
                                            <div class="search-container">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted small"></i></span>
                                                    <input type="text" name="signatory_names[]" class="form-control user-search border-start-0" value="{{ $oldName }}" placeholder="Start typing name..." autocomplete="off" required>
                                                </div>
                                                <div class="search-results d-none"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Sequence #</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control fw-bold bg-light" value="{{ $index + 1 }}" readonly>
                                                @if($index > 0) 
                                                    <button type="button" class="btn btn-outline-danger ms-2 px-3" onclick="this.closest('.signer-block').remove()"><i class="fa fa-trash-alt"></i></button> 
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2 fw-bold px-3 shadow-sm" onclick="addSigner()"><i class="fa fa-plus me-1"></i> ADD PERSONNEL</button>
                    </div>
                </div>

                <!-- CARD 4: ROUTING & FILE/ITEM -->
                <div class="card ispsc-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-title">Final Hub Station</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Target Office Hub</label>
                                <select name="target_office_id" class="form-select" required>
                                    <option value="" disabled {{ !old('target_office_id') ? 'selected' : '' }}>Choose target destination...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ old('target_office_id') == $office->id ? 'selected' : '' }}>{{ $office->office_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div id="softCopySection">
                                    <label class="form-label">Digital Source Document</label>
                                    <input type="file" name="doc_files[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                    <small class="text-muted mt-2 d-block">PDF, JPG, PNG accepted. Select multiple files if required.</small>
                                </div>
                                <div id="hardCopySection" class="d-none">
                                    <label class="form-label text-danger">Item Narrative</label>
                                    <input type="text" name="physical_description" class="form-control border-danger" placeholder="e.g. Lenovo Laptop [Asset: 77891]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5 mb-5 pb-4">
                    <button type="submit" class="btn btn-maroon px-5 py-3 shadow">DISPATCH TRANSACTION <i class="fa fa-arrow-right ms-2 small"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // JavaScript Logic Unaltered
    const users = @json($users->map(fn($u) => [
        'name' => $u->username, 
        'office' => $u->office->office_name ?? 'N/A',
        'role' => $u->role_title ?? 'Officer'
    ]));

    function renderDropdown(input) {
        const container = input.closest('.search-container');
        const resultsDiv = container.querySelector('.search-results');
        const query = input.value.toLowerCase();
        const matches = users.filter(u => u.name.toLowerCase().includes(query));

        if (matches.length > 0) {
            resultsDiv.innerHTML = matches.map(u => `
                <div class="search-item d-flex justify-content-between align-items-center" onclick="selectUser(this, '${u.name}')">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">${u.name}</div>
                        <div class="text-muted small">${u.office}</div>
                    </div>
                    <span class="badge bg-light text-maroon border border-maroon small">
                        ${u.role_title ? u.role_title.toUpperCase() : 'STAFF'}
                    </span>
                </div>
            `).join('');
            resultsDiv.classList.remove('d-none');
        }
    }

    window.selectUser = function(element, name) {
        const container = element.closest('.search-container');
        const inputField = container.querySelector('.user-search');
        const allInputs = document.querySelectorAll('.user-search');
        let isDuplicate = false;

        allInputs.forEach(input => {
            if (input !== inputField && input.value.trim() === name.trim()) {
                isDuplicate = true;
            }
        });

        if (isDuplicate) {
            Swal.fire({
                title: 'Duplicate User Detected',
                text: `${name} is already assigned in this sequence.`,
                icon: 'warning',
                confirmButtonColor: '#800000',
                confirmButtonText: 'Try Another'
            });
            inputField.value = "";
            container.querySelector('.search-results').classList.add('d-none');
            return;
        }

        inputField.value = name;
        container.querySelector('.search-results').classList.add('d-none');
    };

    document.addEventListener('focusin', e => e.target.classList.contains('user-search') && renderDropdown(e.target));
    document.addEventListener('input', e => e.target.classList.contains('user-search') && renderDropdown(e.target));
    document.addEventListener('click', e => !e.target.closest('.search-container') && document.querySelectorAll('.search-results').forEach(d => d.classList.add('d-none')));

    function addSigner() {
        const container = document.getElementById('signatory-container');
        const count = container.children.length + 1;
        const html = `<div class="signer-block animate__animated animate__fadeIn"><div class="row align-items-center g-3"><div class="col-md-8"><label class="form-label">Identify Personnel</label><div class="search-container"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted small"></i></span><input type="text" name="signatory_names[]" class="form-control user-search border-start-0" required autocomplete="off" placeholder="Start typing name..."></div><div class="search-results d-none"></div></div></div><div class="col-md-4"><label class="form-label">Sequence #</label><div class="input-group"><input type="number" class="form-control bg-light fw-bold" value="${count}" readonly><button type="button" class="btn btn-outline-danger ms-2 px-3" onclick="this.closest('.signer-block').remove()"><i class="fa fa-trash-alt"></i></button></div></div></div></div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function toggleOthers(val) {
        document.getElementById('otherInputContainer').classList.toggle('d-none', val !== 'Others');
    }
</script>
@endpush