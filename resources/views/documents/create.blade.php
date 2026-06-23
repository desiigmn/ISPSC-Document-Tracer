@extends('layouts.ispsc')

@section('title', 'New Virtual Action Slip')

@push('css')
<style>
    :root { 
        --ispsc-maroon: #800000; 
        --ispsc-yellow: #FFCC00;
        --bg-light: #f4f7f9;
    }

    body { background-color: var(--bg-light); font-size: 14px; color: #333; }

    .main-content-fluid { width: 100%; padding: 0 40px; }

    /* CARD STYLING - FIXED: Overflow visible so dropdown isn't cut */
    .tracer-card { 
        background: #fff; border: 1px solid #e1e8ed; border-radius: 12px; 
        margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
        position: relative;
        overflow: visible !important; 
    }
    .tracer-card-header { 
        padding: 15px 25px; border-bottom: 1px solid #f1f1f1; 
        display: flex; justify-content: space-between; align-items: center; background: #fff; 
        border-radius: 12px 12px 0 0;
    }
    .tracer-card-header h6 { margin: 0; font-weight: 800; color: #000; text-transform: uppercase; font-size: 13px; }

    /* FORM TYPOGRAPHY */
    .form-label { 
        font-weight: 700; 
        color: #666; 
        font-size: 12px; 
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    /* INPUTS */
    .form-control, .form-select {
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 14px;
        color: #1a1a1a;
        background-color: #ffffff;
        transition: 0.3s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--ispsc-maroon);
        outline: none;
    }
    .form-control[readonly] { background-color: #f8f9fa; border-color: #eee; color: #888; }

    /* SIGNER BLOCK */
    .signer-block {
        background-color: #fff;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
    }

    /* BUTTONS */
    .btn-docu { border-radius: 10px; font-weight: 800; text-transform: uppercase; font-size: 13px; padding: 12px 30px; transition: 0.3s; border: none; }
    .btn-maroon { background: var(--ispsc-maroon); color: #fff; }
    .btn-maroon:hover { background: #600000; transform: translateY(-2px); }
    .btn-dark { background: #1a1a1a; color: #fff; }
    .btn-outline-danger { border: 2px solid #dc3545; color: #dc3545; font-weight: 800; }

    /* SEARCH DROPDOWN FIX */
    .search-container { position: relative; z-index: 100; }
    .search-results {
        position: absolute;
        top: 100%; left: 0; right: 0;
        background: white;
        border: 2px solid var(--ispsc-maroon);
        border-radius: 10px;
        z-index: 2000; 
        max-height: 250px;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .search-item { padding: 12px 15px; cursor: pointer; border-bottom: 1px solid #f8f9fa; }
    .search-item:hover { background-color: #fff9e6; }

    .custom-switch-wrapper {
        background-color: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        border: 2px solid #e1e8ed;
    }
</style>
@endpush

@section('content')
@php 
    $isAdminOrRecords = (Auth::user()->role === 'superadmin' || str_contains(Auth::user()->office_id ?? '', '-REC-'));
@endphp

<div class="main-content-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- SECTION 1: ORIGIN -->
                <div class="tracer-card">
                    <div class="tracer-card-header"><h6>Creator Information</h6></div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">User Handle</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->username }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Active Campus</label>
                                <input type="text" class="form-control" value="MAIN CAMPUS" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Home Office</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->office->office_name ?? 'No Office Assigned' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: CLASSIFICATION -->
                <div class="tracer-card">
                    <div class="tracer-card-header"><h6>Transaction Details</h6></div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="{{ $isAdminOrRecords ? 'col-md-6' : 'col-md-12' }}">
                                <label class="form-label">Classification Type</label>
                                <select name="classification" id="classSelect" class="form-select" onchange="toggleOthers(this.value)" required>
                                    <option value="" disabled {{ !old('classification') ? 'selected' : '' }}>Select classification...</option>
                                    @foreach(['Memorandum', 'Special Order', 'Communication Letter', 'Purchase Request', 'Device/Equipment', 'Others'] as $opt)
                                        <option value="{{ $opt }}" {{ old('classification') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                <div id="otherInputContainer" class="mt-3 {{ old('classification') == 'Others' ? '' : 'd-none' }}">
                                    <label class="form-label text-maroon">Manual Title Specification</label>
                                    <input type="text" name="custom_title" value="{{ old('custom_title') }}" class="form-control" placeholder="Enter document title...">
                                </div>
                            </div>

                            @if($isAdminOrRecords)
                                <div class="col-md-6">
                                    <label class="form-label">Sequence Urgency</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="priority" id="prio3" value="3" {{ old('priority') == 3 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger py-2" for="prio3">Extremely Urgent</label>
                                        <input type="radio" class="btn-check" name="priority" id="prio2" value="2" {{ old('priority') == 2 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning text-dark py-2" for="prio2">Urgent</label>
                                        <input type="radio" class="btn-check" name="priority" id="prio1" value="1" {{ old('priority', 1) == 1 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary py-2" for="prio1">Normal</label>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <div class="custom-switch-wrapper d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white p-2 rounded border me-3"><i class="fa fa-box-open text-muted"></i></div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Physical Hard Copy Tracking</h6>
                                            <small class="text-muted">Digital signatures will be replaced by receipt confirmations.</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="is_hard_copy" id="hardCopyToggle" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: CHAIN -->
                <div class="tracer-card">
                    <div class="tracer-card-header"><h6>Approval / Personnel Chain</h6></div>
                    <div class="card-body p-4">
                        <div id="signatory-container">
                            <div class="signer-block">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-9">
                                        <label class="form-label">Sequence #1: Personnel Name</label>
                                        <div class="search-container">
                                            <input type="text" name="signatory_names[]" class="form-control user-search" placeholder="Start typing personnel name..." autocomplete="off" required>
                                            <div class="search-results d-none"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-outline-secondary w-100 py-2" disabled>Primary Signer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm mt-2 fw-bold px-4 shadow-sm" onclick="addSigner()">+ ADD PERSONNEL</button>
                    </div>
                </div>

                <!-- SECTION 4: DESTINATION (FIXED: Now inside form) -->
                <div class="tracer-card">
                    <div class="tracer-card-header"><h6>Routing & Source</h6></div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Target Office Hub</label>
                                <select name="target_office_id" class="form-select" required>
                                    <option value="" disabled selected>Select destination office...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->office_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div id="softCopySection">
                                    <label class="form-label">Digital Source Document</label>
                                    <input type="file" name="doc_files[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                </div>
                                <div id="hardCopySection" class="d-none">
                                    <label class="form-label text-maroon">Item Description</label>
                                    <input type="text" name="physical_description" class="form-control border-maroon" placeholder="Describe the physical item...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center py-5">
                    <button type="submit" class="btn-docu btn-maroon px-5 py-3 shadow-lg">
                        SUBMIT TRANSACTION <i class="fa fa-arrow-right ms-2 small"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const users = @json($users->map(fn($u) => ['name' => $u->username, 'office' => $u->office->office_name ?? 'N/A', 'role' => $u->role_title ?? 'Staff']));

    function renderDropdown(input) {
        const container = input.closest('.search-container');
        const resultsDiv = container.querySelector('.search-results');
        const query = input.value.toLowerCase();
        if (!query) { resultsDiv.classList.add('d-none'); return; }

        const matches = users.filter(u => u.name.toLowerCase().includes(query));
        if (matches.length > 0) {
            resultsDiv.innerHTML = matches.map(u => `
                <div class="search-item d-flex justify-content-between align-items-center" onclick="selectUser(this, '${u.name}')">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 13px;">${u.name}</div>
                        <div class="text-muted" style="font-size: 11px;">${u.office}</div>
                    </div>
                </div>`).join('');
            resultsDiv.classList.remove('d-none');
        } else { resultsDiv.classList.add('d-none'); }
    }

    window.selectUser = function(element, name) {
        const container = element.closest('.search-container');
        container.querySelector('.user-search').value = name;
        container.querySelector('.search-results').classList.add('d-none');
    };

    document.addEventListener('input', e => { if (e.target.classList.contains('user-search')) renderDropdown(e.target); });
    document.addEventListener('click', e => { if (!e.target.closest('.search-container')) document.querySelectorAll('.search-results').forEach(d => d.classList.add('d-none')); });

    function addSigner() {
        const container = document.getElementById('signatory-container');
        const count = container.children.length + 1;
        const html = `<div class="signer-block"><div class="row align-items-end g-3"><div class="col-md-9"><label class="form-label">Sequence #${count}: Personnel Name</label><div class="search-container"><input type="text" name="signatory_names[]" class="form-control user-search" required autocomplete="off" placeholder="Search name..."><div class="search-results d-none"></div></div></div><div class="col-md-3"><button type="button" class="btn btn-outline-danger w-100 py-2" onclick="this.closest('.signer-block').remove()">Remove</button></div></div></div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function toggleOthers(val) { document.getElementById('otherInputContainer').classList.toggle('d-none', val !== 'Others'); }

    document.getElementById('hardCopyToggle').addEventListener('change', function() {
        document.getElementById('hardCopySection').classList.toggle('d-none', !this.checked);
        document.getElementById('softCopySection').classList.toggle('d-none', this.checked);
    });
</script>
@endpush