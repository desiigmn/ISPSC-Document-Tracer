<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ISPSC | @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --ispsc-maroon: #800000; --ispsc-yellow: #FFCC00; --ispsc-dark: #1a1a1a; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-height: 100vh; background: var(--ispsc-dark); color: white; position: fixed; width: 250px; border-right: 4px solid var(--ispsc-yellow); }
        .sidebar a { color: #adb5bd; padding: 15px 25px; display: block; text-decoration: none; border-left: 4px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background: var(--ispsc-maroon); color: var(--ispsc-yellow); border-left: 4px solid var(--ispsc-yellow); }
        main { margin-left: 250px; padding: 20px; }
        .onwards-header { background: white; padding: 20px; border-radius: 12px; border-left: 10px solid var(--ispsc-maroon); box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .bg-maroon { background: var(--ispsc-maroon) !important; color: white; }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="text-center py-4">
            <h6 class="text-yellow fw-bold">DOCUMENT TRACER</h6>
        </div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa fa-home me-2"></i> Dashboard</a>
        <a href="{{ route('documents.create') }}"><i class="fa fa-file-signature me-2"></i> New Action Slip</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn text-danger w-100 text-start ps-4 mt-4"><i class="fa fa-power-off me-2"></i> Logout</button>
        </form>
    </nav>
    <main>
        <div class="onwards-header d-flex justify-content-between align-items-center">
            <div><h1>ILOCOS SUR POLYTECHNIC STATE COLLEGE</h1><p class="text-success fw-bold mb-0">ONWARDS UIP</p></div>
            <div class="badge bg-maroon p-2">USER: {{ strtoupper(Auth::user()->username) }}</div>
        </div>
        @yield('content')
    </main>
</body>
</html>