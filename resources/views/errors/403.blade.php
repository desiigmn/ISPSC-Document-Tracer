<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted | DocuRoute</title>
    
    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');

        :root {
            --ispsc-maroon: #800000;
            --ispsc-yellow: #FFCC00;
            --sidebar-bg: #0b132b; /* Matches your sidebar theme */
            --bg-light: #f4f7f9;
        }

        body {
            background-color: var(--bg-light);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            font-size: 14px; /* NORMAL INFO 14px */
            padding: 20px;
            color: #1a1a1a;
        }

        /* Matches the Dashboard Card Styling */
        .tracer-error-card {
            max-width: 500px;
            width: 100%;
            border: 1px solid #e1e8ed;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        /* Branding Header - Matches Sidebar Header */
        .brand-header {
            background-color: var(--sidebar-bg);
            padding: 30px;
            text-align: center;
            border-bottom: 3px solid var(--ispsc-maroon);
        }
        .brand-text { font-weight: 900; font-size: 1.4rem; letter-spacing: -1px; color: #fff; line-height: 1; }
        .brand-sub { font-weight: 700; font-size: 0.6rem; color: var(--ispsc-yellow); text-transform: uppercase; letter-spacing: 1px; }

        .icon-shield {
            width: 80px;
            height: 80px;
            background-color: #fff5f5;
            color: var(--ispsc-maroon);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: -40px auto 20px;
            border: 4px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            font-weight: 800;
            color: var(--sidebar-bg);
            font-size: 1.5rem;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        /* Content spacing for 14px text */
        .alert-box {
            background-color: #f8f9fa;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            color: #444;
            padding: 20px;
            margin: 25px 0;
            line-height: 1.6;
        }

        .btn-maroon {
            background-color: var(--ispsc-maroon);
            color: #fff;
            font-weight: 800;
            border-radius: 8px;
            padding: 12px 25px;
            transition: 0.3s;
            text-transform: uppercase;
            border: none;
            font-size: 13px;
        }

        .btn-maroon:hover {
            background-color: #600000;
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-switch {
            color: var(--ispsc-maroon);
            font-weight: 800;
            text-decoration: none;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.7;
        }
        .btn-switch:hover { opacity: 1; text-decoration: underline; }

        .footer-tag {
            font-size: 11px;
            font-weight: 700;
            color: #888;
            padding: 20px;
            border-top: 1px solid #f1f1f1;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <div class="tracer-error-card animate__animated animate__fadeInUp">
        <!-- Dashboard Branded Header -->
        <div class="brand-header">
            <span class="brand-text">DocuRoute</span>
            <span class="brand-sub d-block">ISPSC ONWARDS UIP</span>
        </div>
        
        <div class="card-body p-4 p-md-5 text-center">
            <div class="icon-shield">
                <i class="fa fa-lock-hashtag fa-2x"></i>
            </div>

            <h2>Access Denied</h2>
            
            <p class="text-muted mt-3" style="font-size: 14px;">
                User Account: <strong class="text-dark">{{ Auth::user()->username }}</strong><br>
                Identity verified, but permissions are <span class="text-danger fw-bold">Insufficient</span>.
            </p>

            <div class="alert-box text-start">
                <div class="fw-bold text-dark mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa fa-shield-halved me-2 text-maroon"></i> 
                    Security Protocol:
                </div>
                Access to this document is strictly reserved for the <strong class="text-dark">Uploader</strong>, the <strong class="text-dark">Assigned Signatories</strong>, or <strong class="text-dark">Records Personnel</strong>.
            </div>

            <div class="d-grid gap-3">
                <a href="{{ route('dashboard') }}" class="btn btn-maroon shadow-sm">
                    <i class="fa fa-th-large me-2"></i> Return to Dashboard Hub
                </a>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link btn-switch">
                        <i class="fa fa-users-rotate me-1"></i> Sign out to switch account
                    </button>
                </form>
            </div>
        </div>

        <div class="footer-tag text-center">
            Ilocos Sur Polytechnic State College &bull; 2026
        </div>
    </div>

</body>
</html>