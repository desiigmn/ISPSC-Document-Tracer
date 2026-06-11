<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | ISPSC ONWARDS UIP</title>
    <!-- Inside the <head> tag -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <style>
        :root {
            --ispsc-maroon: #800000;
            --ispsc-yellow: #FFCC00;
        }

        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* NAVBAR CORE - Increased padding for height */
        .navbar-ispsc { 
            background-color: #800000 !important; 
            border-bottom: 5px solid #FFCC00; 
            padding: 18px 0; /* Increased for a taller feel */
        }

        /* BRANDING - Enlarged text and space */
        .navbar-brand-text {
            line-height: 1.2;
            border-right: 2px solid rgba(255,255,255,0.3);
            padding-right: 25px;
            margin-right: 25px;
        }

        .brand-title {
            font-weight: 800; 
            font-size: 1.5rem; /* Enlarged from 1.1rem */
            color: #fff; 
            letter-spacing: 1px;
            display: block;
        }

        .brand-sub {
            font-weight: 700; 
            font-size: 0.85rem; /* Enlarged from 0.6rem */
            color: #FFCC00; 
            text-transform: uppercase;
            display: block;
        }

        /* NAV LINKS - Larger fonts and more horizontal space */
        .nav-link { 
            color: #ffffff !important; 
            font-weight: 600; 
            font-size: 1.15rem; /* Enlarged from 0.9rem */
            padding: 10px 20px !important; /* Added more space between items */
            transition: 0.3s;
        }

        .nav-link i {
            font-size: 1.3rem; /* Slightly larger icons */
            margin-right: 10px;
        }

        .nav-link:hover, .nav-link.active { 
            color: #FFCC00 !important; 
        }

        /* USER INFO SECTION - Enlarged labels */
        .user-name {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.2rem; /* Enlarged from 0.9rem */
            line-height: 1.1;
        }

        .user-office {
            color: #FFCC00;
            font-weight: 600;
            font-size: 0.9rem; /* Enlarged from 0.65rem */
            text-transform: uppercase;
        }

        /* LOGOUT BUTTON - Enlarged circle */
        .btn-logout-circle {
            width: 48px; 
            height: 48px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #FFCC00;
            color: #FFCC00;
            background: transparent;
            transition: 0.3s;
        }

        .btn-logout-circle:hover {
            background-color: #FFCC00;
            color: #800000;
        }

        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            font-size: 1rem;
        }

        .badge-pill-custom {
            font-size: 0.75rem !important;
            padding: 0.4em 0.6em !important;
        }

        @stack('css')
    </style>
</head>
<body>

    <!-- COMPACT NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ispsc sticky-top">
        <div class="container-fluid px-5"> <!-- Increased container padding -->
            <!-- BRANDING -->
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <div class="navbar-brand-text">
                    <span class="brand-title">DOCUMENT TRACER</span>
                    <span class="brand-sub">ISPSC | ONWARDS UIP</span>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fa fa-home"></i> Dashboard
                        </a>
                    </li>
                    
                    @php
                        $userNotifications = \App\Models\Notification::where('user_id', Auth::id())->latest()->take(5)->get();
                        $unreadCount = $userNotifications->count();
                    @endphp


                    @if(Auth::user()->role === 'superadmin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.personnel') ? 'active' : '' }}" href="{{ route('admin.personnel') }}">
                                <i class="fa fa-users-cog"></i> Personnel
                            </a>
                        </li>
                    @endif
                </ul>

                <!-- RIGHT SIDE: USER INFO -->
                <div class="d-flex align-items-center gap-4"> <!-- Increased gap between user info and logout -->
                    <div class="dropdown text-end">
                        <a href="#" class="text-decoration-none" data-bs-toggle="dropdown">
                            <div class="user-name">{{ Auth::user()->username }} <i class="fa fa-caret-down ms-1 opacity-50"></i></div>
                            <div class="user-office">{{ Auth::user()->office->office_name ?? 'Staff' }}</div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                            <li>
                                <a class="dropdown-item fw-bold py-3" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="fa fa-lock me-2 text-muted"></i> CHANGE PASSWORD
                                </a>
                            </li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-logout-circle rounded-circle">
                            <i class="fa fa-power-off"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="container-fluid mt-5 mb-5 px-5"> <!-- Increased container spacing -->
        @yield('content')
    </div>

    <!-- FOOTER -->
    <footer class="text-center py-4 text-muted border-top bg-white mt-auto">
        <small>ILOCOS SUR POLYTECHNIC STATE COLLEGE | <strong>ONWARDS UIP</strong> &copy; {{ date('Y') }}</small>
    </footer>

    <!-- CHANGE PASSWORD MODAL -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold small"><i class="fa fa-shield-alt me-2"></i> SECURITY SETTINGS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">CURRENT PASSWORD</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">NEW PASSWORD</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="small fw-bold text-muted">CONFIRM NEW PASSWORD</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="submit" class="btn btn-maroon w-100 fw-bold shadow-sm" style="background-color: #800000; color: white; padding: 12px;">UPDATE PASSWORD</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>