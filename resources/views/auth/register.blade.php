<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | ISPSC ONWARDS UIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --ispsc-maroon: #800000; --ispsc-yellow: #FFCC00; }
        body { background-color: #f4f4f4; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .register-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 500px; overflow: hidden; border-top: 5px solid var(--ispsc-yellow); }
        .register-header { background: var(--ispsc-maroon); padding: 25px; text-align: center; color: white; }
        .btn-maroon { background-color: var(--ispsc-maroon); color: white; font-weight: bold; border: none; padding: 12px; }
        .btn-maroon:hover { background-color: #600000; color: var(--ispsc-yellow); }
        .text-maroon { color: var(--ispsc-maroon); }
        .form-label { margin-bottom: 0.3rem; }
    </style>
</head>
<body>

    <!-- ... existing head and styles ... -->

<div class="register-card shadow-lg">
    <div class="register-header">
        <h2 class="fw-bold mb-0">ISPSC</h2>
        <small class="fw-bold" style="color: var(--ispsc-yellow); letter-spacing: 1px;">ACCOUNT ENROLLMENT</small>
    </div>
    
    <div class="p-4">
        @if ($errors->any())
            <div class="alert alert-danger py-2 small mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-3">
                <label class="form-label small fw-bold text-maroon">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus placeholder="John Doe">
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label small fw-bold text-maroon">Email Address</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="example@gmail.com">
            </div>

            <!-- Account Type -->
            <div class="mb-3">
                <label class="form-label small fw-bold text-maroon">Account Type</label>
                <select name="role" id="roleSelect" class="form-select" onchange="toggleAdminFields()">
                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Office Staff (User)</option>
                    <option value="clerk" {{ old('role') == 'clerk' ? 'selected' : '' }}>Clerk Admin</option>
                    <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            <!-- ADDED: Assign Campus -->
            <!-- In register.blade.php -->
<div class="mb-3" id="campusDiv">
    <label class="form-label small fw-bold text-maroon">Assign Campus</label>
    <select name="campus_code" id="campus_code" class="form-select"> {{-- Ensure name is campus_code --}}
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

            <!-- Assign Office -->
            <div class="mb-3" id="officeDiv">
                <label for="office_id" class="form-label small fw-bold text-maroon">Assign Office</label>
                <select name="office_id" id="office_id" class="form-select">
                    <option value="" disabled selected>Select your office...</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                            {{ $office->office_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- SECRET PASSKEY -->
            <div class="mb-3 d-none" id="passkeyDiv">
                <label class="form-label small fw-bold text-danger">Admin Secret Passkey</label>
                <input type="password" name="admin_passkey" class="form-control border-danger" placeholder="Enter code for Admin rights">
            </div>

            <!-- Password Row -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-maroon">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-maroon">Confirm</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-maroon shadow-sm">CREATE ACCOUNT</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAdminFields() {
        const role = document.getElementById('roleSelect').value;
        const campusDiv = document.getElementById('campusDiv');
        const officeDiv = document.getElementById('officeDiv');
        const passkeyDiv = document.getElementById('passkeyDiv');

        if (role === 'superadmin') {
            campusDiv.classList.add('d-none');
            officeDiv.classList.add('d-none');
            passkeyDiv.classList.remove('d-none');
            document.getElementById('office_id').required = false;
            document.getElementById('campus_code').required = false;
        } else {
            campusDiv.classList.remove('d-none');
            officeDiv.classList.remove('d-none');
            passkeyDiv.classList.add('d-none');
            document.getElementById('office_id').required = true;
            document.getElementById('campus_code').required = true;
        }
    }
    window.onload = toggleAdminFields;
</script>
</body>
</html>