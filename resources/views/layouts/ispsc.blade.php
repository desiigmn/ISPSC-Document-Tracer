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
            --sidebar-bg: #101524; /* Dark Navy from screenshot */
            --bg-light: #f4f7f9;
            --sidebar-width: 280px;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', sans-serif;
            font-size: 14px; 
            color: #1a1a1a;
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* 1. SIDEBAR STYLING */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            left: 0; top: 0;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            color: #fff;
            transition: all 0.3s ease;
        }

        .sidebar-header { padding: 30px 25px; }
        .brand-text { font-weight: 900; font-size: 1.6rem; color: #fff; display: block; }
        .brand-sub { font-weight: 700; font-size: 0.65rem; color: var(--ispsc-yellow); text-transform: uppercase; letter-spacing: 0.5px; }

        .sidebar-user {
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .u-avatar {
            width: 85px; height: 85px;
            background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; 
            margin: 0 auto 15px;
        }
        .u-avatar img { width: 100%; height: 100%; object-fit: cover; }
        
        .u-name { font-weight: 800; font-size: 14px; margin-bottom: 2px; }
        .u-designation { font-weight: 600; font-size: 12px; color: #ccc; margin-bottom: 2px; display: block;}
        .u-office { font-size: 10px; color: #888; text-transform: uppercase; margin-bottom: 10px; font-weight: 700; }
        
        .btn-edit-profile {
            color: #007bff; /* Blue as seen in screenshot */
            text-decoration: none;
            font-weight: 800;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-edit-profile:hover { color: #fff; }

        #sidebar .nav-list { padding: 10px; list-style: none; flex: 1; }
        #sidebar .nav-item { margin-bottom: 8px; }
        #sidebar .nav-link {
            color: #fff;
            font-weight: 700;
            padding: 12px 20px;
            border-radius: 10px; /* Rounded pill style */
            display: flex; align-items: center;
            text-decoration: none; transition: 0.2s;
        }
        #sidebar .nav-link i { min-width: 35px; font-size: 1.2rem; }
        #sidebar .nav-link:hover { background: rgba(255,255,255,0.1); }
        #sidebar .nav-link.active { background: var(--ispsc-maroon); color: #fff; }

        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .btn-signout {
            background: transparent; border: none;
            color: #fff; font-weight: 800; width: 100%; text-align: left;
            padding: 10px 15px; display: flex; align-items: center; gap: 10px;
        }

        /* 2. CONTENT AREA */
        #content { 
            margin-left: var(--sidebar-width); 
            min-height: 100vh; 
            transition: all 0.3s ease;
        }

        .mobile-top-bar {
            display: none;
            background: #fff;
            padding: 15px 20px;
            border-bottom: 1px solid #e1e8ed;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* 3. RESPONSIVE LOGIC */
        @media (max-width: 991px) {
            #sidebar { left: -280px; }
            #sidebar.active { left: 0; }
            #content { margin-left: 0 !important; }
            .mobile-top-bar { display: flex; }
        }

        #sidebar-overlay { 
            display: none; 
            position: fixed; 
            inset: 0; 
            background: rgba(0,0,0,0.5); 
            z-index: 1050; 
        }
        #sidebar-overlay.active { display: block; }

        /* BUTTONS & ALERTS */
        .btn-maroon { background: var(--ispsc-maroon); color: #fff; font-weight: 800; border: none; transition: 0.3s; }
        .btn-maroon:hover { background: #600000; color: #fff; }
        .form-control:focus { border-color: var(--ispsc-maroon); box-shadow: none; }
        
        .fw-black { font-weight: 900; }
    </style>
    @stack('css')
</head>
<body>

    <div id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- 1. SIDEBAR -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <span class="brand-text">DocuRoute</span>
            <span class="brand-sub">ISPSC ONWARDS UIP</span>
        </div>

        @if(Auth::check())
        <div class="sidebar-user">
            <div class="u-avatar shadow-sm">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Profile">
                @else
                    <i class="fa fa-user fa-2x text-muted"></i>
                @endif
            </div>
            <div class="u-info">
                <span class="u-name">{{ Auth::user()->username }}</span>
                <span class="u-designation">{{ Auth::user()->role === 'superadmin' ? 'Records Head' : 'Office Staff' }}</span>
                <div class="u-office">{{ Auth::user()->office->office_name ?? 'RECORDS' }}</div>
                
                <a href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal" class="btn-edit-profile">
                    <i class="fa fa-cog"></i> EDIT PROFILE
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
                    <i class="fa fa-users"></i> <span>Personnel</span>
                </a>
            </li>
            @endif

            @if(Auth::user() && Auth::user()->role !== 'superadmin')
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fa fa-shield-alt"></i> <span>Security</span>
                </a>
            </li>
            @endif
        </ul>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-signout">
                    <i class="fa fa-sign-out-alt"></i> <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- 2. CONTENT AREA -->
    <div id="content">
        <div class="mobile-top-bar">
            <button class="btn btn-dark" onclick="toggleSidebar()">
                <i class="fa fa-bars"></i>
            </button>
            <span class="fw-black text-maroon" style="letter-spacing: 1px;">DOCUROUTE</span>
            <div style="width: 40px;"></div>
        </div>

        <main class="flex-grow-1">
            <!-- Dynamic Alert Section -->
            <div class="container-fluid px-lg-4 mt-3">
                @if(session('msg'))
                    <div class="alert alert-success border-0 shadow-sm animate__animated animate__fadeIn">
                        <i class="fa fa-check-circle me-2"></i> {{ session('msg') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm animate__animated animate__fadeIn">
                        <i class="fa fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    </div>
                @endif
            </div>

            @yield('content')
        </main>

        <footer class="text-center py-4 text-muted small mt-auto">
            <div class="container-fluid">
                <div class="fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 10px;">
                    Ilocos Sur Polytechnic State College | Onwards UIP
                </div>
                <div class="fw-black mt-1" style="color: var(--ispsc-maroon); font-size: 12px;">
                    DOCUROUTE &copy; 2026
                </div>
            </div>
        </footer>
    </div>

    <!-- MODAL: EDIT PROFILE -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h6 class="modal-title fw-black text-uppercase m-0">Account Settings</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 text-center">
                        <div id="avatar-preview-box" class="mx-auto shadow-sm border mb-3" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="fa fa-user fa-3x text-muted"></i>
                            @endif
                        </div>
                        <label for="avatar-input" class="btn btn-sm btn-outline-dark fw-bold mb-4" style="font-size: 11px;">CHANGE AVATAR</label>
                        <input type="file" name="avatar" id="avatar-input" class="d-none" accept="image/*" onchange="previewImage(this)">
                        
                        <div class="text-start">
                            <label class="form-label small fw-bold">USERNAME</label>
                            <input type="text" name="username" class="form-control" value="{{ Auth::user()->username }}" required>
                        </div>
                    </div>
                    <div class="p-4 pt-0">
                        <button type="submit" class="btn btn-maroon w-100 py-3 small text-uppercase">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: SECURITY -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h6 class="modal-title fw-black text-uppercase m-0">Change Password</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">CURRENT PASSWORD</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">NEW PASSWORD</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">CONFIRM PASSWORD</label>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview-box').innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    @stack('scripts')

</body>
</html>