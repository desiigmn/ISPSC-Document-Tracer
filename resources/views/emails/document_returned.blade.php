<!DOCTYPE html>
<html>
<head>
    <style>
        .email-card { font-family: sans-serif; border: 1px solid #eee; padding: 20px; max-width: 600px; }
        .header { background: #800000; color: white; padding: 10px; text-align: center; }
        .content { padding: 20px; line-height: 1.6; }
        .reason-box { background: #fffbeb; border-left: 5px solid #ffcc00; padding: 15px; margin: 15px 0; font-style: italic; }
        .btn { background: #800000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="header">
            <h2>Document Returned</h2>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $document->uploader->username }}</strong>,</p>
            <p>The following document has been returned for corrections:</p>
            
            <ul>
                <li><strong>Tracking ID:</strong> {{ $document->tracking_id }}</li>
                <li><strong>Title:</strong> {{ $document->title }}</li>
            </ul>

            <div class="reason-box">
                <strong>Reason for Return:</strong><br>
                "{{ $remarks }}"
            </div>

            <p>Please log in to the system to re-upload the corrected document or provide further justification.</p>
            
            <a href="{{ route('documents.view', $document->tracking_id) }}" class="btn">View Document</a>
        </div>
    </div>
</body>
</html>