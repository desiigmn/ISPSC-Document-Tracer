<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted | ISPSC Document Tracer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-card {
            max-width: 500px;
            width: 100%;
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(128, 0, 0, 0.1);
        }
        .card-accent {
            height: 8px;
            background: linear-gradient(to right, #800000, #FFCC00);
        }
        .icon-box {
            width: 100px;
            height: 100px;
            background-color: #fff5f5;
            color: #800000;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 4px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .btn-maroon {
            background-color: #800000;
            color: #FFCC00;
            font-weight: 700;
            border-radius: 10px;
            padding: 12px 30px;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
        }
        .btn-maroon:hover {
            background-color: #600000;
            color: #fff;
            transform: translateY(-2px);
        }
        .brand-text {
            color: #800000;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    <div class="error-card card bg-white text-center p-0">
        <div class="card-accent"></div>
        <div class="card-body p-5">
            <div class="mb-4">
                <span class="brand-text">DOCUMENT TRACER</span>
            </div>

            <div class="icon-box">
                <i class="fa fa-shield-halved fa-3x"></i>
            </div>

            <h2 class="fw-bold text-dark mb-3">Document Not Available For You</h2>
            
            <p class="text-muted mb-4 px-3">
                Sorry, <span class="fw-bold text-dark">{{ Auth::user()->username }}</span>. <br>
                You are currently logged in but do not have the permissions required to track or sign this specific document.
            </p>

            <div class="alert alert-light border-0 small text-start mb-4" style="background-color: #fdfdfd; color: #666;">
                <i class="fa fa-info-circle me-2 text-maroon"></i> 
                Only the <strong>Creator</strong>, <strong>Assigned Signatories</strong>, and the <strong>Records Office</strong> can access this cycle. Unless you are one of these, you will not be able to view or sign this document.
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-maroon shadow-sm">
                    <i class="fa fa-home me-2"></i> Return to Dashboard
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted small text-decoration-none mt-2">
                        Switch Account
                    </button>
                </form>
            </div>
        </div>
        <div class="card-footer bg-light border-0 py-3 text-muted" style="font-size: 0.75rem;">
            Tracking Security Protocol Enabled &copy; ISPSC ONWARDS UIP
        </div>
    </div>

</body>
</html>