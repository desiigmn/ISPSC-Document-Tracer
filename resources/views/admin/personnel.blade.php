@extends('layouts.ispsc')

@section('title', 'Personnel & Office Management')

@push('css')
<style>
    /* THEME COLORS */
    :root {
        --ispsc-maroon: #800000;
        --ispsc-yellow: #FFCC00;
        --ispsc-gold: #d4a017;
    }

    .text-maroon { color: var(--ispsc-maroon) !important; }
    .bg-maroon { background-color: var(--ispsc-maroon) !important; color: white !important; }
    .border-maroon { border-color: var(--ispsc-maroon) !important; }

    /* BUTTON STYLING */
    .btn-maroon { 
        background-color: var(--ispsc-maroon) !important; 
        color: var(--ispsc-yellow) !important; 
        border: none;
        font-weight: bold;
        transition: 0.3s;
    }
    .btn-maroon:hover { 
        background-color: #600000 !important; 
        color: #ffffff !important; 
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .btn-outline-maroon {
        border: 1px solid var(--ispsc-maroon);
        color: var(--ispsc-maroon);
        background: white;
    }
    .btn-outline-maroon:hover {
        background: var(--ispsc-maroon);
        color: white;
    }

    /* TABLE STYLING */
    .table-theme thead th {
        background-color: #f8f9fa;
        color: var(--ispsc-maroon);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--ispsc-maroon);
    }

    .office-table-container {
        max-height: 480px;
        overflow-y: auto;
        border-radius: 0 0 12px 12px;
    }

    /* CUSTOM SEARCH BOX */
    .header-search {
        max-width: 300px;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.3) !important;
        background: rgba(255,255,255,0.1) !important;
        color: white !important;
    }
    .header-search::placeholder { color: rgba(255,255,255,0.7); }

    /* ROW HOVER */
    .table-hover tbody tr:hover {
        background-color: rgba(128, 0, 0, 0.04) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    
    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-maroon mb-1">Personnel & Office Management</h2>
            <p class="text-muted small mb-0">System administration hub for users and office structures.</p>
        </div>
        <button class="btn btn-maroon shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#registerStaffModal">
            <i class="fa fa-user-plus me-2"></i> REGISTER STAFF
        </button>
    </div>

    {{-- Session Alerts --}}
    @if(session('msg'))
        <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show">
            <i class="fa fa-check-circle me-2"></i> {{ session('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- LEFT COLUMN: OFFICE MANAGEMENT -->
        <div class="col-xl-4">
            <!-- Add Office -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-maroon py-3">
                    <h6 class="mb-0 fw-bold text-uppercase small" style="letter-spacing: 1px;">Add New Office</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.offices.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Office Name</label>
                            <input type="text" name="office_name" class="form-control form-control-lg border-light bg-light" placeholder="e.g. Accounting Office" required style="font-size: 0.9rem;">
                        </div>
                        <button class="btn btn-maroon w-100 py-2 shadow-sm">SAVE OFFICE</button>
                    </form>
                </div>
            </div>

            <!-- Office List -->
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-maroon d-flex justify-content-between align-items-center py-2 px-3">
                    <h6 class="mb-0 fw-bold text-uppercase small" style="letter-spacing: 1px;">Existing Offices</h6><br>
                    <input type="text" id="officeSearch" class="form-control form-control-sm header-search px-3" placeholder="Search offices...">
                </div>
                <div class="card-body p-0 office-table-container">
                    <table class="table table-hover align-middle mb-0 table-theme" id="officeTable">
                        <thead>
                            <tr>
                                <th class="ps-3">OFFICE NAME</th>
                                <th class="text-end pe-3">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offices as $office)
                            <tr class="office-row">
                                <td class="ps-3 office-name fw-bold text-dark" style="font-size: 0.85rem;">{{ $office->office_name }}</td>
                                <td class="text-end pe-3">
                                    @if($office->id !== 'ISPSC-MC-REC-2026-4URQGK')
                                        <form action="{{ route('admin.offices.destroy', $office->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm text-danger border-0 bg-transparent" onclick="return confirm('Delete Office?')">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <tr id="noResults" style="display: none;">
                                <td colspan="2" class="text-center py-4 text-muted small">No offices found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: PERSONNEL LIST -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-maroon py-3">
                    <h6 class="mb-0 fw-bold text-uppercase small" style="letter-spacing: 1px;">Registered Staff Members</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-theme">
                            <thead>
                                <tr>
                                    <th class="ps-4">NAME / EMAIL</th>
                                    <th>OFFICE</th>
                                    <th class="text-end pe-4">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allUsers as $u)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $u->username }}</div>
                                        <small class="text-muted">{{ $u->email }}</small>
                                    </td>
                                    <td>
                                        <span class="small fw-bold text-muted">{{ $u->office->office_name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-outline-maroon fw-bold px-3" 
                                                    onclick="openResetModal('{{ $u->id }}', '{{ $u->username }}')" title="Reset Password">
                                                <i class="fa fa-key"></i>
                                            </button>

                                            @if($u->id !== Auth::id())
                                                <form action="{{ route('admin.staff.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Permanently remove this user?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 shadow-sm">
                                                        <i class="fa fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL: REGISTER STAFF -->
<div class="modal fade" id="registerStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-maroon">
                <h5 class="modal-title fw-bold text-white">REGISTER NEW STAFF</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.staff.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">FULL NAME</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. juan@example.com" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted">OFFICE</label>
                            <select name="office_id" class="form-select" required>
                                <option value="" selected disabled>Select...</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->office_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted">CAMPUS</label>
                            <select name="campus_code" class="form-select" required>
                                <option value="0001">MAIN (0001)</option>
                                <option value="0010">CANDON (0010)</option>
                                <option value="0011">SANTIAGO (0011)</option>
                                <option value="0100">STA MARIA (0100)</option>  
                                <option value="0101">TAGUDIN (0101)</option>
                                <option value="0110">NARVACAN (0110)</option>
                                <option value="0111">CERVANTES (0111)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-maroon text-uppercase">Account Role</label>
                        <select name="role" class="form-select border-maroon" required>
                            <option value="staff" selected>STAFF (REGULAR USER)</option>
                            <option value="superadmin">SUPER ADMIN (RECORDS HEAD)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted">PASSWORD</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted">CONFIRM</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-maroon w-100 fw-bold py-2">CREATE ACCOUNT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: RESET PASSWORD -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold small">RESET PASSWORD</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted">Setting new password for: <strong id="resetTargetName" class="text-dark"></strong></p>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">NEW PASSWORD</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">CONFIRM PASSWORD</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-maroon w-100 fw-bold">UPDATE PASSWORD</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // OFFICE SEARCH LOGIC
    document.getElementById('officeSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('.office-row');
        let hasResults = false;
        rows.forEach(row => {
            let text = row.querySelector('.office-name').textContent.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = "";
                hasResults = true;
            } else {
                row.style.display = "none";
            }
        });
        document.getElementById('noResults').style.display = hasResults ? "none" : "table-row";
    });

    function openResetModal(userId, userName) {
        document.getElementById('resetTargetName').innerText = userName;
        document.getElementById('resetPasswordForm').action = `/admin/staff/reset-password/${userId}`;
        new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
    }
</script>
@endpush