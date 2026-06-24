@extends('layouts.ispsc')

@section('title', 'Personnel & Office Management')

@push('css')
<style>
    :root { 
        --ispsc-maroon: #800000; 
        --ispsc-yellow: #FFCC00;
        --ispsc-blue: #0056b3;
        --bg-light: #f4f7f9;
    }

    body { background-color: var(--bg-light); font-size: 14px; color: #333; }
    .main-content-fluid { width: 100%; padding: 0 40px; }

    /* CARD STYLING ALIGNED TO DASHBOARD */
    .tracer-card { 
        background: #fff; border: 1px solid #e1e8ed; border-radius: 12px; 
        margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; 
    }
    .tracer-card-header { 
        padding: 15px 25px; border-bottom: 1px solid #f1f1f1; 
        display: flex; justify-content: space-between; align-items: center; background: #fff; 
    }
    .tracer-card-header h6 { margin: 0; font-weight: 800; color: #000; text-transform: uppercase; font-size: 13px; }

    /* TABLE DESIGN (14px Content) */
    .table thead th { 
        background: #f8f9fa; color: #666; font-size: 11px; 
        text-transform: uppercase; font-weight: 800; border: none; 
        padding: 15px 20px;
    }
    .ledger-row td { 
        padding: 18px 20px !important; 
        border-bottom: 1px solid #f1f1f1 !important; 
        font-size: 14px; /* NORMAL INFO 14px */
        vertical-align: middle;
    }

    /* SEARCH STYLE IN HEADER */
    .header-search-container { position: relative; width: 100%; }
    .header-search { 
        width: 100%; padding: 8px 15px 8px 35px; border-radius: 8px; 
        border: 1px solid #e1e8ed; font-size: 12px; font-weight: 600; 
    }
    .header-search-icon { position: absolute; left: 12px; top: 10px; color: #adb5bd; font-size: 12px; }

    /* BUTTONS */
    .btn-docu { border-radius: 10px; font-weight: 800; text-transform: uppercase; font-size: 13px; padding: 10px 25px; transition: 0.3s; border: none; }
    .btn-maroon { background: var(--ispsc-maroon); color: #fff; }
    .btn-maroon:hover { background: #600000; transform: translateY(-2px); }
    .btn-dark { background: #1a1a1a; color: #fff; }
    
    .btn-outline-maroon { border: 1.5px solid var(--ispsc-maroon); color: var(--ispsc-maroon); font-weight: 800; }
    .btn-outline-maroon:hover { background: var(--ispsc-maroon); color: #fff; }

    .office-table-container { max-height: 500px; overflow-y: auto; }
    .fw-black { font-weight: 900 !important; }

    @media (max-width: 992px) { .main-content-fluid { padding: 0 15px; } }
</style>
@endpush

@section('content')
@php
    // Mapping campus codes to names for the table display
    $campusNames = [
        '0001' => 'MAIN',
        '0010' => 'CANDON',
        '0011' => 'SANTIAGO',
        '0100' => 'STA MARIA',
        '0101' => 'TAGUDIN',
        '0110' => 'NARVACAN',
        '0111' => 'CERVANTES',
    ];
@endphp

<div class="main-content-fluid py-4">
    
    <!-- HEADER SECTION -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-8">
            <h4 class="fw-black mb-0">Personnel & Office Management</h4>
            <small class="text-muted fw-bold">Institutional Structure & User Administration</small>
        </div>
        <div class="col-lg-4 d-flex justify-content-lg-end">
            <button class="btn btn-docu btn-maroon shadow-sm" data-bs-toggle="modal" data-bs-target="#registerStaffModal">
                <i class="fa fa-user-plus me-2"></i> REGISTER STAFF
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT: OFFICE MANAGEMENT -->
        <div class="col-xl-4">
            <!-- ADD OFFICE -->
            <div class="tracer-card">
                <div class="tracer-card-header bg-maroon">
                    <h6 class="text-dark mb-0">Create New Office </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.offices.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Institutional Name</label>
                            <input type="text" name="office_name" class="form-control" placeholder="e.g. Registrar's Office" required>
                        </div>
                        <button class="btn btn-docu btn-dark w-100 py-2">SAVE OFFICE</button>
                    </form>
                </div>
            </div>

            <!-- OFFICE LIST -->
            <div class="tracer-card">
                <div class="tracer-card-header">
                    <h6>Registry of Offices</h6>
                </div>
                <div class="p-3 border-bottom bg-light">
                    <div class="header-search-container">
                        <i class="fa fa-search header-search-icon"></i>
                        <input type="text" id="officeSearch" class="header-search" placeholder="Filter office list...">
                    </div>
                </div>
                <div class="office-table-container">
                    <table class="table table-hover align-middle mb-0" id="officeTable">
                        <thead>
                            <tr>
                                <th class="ps-4">OFFICE NAME</th>
                                <th class="text-end pe-4">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offices as $office)
                            <tr class="office-row ledger-row">
                                <td class="ps-4 office-name fw-bold text-dark">{{ $office->office_name }}</td>
                                <td class="text-end pe-4">
                                    @if($office->id !== 'ISPSC-MC-REC-2026-4URQGK')
                                        <form action="{{ route('admin.offices.destroy', $office->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm text-danger border-0 bg-transparent" onclick="return confirm('Delete this Office?')">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <tr id="noResults" style="display: none;">
                                <td colspan="2" class="text-center py-5 text-muted fw-bold">No matching hub found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT: PERSONNEL LIST -->
        <div class="col-xl-8">
            <div class="tracer-card">
                <div class="tracer-card-header">
                    <h6>Registered Institutional Personnel</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">PERSONNEL / ACCOUNT EMAIL</th>
                                <th>OFFICE</th>
                                <th>CAMPUS</th>
                                <th class="text-end pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Sorting by campus_code ensures 0001 (Main) comes first, then 0010, 0011, etc. --}}
                            @foreach($allUsers->sortBy('campus_code') as $u)
                            <tr class="ledger-row">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $u->username }}</div>
                                    <small class="text-muted fw-bold">{{ $u->email }}</small>
                                </td>
                                <td>
                                    <span class="small fw-bold text-muted">{{ $u->office->office_name ?? 'NOT ASSIGNED' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-bold" style="font-size: 11px;">
                                        {{ $campusNames[$u->campus_code] ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-maroon px-3 py-1" onclick="openResetModal('{{ $u->id }}', '{{ $u->username }}')" title="Reset Security Code">
                                            <i class="fa fa-key"></i>
                                        </button>

                                        @if($u->id !== Auth::id())
                                            <form action="{{ route('admin.staff.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Remove personnel from registry?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-1">
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