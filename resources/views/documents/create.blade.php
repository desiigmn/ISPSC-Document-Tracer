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
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .search-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f1f1f1;
    }
    .search-item:hover { background-color: #f8f9fa; }
    .search-item:last-child { border-bottom: none; }
    .search-container { position: relative; }
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0 0 8px 8px;
        z-index: 1050; /* Ensure it stays above everything */
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
                    <option value="" selected disabled>Choose classification...</option>
                    <optgroup label="Common Documents">
                        <option value="Memorandum">Memorandum</option>
                        <option value="Special Order">Special Order</option>
                        <option value="Communication Letter">Communication Letter</option>
                    </optgroup>
                    <optgroup label="Logistics">
                        <option value="Purchase Request">Purchase Request</option>
                        <option value="Device/Equipment">Device / Equipment</option>
                    </optgroup>
                    <option value="Others">Others (Please specify...)</option>
                </select>

                <!-- Hidden Input: Only appears if "Others" is selected -->
                <div id="otherInputContainer" class="mt-3 d-none animate__animated animate__fadeIn">
                    <label class="form-label small fw-bold text-uppercase text-maroon" style="font-size: 0.75rem;">Specify Type</label>
                    <input type="text" name="custom_title" id="otherClassification" class="form-control form-control-sm border-maroon-focus" placeholder="e.g. Clearance Form">
                </div>
            </div>

            <!-- Priority Level -->
            <div class="col-md-6">
                <label class="form-label small fw-bold text-uppercase text-secondary">Priority Level</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="priority" id="prio3" value="3" autocomplete="off">
                    <label class="btn btn-outline-danger py-2 fw-bold" for="prio3"><i class="fa fa-bolt me-1"></i> Extreme</label>

                    <input type="radio" class="btn-check" name="priority" id="prio2" value="2" autocomplete="off">
                    <label class="btn btn-outline-warning py-2 fw-bold" for="prio2"><i class="fa fa-exclamation-triangle me-1"></i> Urgent</label>

                    <input type="radio" class="btn-check" name="priority" id="prio1" value="1" autocomplete="off" checked>
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
                        <input class="form-check-input custom-switch" type="checkbox" name="is_hard_copy" id="hardCopyToggle" value="1">
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
                            <div class="signer-block mb-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold">Signatory Name</label>
                                        <div class="search-container">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                                <input type="text" name="signatory_names[]" 
                                                    class="form-control user-search" 
                                                    placeholder="Select or type name..." 
                                                    autocomplete="off" required>
                                            </div>
                                            <!-- This div will hold the dropdown list -->
                                            <div class="search-results d-none"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Order of Signing</label>
                                        <input type="number" class="form-control bg-light" value="1" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addSigner()">
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
                    <option value="" selected disabled>Select the destination office...</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}">{{ $office->office_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- DYNAMIC INPUT AREA -->
            <div class="col-md-6">
                <!-- Shown if Digital -->
                <div id="softCopySection">
                    <label class="form-label">Upload PDF/Document(s)</label>
                    <input type="file" name="doc_files[]" id="fileInput" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx" multiple required>
                    <div class="form-text text-muted">Upload the soft copy for digital signing.</div>
                </div>

                <!-- Shown if Hard Copy -->
                <div id="hardCopySection" class="d-none">
                    <label class="form-label text-danger fw-bold">Physical Item / Hard Copy Description</label>
                    <input type="text" name="physical_description" id="physicalInput" class="form-control border-danger" placeholder="e.g. Acer Projector (SN: 12345) or 2024 Payroll Folder">
                    <div class="form-text text-danger">Provide a specific name/ID for the item being tracked.</div>
                </div>
            </div>
        </div>
    </div>
</div>

                <div class="text-center mt-5">
                    <button type="submit" class="btn btn-maroon btn-lg shadow-lg px-5 py-3">
                        SUBMIT TO RECORDS GATEWAY <i class="fa fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('css')
<style>
    /* Styling for the focus color if you have a maroon theme */
    .border-maroon-focus:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.25rem rgba(128, 0, 0, 0.25);
    }
    .text-maroon {
        color: #800000;
    }
    #classSelect {
    font-size: 0.9rem; /* Standard font size */
}

#classSelect optgroup {
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #666;
}

#classSelect option {
    font-size: 0.9rem;
    padding: 5px;
    color: #333;
}
</style>
@endpush
<!-- Datalist for Employee Search -->
<datalist id="userList">
    @foreach($users as $user)
        <option value="{{ $user->username }}">
    @endforeach
</datalist>

@endsection

@push('scripts')
<script>
    // 1. DATA: Load users once from PHP to JS
    const users = @json($users->map(function($u) {
        return [
            'name' => $u->username,
            'office' => $u->office->office_name ?? 'No Office'
        ];
    }));

    // 2. RENDER DROPDOWN FUNCTION
    // This handles both showing the full list and filtering
    function renderDropdown(input) {
        const container = input.closest('.search-container');
        const resultsDiv = container.querySelector('.search-results');
        const query = input.value.toLowerCase();
        
        // Filter users based on input (if empty, shows all)
        const matches = users.filter(u => u.name.toLowerCase().includes(query));

        if (matches.length > 0) {
            resultsDiv.innerHTML = matches.map(u => `
                <div class="search-item" onclick="selectUser(this, '${u.name}')">
                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">${u.name}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">${u.office}</div>
                </div>
            `).join('');
            resultsDiv.classList.remove('d-none');
        } else {
            resultsDiv.innerHTML = '<div class="p-3 small text-muted">No users found</div>';
            resultsDiv.classList.remove('d-none');
        }
    }

    // 3. EVENT DELEGATION (Works for existing and dynamically added rows)
    
    // Show dropdown when input is clicked or focused
    document.addEventListener('focusin', function(e) {
        if (e.target.classList.contains('user-search')) {
            renderDropdown(e.target);
        }
    });

    // Filter list as user types
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('user-search')) {
            renderDropdown(e.target);
        }
    });

    // Close dropdown when clicking outside the container
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            document.querySelectorAll('.search-results').forEach(div => div.classList.add('d-none'));
        }
    });

    // 4. SELECT USER FUNCTION
    window.selectUser = function(element, name) {
        const container = element.closest('.search-container');
        const input = container.querySelector('.user-search');
        const resultsDiv = container.querySelector('.search-results');
        
        input.value = name;
        resultsDiv.classList.add('d-none');
    };

    // 5. ADD SIGNER FUNCTION
    function addSigner() {
        const container = document.getElementById('signatory-container');
        const count = container.getElementsByClassName('signer-block').length + 1;
        
        const html = ` 
            <div class="signer-block mb-3 border-top pt-3 animate__animated animate__fadeIn">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Signatory Name</label>
                        <div class="search-container">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" name="signatory_names[]" class="form-control user-search" placeholder="Select or type name..." autocomplete="off" required>
                            </div>
                            <div class="search-results d-none"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Order of Signing</label>
                        <div class="input-group">
                            <input type="number" class="form-control bg-light" value="${count}" readonly>
                            <button class="btn btn-outline-danger" type="button" onclick="this.closest('.signer-block').remove()">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    // 6. HARD COPY TOGGLE LOGIC
    document.getElementById('hardCopyToggle').addEventListener('change', function() {
        const isHardCopy = this.checked;
        document.getElementById('softCopySection').classList.toggle('d-none', isHardCopy);
        document.getElementById('hardCopySection').classList.toggle('d-none', !isHardCopy);
        document.getElementById('fileInput').required = !isHardCopy;
        document.getElementById('physicalInput').required = isHardCopy;
    });
    function toggleOthers(val) {
    const container = document.getElementById('otherInputContainer');
    const inputField = document.getElementById('otherClassification');

    if (val === 'Others') {
        container.classList.remove('d-none');
        inputField.setAttribute('required', 'required');
        inputField.focus();
    } else {
        container.classList.add('d-none');
        inputField.removeAttribute('required');
        inputField.value = ''; // Clear value if user changes mind
    }
}
</script>
@endpush