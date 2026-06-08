<!DOCTYPE html>
<html>
<head>
    <title>ISPSC | Public Document Tracer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .tracer-header { background: #800000; color: #FFCC00; padding: 30px 0; border-bottom: 5px solid #FFCC00; }
    </style>
</head>
<body>
    <div class="tracer-header text-center mb-5">
        <h2>ILOCOS SUR POLYTECHNIC STATE COLLEGE</h2>
        <p class="mb-0 fw-bold">ONWARDS UIP - Public Tracking System</p>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="{{ route('public.search') }}" method="GET" class="card p-4 shadow-sm mb-4">
                    <label class="fw-bold mb-2">Enter Tracking ID:</label>
                    <div class="input-group">
                        <input type="text" name="tid" class="form-control" placeholder="ISPSC-UIP-XXXX" value="{{ request('tid') }}">
                        <button class="btn btn-dark">Search</button>
                    </div>
                </form>

                @if($document)
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="text-maroon">Document Found: {{ $document->tracking_id }}</h5>
                            <p><strong>Title:</strong> {{ $document->title }}</p>
                            <p><strong>Current Station:</strong> {{ $document->currentOffice->office_name ?? 'Records Gateway' }}</p>
                            <span class="badge bg-{{ $document->status == 'accepted' ? 'success' : 'warning text-dark' }}">
                                {{ strtoupper($document->status) }}
                            </span>
                            <hr>
                            <h6>Movement History:</h6>
                            @foreach($document->logs as $log)
                                <div class="border-bottom py-2 small">
                                    <span class="text-muted">{{ $log->created_at->format('M d, Y h:i A') }}</span><br>
                                    <strong>{{ $log->action }}</strong> at {{ $log->office_name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif(request('tid'))
                    <div class="alert alert-danger">No document found with that Tracking ID.</div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>