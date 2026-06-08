<!DOCTYPE html>
<html>
<head>
    <style>
        .btn { background: #800000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body style="font-family: sans-serif; line-height: 1.6;">
    <h2 style="color: #800000;">
        {{ $isReminder ? 'Action Reminder' : 'Urgent Document Alert' }}
    </h2>
    
    <p>This is a system-generated alert for Document Tracking ID: <strong>{{ $document->tracking_id }}</strong>.</p>
    
    <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #800000; margin: 20px 0;">
        <strong>Document Title:</strong> {{ $document->title }}<br>
        <strong>Priority:</strong> {{ $document->priority == 3 ? 'EXTREMELY URGENT' : 'URGENT' }}<br>
        <strong>Current Status:</strong> Waiting for your signature/receipt.
    </div>

    <p>Please log in to the portal to take action on this document.</p>
    
    <br>
    <a href="{{ route('documents.view', $document->id) }}" class="btn">VIEW DOCUMENT HUB</a>
    <br><br>

    <small style="color: #666;">If you have already signed this document, please ignore this message.</small>
</body>
</html>