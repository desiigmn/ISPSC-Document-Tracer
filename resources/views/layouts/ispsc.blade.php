<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | ISPSC ONWARDS UIP</title>
    
    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --ispsc-maroon: #800000;
            --ispsc-yellow: #FFCC00;
        }

        /* 1. ACCESSIBILITY SCALING */
        html { font-size: 16px; } 
        @media (min-width: 1200px) { html { font-size: 17.5px; } }

        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #111;
            line-height: 1.5;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Necessary for sticky footer */
            overflow-x: hidden;
        }

        /* 2. REFINED NAVBAR UI */
        .navbar-ispsc { 
            background-color: var(--ispsc-maroon) !important; 
            border-bottom: 4px solid var(--ispsc-yellow); 
            padding: 10px 0;
            z-index: 1030;
        }

        .brand-section {
            line-height: 1.1;
            padding-right: 1.5rem;
            margin-right: 1.5rem;
            border-right: 1.5px solid rgba(255,255,255,0.2);
        }

        .brand-text { font-weight: 900; font-size: 1.3rem; letter-spacing: 1.5px; color: #fff; text-transform: uppercase; }
        .brand-sub { font-weight: 700; font-size: 0.65rem; color: #FFCC00; letter-spacing: 1px; text-transform: uppercase; }

        .navbar-nav .nav-link { 
            color: #ffffff !important; 
            font-weight: 800; 
            font-size: 0.95rem; 
            padding: 0.7rem 1.2rem !important;
            margin: 0 4px;
            transition: 0.3s ease;
            display: flex;
            align-items: center;
            border-radius: 8px;
        }

        .navbar-nav .nav-link:hover { background: rgba(255, 255, 255, 0.1); color: #FFCC00 !important; }
        .navbar-nav .nav-link.active { background: #FFCC00 !important; color: #800000 !important; }

        .nav-link i { font-size: 1.1rem; margin-right: 10px; }

        /* 3. USER PROFILE */
        .user-profile-meta { text-align: right; line-height: 1.1; }
        .u-name { color: #fff; font-weight: 800; font-size: 0.95rem; }
        .u-office { color: #FFCC00; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }

        .btn-power {
            width: 42px; height: 42px;
            border-radius: 50%;
            border: 2px solid #FFCC00;
            color: #FFCC00;
            background: transparent;
            display: flex; align-items: center; justify-content: center;
            transition: 0.3s;
        }
        .btn-power:hover { background: #FFCC00; color: #800000; transform: rotate(90deg); }

        /* 4. FLUID MAIN CONTAINER */
        .main-content-wrapper { flex: 1; width: 100%; padding: 1.5rem 0; }

        .alert { border-radius: 15px; animation: fadeInDown 0.5s ease-out; }
        .fw-black { font-weight: 900; }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* 5. FIX FOR FOOTER HEIGHT */
        .academic-footer {
            background-color: #ffffff;
            border-top: 1px solid #eef0f2;
            padding: 20px 0; /* Shrunk padding from py-5 */
            margin-top: auto;
        }
           /* SECURITY MODAL HUB REFINEMENTS */
    #changePasswordModal .modal-content {
        border: 1px solid #eef0f2;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }

    #changePasswordModal .modal-header {
        background-color: #f8f9fa; /* Ghost white for academic minimalism */
        border-bottom: 1px solid #eef0f2;
        padding: 20px 30px;
    }

    #changePasswordModal .modal-title {
        color: #800000;
        font-weight: 800;
        font-size: 0.85rem;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    #changePasswordModal .form-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: #94a3b8; /* Slate grey labels */
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    #changePasswordModal .form-control {
        border: 1.5px solid #e2e8f0;
        padding: 12px 15px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        background-color: #fff;
    }

    #changePasswordModal .form-control:focus {
        border-color: #800000;
        box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.05);
        outline: none;
    }

    #changePasswordModal .btn-save-security {
        background-color: #800000;
        border: none;
        color: white;
        padding: 15px;
        font-weight: 800;
        font-size: 0.85rem;
        letter-spacing: 1px;
        border-radius: 8px;
        width: 100%;
        transition: 0.3s;
    }

    #changePasswordModal .btn-save-security:hover {
        background-color: #600000;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2);
    }

        @stack('css')
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-ispsc sticky-top shadow-sm">
        <div class="container-fluid px-lg-4">
            @php
                if(Auth::check()){
                    $userNotifications = \App\Models\Notification::where('user_id', Auth::id())->latest()->take(5)->get();
                }
            @endphp

            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <div class="brand-section d-none d-sm-block">
                    <span class="brand-text">DocuRoute</span>
                    <span class="brand-sub d-block">ISPSC | ONWARDS UIP</span>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#ispscMainNav">
                <i class="fa fa-bars text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="ispscMainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="fa-solid fa-layer-group"></i> DASHBOARD</a></li>
                    @if(Auth::user() && Auth::user()->role === 'superadmin')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.personnel') ? 'active' : '' }}" href="{{ route('admin.personnel') }}"><i class="fa-solid fa-users-gear"></i> PERSONNEL</a></li>
                    @endif
               </ul>

                <div class="d-flex align-items-center border-start border-white border-opacity-10 ps-3">
                    @if(Auth::check())
                    <div class="dropdown me-3">
                        <a href="#" class="text-decoration-none d-flex align-items-center gap-3" data-bs-toggle="dropdown">
                            <div class="user-profile-meta d-none d-md-block">
                                <div class="u-name">{{ strtoupper(Auth::user()->username) }}</div>
                            </div>
                            <i class="fa fa-caret-down text-white opacity-50"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3">
                            <li><a class="dropdown-item fw-bold py-3" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="fa fa-shield-halved me-2"></i> SECURITY</a></li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-power"><i class="fa fa-power-off"></i></button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="container-fluid px-lg-5">
            {{-- Messages (Simplified for brevity) --}}
            @if(session('msg'))<div class="alert alert-success border-0 shadow-sm mb-4" style="border-left: 10px solid #198754 !important;">{{ session('msg') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger border-0 shadow-sm mb-4" style="border-left: 10px solid #dc3545 !important;">{{ session('error') }}</div>@endif
        </div>
        @yield('content')
    </div>

    <!-- THE SLIM FOOTER -->
    <footer class="academic-footer text-center">
        <div class="container">
            <div class="small fw-bold text-dark opacity-75">ILOCOS SUR POLYTECHNIC STATE COLLEGE</div>
            <div class="text-uppercase fw-bold opacity-50" style="color: var(--ispsc-maroon); letter-spacing: 1px; font-size: 0.65rem;">
                DocuRoute &bull; ONWARDS UIP &bull; &copy; {{ date('Y') }}
            </div>
        </div>
    </footer>

<!-- UPDATED PASSWORD MODAL -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h6 class="modal-title mb-0"><i class="fa fa-shield-halved me-2"></i> Update Security Hub</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 0.7rem;"></button>
            </div>
            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Current -->
                    <div class="mb-4">
                        <label class="form-label text-uppercase">Existing Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter your current password" required>
                    </div>

                    <div class="row g-3">
                        <!-- New -->
                        <div class="col-6">
                            <label class="form-label text-uppercase">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 chars" required>
                        </div>
                        <!-- Confirm -->
                        <div class="col-6">
                            <label class="form-label text-uppercase">Verify Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password" required>
                        </div>
                    </div>
                </div>
                <div class="p-4 pt-0">
                    <button type="submit" class="btn-save-security text-uppercase">
                        Save Security Changes <i class="fa fa-arrow-right ms-2 small"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>