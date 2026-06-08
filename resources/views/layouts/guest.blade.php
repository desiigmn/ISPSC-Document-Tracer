<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ISPSC ONWARDS UIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --ispsc-maroon: #800000; --ispsc-yellow: #FFCC00; }
        body { 
            background: linear-gradient(rgba(128, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), 
                        url('https://raw.githubusercontent.com/your-repo/ispsc-assets/main/ispsc_bg.jpg'); 
            background-size: cover; 
            background-position: center;
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }
        .login-card { 
            background: rgba(255, 255, 255, 0.95); 
            border-radius: 20px; 
            width: 100%; 
            max-width: 400px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
            border-top: 8px solid var(--ispsc-yellow); 
            overflow: hidden;
        }
        .ispsc-logo { font-size: 3rem; color: var(--ispsc-maroon); font-weight: 900; margin-bottom: 0; line-height: 1; }
        .onwards-text { color: #006400; font-weight: bold; font-size: 1.4rem; letter-spacing: 2px; margin-top: 0; }
        .btn-maroon { background: var(--ispsc-maroon); color: white; font-weight: bold; border: none; padding: 12px; transition: 0.3s; }
        .btn-maroon:hover { background: #600000; color: var(--ispsc-yellow); }
        .form-control:focus { border-color: var(--ispsc-maroon); box-shadow: 0 0 0 0.25rem rgba(128, 0, 0, 0.25); }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>