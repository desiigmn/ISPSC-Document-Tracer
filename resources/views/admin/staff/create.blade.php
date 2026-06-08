@extends('layouts.ispsc')

@section('title', 'Create Staff Account')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa fa-user-plus me-2"></i> CREATE STAFF ACCOUNT</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.staff.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold small">USERNAME</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">ASSIGNED OFFICE</label>
                                <select name="office_id" class="form-select" required>
                                    <option value="" selected disabled>Select Office...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->office_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">CAMPUS</label>
                                <select name="campus_code" class="form-select" required>
                                   <option value="" selected disabled>Select Campus...</option>
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

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">PASSWORD</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">CONFIRM PASSWORD</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">ACCOUNT TYPE</label>
                            <select name="role" class="form-select border-maroon">
                                <option value="staff">Staff (User)</option>
                                <option value="superadmin">Super Admin (Records Head)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-maroon w-100 py-2 fw-bold shadow-sm">
                            CREATE ACCOUNT NOW
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection