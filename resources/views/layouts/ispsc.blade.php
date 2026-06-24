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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');

        :root {
            --ispsc-maroon: #800000;
            --ispsc-yellow: #FFCC00;
            --sidebar-bg: #0b132b;
            --bg-light: #f4f7f9;
            --sidebar-width: 280px;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', sans-serif;
            font-size: 14px; 
            color: #1a1a1a;
            margin: 0;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* 1. SIDEBAR SYSTEM */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            left: 0; top: 0;
            z-index: 1040; /* Lower than Modal (1050) */
            display: flex;
            flex-direction: column;
            color: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease-in-out;
        }

        .sidebar-header { padding: 40px 35px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .brand-text { font-weight: 900; font-size: 1.4rem; letter-spacing: -1px; color: #fff; line-height: 1.1; }
        .brand-sub { font-weight: 700; font-size: 0.6rem; color: var(--ispsc-yellow); text-transform: uppercase; }

        /* User Profile (Centered) */
        .sidebar-user {
            padding: 30px 20px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            text-align: center;
        }
        .u-avatar {
            width: 80px; height: 80px;
            background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--sidebar-bg); font-size: 1.5rem; flex-shrink: 0;
            overflow: hidden; border: 2px solid rgba(255,255,255,0.1);
            margin: 0 auto 15px;
        }
        .u-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .u-info { line-height: 1.3; overflow: hidden; width: 100%; }
        .u-name { font-weight: 800; font-size: 0.95rem; color: #fff; margin-bottom: 4px; }
        .u-designation { font-size: 0.7rem; color: var(--ispsc-yellow); font-weight: 700; text-transform: uppercase; margin-bottom: 8px; display: block; }
        .btn-edit-profile { 
            font-size: 10px; font-weight: 800; color: #fff; 
            text-decoration: none; background: rgba(255,255,255,0.1); 
            padding: 5px 12px; border-radius: 20px; transition: 0.2s; 
        }

        #sidebar .nav-list { padding: 15px 0; list-style: none; flex: 1; overflow-y: auto; }
        #sidebar .nav-item { padding: 3px 20px; }
        #sidebar .nav-link {
            color: rgba(255,255,255,0.6);
            font-weight: 600;
            padding: 12px 15px;
            border-radius: 8px;
            display: flex; align-items: center;
            text-decoration: none; transition: 0.2s;
        }
        #sidebar .nav-link i { width: 30px; font-size: 1.1rem; }
        #sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,0.05); }
        #sidebar .nav-link.active { background: var(--ispsc-maroon); color: #fff; }

        /* 2. MAIN CONTENT WRAPPER */
        #content { 
            flex: 1; 
            margin-left: var(--sidebar-width); 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            width: 100%; 
            transition: all 0.3s ease;
        }

        /* 3. MODAL CRITICAL FIXES */
        /* Ensure modals are always on top of the sidebar */
        .modal { z-index: 2000 !important; }
        .modal-backdrop { z-index: 1950 !important; }

        /* Mobile header logic */
        .mobile-top-bar {
            display: none;
            background: #fff;
            padding: 12px 20px;
            border-bottom: 1px solid #e1e8ed;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .academic-footer { padding: 25px 0; border-top: 1px solid #e1e8ed; background: #fff; margin-top: auto; }

        /* 4. RESPONSIVE BREAKPOINTS */
        @media (max-width: 1199px) {
            :root { --sidebar-width: 85px; }
            .u-info, .brand-text, .brand-sub, #sidebar span { display: none; }
            .sidebar-user, .sidebar-header { justify-content: center !important; padding: 20px 0 !important; }
            .u-avatar { width: 50px; height: 50px; margin-bottom: 0; }
            #sidebar .nav-link { justify-content: center; }
            #sidebar .nav-link i { margin: 0 !important; }
        }

        @media (max-width: 767px) {
            :root { --sidebar-width: 0px; }
            #sidebar { left: -280px; width: 280px; }
            #sidebar.active { left: 0; }
            #content { margin-left: 0; }
            .mobile-top-bar { display: flex; }
            
            #sidebar.active .u-info, #sidebar.active .brand-text, #sidebar.active .brand-sub, #sidebar.active span { display: block; }
            #sidebar.active .sidebar-header { padding: 30px 25px; text-align: left; }
            #sidebar.active .sidebar-user { padding: 30px 20px; text-align: center; }
            #sidebar.active .u-avatar { width: 80px; height: 80px; margin: 0 auto 15px; }
            #sidebar.active .nav-link { justify-content: flex-start; padding: 12px 20px; }
        }

        /* Overlay for mobile */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1030;
        }
        #sidebar-overlay.active { display: block; }

        /* Form Utilities for Elder-friendly use */
        .form-control { border: 2px solid #e1e8ed; padding: 12px; border-radius: 8px; font-size: 14px; }
        .btn-maroon { background: var(--ispsc-maroon); color: #fff; border: none; font-weight: 800; }
        .btn-secondary-outline { background: transparent; border: 2px solid #e1e8ed; color: #666; font-weight: 800; border-radius: 8px; }

        @stack('css')
    </style>
</head>
<body>

    <div id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- 1. SIDEBAR -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <span class="brand-text">DocuRoute</span>
            <span class="brand-sub d-block">ISPSC ONWARDS UIP</span>
        </div>

        @if(Auth::check())
        <div class="sidebar-user d-flex flex-column align-items-center">
            <div class="u-avatar shadow-sm">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Profile">
                @else
                    <i class="fa fa-user fa-2x" style="margin-top: 20px; color:#444"></i>
                @endif
            </div>
            <div class="u-info">
                <span class="u-designation">{{ Auth::user()->role === 'superadmin' ? 'Records Head' : 'Staff' }}</span>
                <div class="u-name">{{ Auth::user()->username }}</div>
                <div class="text-white-50 small mb-2" style="font-size: 11px;">{{ Auth::user()->office->office_name ?? 'RECORDS' }}</div>
                <a href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal" class="btn-edit-profile">
                    <i class="fa fa-cog me-1"></i> EDIT PROFILE
                </a>
            </div>
        </div>
        @endif

        <ul class="nav-list">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa fa-th-large"></i> <span>Dashboard</span>
                </a>
            </li>
            @if(Auth::user() && Auth::user()->role === 'superadmin')
            <li class="nav-item">
                <a href="{{ route('admin.personnel') }}" class="nav-link {{ request()->routeIs('admin.personnel') ? 'active' : '' }}">
                    <i class="fa fa-users-cog"></i> <span>Personnel</span>
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fa fa-shield-alt"></i> <span>Security</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer p-4 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link text-white text-decoration-none p-0 fw-bold opacity-50 small">
                    <i class="fa fa-sign-out-alt me-2"></i> <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- 2. CONTENT AREA -->
    <div id="content">
        <!-- Mobile Header Toggle -->
        <header class="mobile-top-bar">
            <button class="btn btn-dark" onclick="toggleSidebar()">
                <i class="fa fa-bars"></i>
            </button>
            <span class="fw-bold text-maroon" style="letter-spacing: 1px;">DOCUROUTE</span>
            <div style="width: 40px;"></div>
        </header>

        <main class="flex-grow-1 mt-4">
            <div class="container-fluid px-lg-5">
                @if(session('msg'))
                    <div class="alert alert-success border-0 shadow-sm animate__animated animate__fadeInDown">
                        <i class="fa fa-check-circle me-2"></i> {{ session('msg') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm animate__animated animate__fadeInDown">
                        <i class="fa fa-triangle-exclamation me-2"></i> {{ session('error') }}
                    </div>
                @endif
            </div>
            @yield('content')
        </main>

        <footer class="academic-footer text-center">
            <div class="container-fluid px-4">
                <div class="fw-bold text-muted small text-uppercase">Ilocos Sur Polytechnic State College | Onwards UIP</div>
                <div class="fw-black mt-1" style="color: var(--ispsc-maroon); font-size: 13px; letter-spacing: 2px;">DOCUROUTE | 2026</div>
            </div>
        </footer>
    </div>

    <!-- MODAL: CHANGE PASSWORD -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-bottom p-4">
                    <h6 class="modal-title fw-black text-uppercase m-0">Security Settings</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="p-4 pt-0">
                        <button type="submit" class="btn btn-maroon w-100 py-3 small text-uppercase">Update Security</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: EDIT PROFILE -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-bottom p-4">
                    <h6 class="modal-title fw-black text-uppercase m-0">Edit Profile</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div id="avatar-preview-box" class="mx-auto shadow-sm border mb-3" style="width: 110px; height: 110px; border-radius: 50%; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <i class="fa fa-user fa-3x text-muted"></i>
                                @endif
                            </div>
                            <label for="avatar-input" class="btn btn-sm btn-outline-dark fw-bold" style="font-size: 11px;">CHANGE PICTURE</label>
                            <input type="file" name="avatar" id="avatar-input" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Display Name</label>
                            <input type="text" name="username" class="form-control" value="{{ Auth::user()->username }}" required>
                        </div>
                    </div>
                    <div class="p-4 pt-0">
                        <button type="submit" class="btn btn-maroon w-100 py-3 small text-uppercase">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebar-overlay').classList.toggle('active');
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview-box').innerHTML = 
                        `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    @stack('scripts')
</body>
</html>