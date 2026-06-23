@extends('layouts.guest')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');

    :root {
        --ispsc-maroon: #800000;
        --ispsc-yellow: #FFCC00;
        --sidebar-bg: #0b132b; /* Deep Navy */
    }

    /* FORCING WHITE BACKGROUND */
    body, html { 
        background-color: #ffffff !important; 
        background-image: none !important;
        font-family: 'Inter', sans-serif; 
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
    }

    .login-container {
        width: 100%;
        max-width: 420px;
        padding: 20px;
        background-color: #ffffff !important;
    }

    .login-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        /* Defined border and dark shadow to ensure card stands out on white */
        border: 1px solid #e1e8ed; 
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }

    /* Branded Header Area */
    .banner {
        background-color: var(--sidebar-bg);
        color: white;
        padding: 40px 20px;
        border-bottom: 4px solid var(--ispsc-maroon);
        text-align: center;
    }

    .brand-text { font-weight: 900; font-size: 1.8rem; letter-spacing: -1px; color: #fff; margin: 0; line-height: 1; }
    .brand-sub { color: var(--ispsc-yellow); font-weight: 700; font-size: 0.65rem; letter-spacing: 1.5px; margin-top: 6px; text-transform: uppercase; }
    
    .portal-tag {
        background: rgba(255,255,255,0.1);
        color: #fff;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-top: 15px;
    }

    /* Form Styling */
    .form-label { font-weight: 800; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .form-control { 
        padding: 12px 15px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 14px; font-weight: 500;
        transition: 0.2s;
        background-color: #ffffff;
    }
    .form-control:focus { border-color: var(--ispsc-maroon); box-shadow: none; outline: none; }
    
    .input-group-text { background-color: #f8f9fa; border: 2px solid #e2e8f0; border-right: none; color: #94a3b8; border-radius: 8px 0 0 8px; }
    .input-group .form-control { border-radius: 0 8px 8px 0; }

    /* Button Styling */
    .btn-authenticate {
        background-color: var(--ispsc-maroon);
        color: #fff;
        font-weight: 800;
        padding: 14px;
        border-radius: 8px;
        border: none;
        transition: 0.3s;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-size: 13px;
        width: 100%;
        margin-top: 10px;
        cursor: pointer;
    }
    .btn-authenticate:hover { background-color: #600000; transform: translateY(-1px); box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2); }

    .footer-note { font-size: 11px; font-weight: 700; color: #94a3b8; text-align: center; margin-top: 30px; text-transform: uppercase; letter-spacing: 1px; }

    @media (max-width: 480px) { .login-container { padding: 15px; } }
</style>

<div class="login-container">
    <div class="login-card animate__animated animate__fadeInDown">
        <div class="banner">
            <div class="brand-text">DocuRoute</div>
            <div class="brand-sub">ISPSC | ONWARDS UIP</div>
            <div class="portal-tag">System Authentication</div>
        </div>

        <div class="p-4 p-md-5">
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4 py-2" style="font-size: 12px; font-weight: 700; background: #fff5f5; color: #c53030; border-radius: 8px;">
                    <i class="fa fa-circle-exclamation me-2"></i> Invalid login credentials.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-4">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-user-circle"></i></span>
                        <input type="text" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="Enter Username">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Security Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label small fw-bold text-muted" for="remember" style="cursor: pointer; font-size: 12px;">Keep me logged in</label>
                    </div>
                </div>

                <button type="submit" class="btn-authenticate">
                    Sign In
                </button>
            </form>
        </div>
    </div>
    
    <p class="footer-note">Ilocos Sur Polytechnic State College &bull; 2026</p>
</div>
@endsection