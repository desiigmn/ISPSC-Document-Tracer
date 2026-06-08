@extends('layouts.ispsc')

@section('title', 'Personnel & Office Management')

@section('content')
<div class="container-fluid py-4">
    
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-maroon">Personnel & Office Management</h2>
            <p class="text-muted">Manage system users, staff accounts, and campus offices.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-maroon shadow-sm fw-bold px-4" data-bs-toggle="modal" data-bs-target="#registerStaffModal">
                <i class="fa fa-user-plus me-1"></i> REGISTER STAFF
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('msg'))
        <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show">
            <i class="fa fa-check-circle me-2"></i> {{ session('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4 alert-dismissible fade show">
            <i class="fa fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- LEFT: OFFICE MANAGEMENT -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold py-3 text-uppercase small">Add New Office</div>
                <div class="card-body">
                    <form action="{{ route('admin.offices.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">OFFICE NAME</label>
                            <input type="text" name="office_name" class="form-control" placeholder="e.g. Registrar Office" required>
                        </div>
                        <button class="btn btn-maroon w-100 fw-bold">SAVE OFFICE</button>
                    </form>
                </div>
            </div>

<div class="card shadow-sm border-0">
    <!-- Main Dark Header -->
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <span class="fw-bold small text-uppercase mb-0">Existing Offices</span>

        <!-- Nested Card for the Search Bar -->
        <div class="card border-0 shadow-sm" style="width: 300px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-0 pe-1">
                    <i class="fa fa-search text-muted small"></i>
                </span>
                <input type="text" id="officeSearch" class="form-control border-0 ps-1" placeholder="Search office name..." style="box-shadow: none; font-size: 0.85rem;">
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-hover align-middle mb-0" id="officeTable">
            <thead class="table-light sticky-top">
                <tr style="font-size: 0.75rem;">
                    <th class="ps-3 py-2">OFFICE NAME</th>
                    <th class="text-end pe-3 py-2">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @foreach($offices as $office)
                <tr class="office-row">
                    <td class="small fw-bold text-dark ps-3 office-name">{{ $office->office_name }}</td>
                    <td class="text-end pe-3">
                        @if($office->id !== 'ISPSC-MC-REC-2026-4URQGK')
                            <form action="{{ route('admin.offices.destroy', $office->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm text-danger border-0 bg-transparent" onclick="return confirm('Delete Office?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
                
                <!-- Hidden No Results Message -->
                <tr id="noResults" style="display: none;">
                    <td colspan="2" class="text-center py-4 text-muted small">
                        <i class="fa fa-info-circle me-1"></i> No matching offices found.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
        </div>

        <!-- RIGHT: PERSONNEL LIST -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold py-3 text-uppercase small">Registered Staff Members</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
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
                                    <td>{{ $u->office->office_name ?? 'N/A' }}</td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <!-- RESET PASSWORD -->
                                            <button class="btn btn-sm btn-outline-dark fw-bold px-3" 
                                                    onclick="openResetModal('{{ $u->id }}', '{{ $u->username }}')" title="Reset Password">
                                                <i class="fa fa-key"></i>
                                            </button>

                                            <!-- DELETE ACCOUNT -->
                                            @if($u->id !== Auth::id())
                                                <form action="{{ route('admin.staff.destroy', $u->id) }}" method="POST" onsubmit="return confirm('ARE YOU SURE? This will permanently remove this user.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3" title="Delete Account">
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
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title fw-bold">REGISTER NEW STAFF</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.staff.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold">FULL NAME</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. name@gmail.com" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">OFFICE</label>
                            <select name="office_id" class="form-select" required>
                                <option value="" selected disabled>Select...</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->office_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">CAMPUS</label>
                            <select name="campus_code" class="form-select" required>
                                <option value="0001">MAIN (0001)</option>
                                <option value="0010">CANDON (0010)</option>
                                <option value="0101">TAGUDIN (0101)</option>
                                <option value="0011">SANTIAGO (0011)</option>
                                <option value="0100">STA MARIA (0100)</option>  
                                <option value="0011">SANTIAGO (0011)</option>
                                <option value="0110">NARVACAN (0110)</option>
                                <option value="0111">CERVANTES (0111)</option>

                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-maroon">ACCOUNT ROLE</label>
                        <select name="role" class="form-select border-maroon" required>
                            <option value="staff" selected>STAFF (REGULAR USER)</option>
                            <option value="superadmin">SUPER ADMIN (RECORDS HEAD)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">PASSWORD</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">CONFIRM</label>
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

<!-- MODAL: RESET PASSWORD (THE ONE THAT WAS MISSING) -->
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
                    <p class="small text-muted">New password for: <strong id="resetTargetName"></strong></p>
                    <div class="mb-3">
                        <label class="small fw-bold">NEW PASSWORD</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">CONFIRM PASSWORD</label>
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
    function openResetModal(userId, userName) {
        document.getElementById('resetTargetName').innerText = userName;
        document.getElementById('resetPasswordForm').action = `/admin/staff/reset-password/${userId}`;
        new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
    }
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
</script>
@endpush