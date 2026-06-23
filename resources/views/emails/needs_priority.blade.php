<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', Helvetica, Arial, sans-serif; line-height: 1.6; color: #1a1a1a; margin: 0; padding: 0; }
        .wrapper { background-color: #f4f7f9; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e1e8ed; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: #0b132b; padding: 30px; border-bottom: 4px solid #800000; text-align: center; }
        .content { padding: 40px; }
        .info-card { background: #f8f9fa; border: 1px solid #e1e8ed; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .label { font-size: 11px; font-weight: 800; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .value { font-size: 15px; font-weight: 700; color: #111; margin-bottom: 15px; }
        .tracking-id { font-family: 'Courier New', monospace; color: #800000; font-size: 18px; font-weight: 900; }
        .btn { background: #800000; color: #ffffff !important; padding: 14px 30px; text-decoration: none; border-radius: 8px; font-weight: 800; font-size: 13px; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px; }
        .footer { padding: 25px; text-align: center; font-size: 11px; color: #888; border-top: 1px solid #f1f1f1; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 style="color: #fff; margin: 0; font-size: 24px; font-weight: 900; letter-spacing: -1px;">DocuRoute</h1>
                <p style="color: #FFCC00; margin: 5px 0 0 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px;">System Security Alert</p>
            </div>
            <div class="content">
                <h2 style="margin-top: 0; font-size: 20px; font-weight: 800;">Action Required: Priority Assignment</h2>
                <p>Hello Team,</p>
                <p>A new transaction has been finalized and is currently held in the <strong>Assignment Queue</strong>. Please review the details below and assign a priority level to commence tracking.</p>
                
                <div class="info-card">
                    <div class="label">Document Identifier</div>
                    <div class="value tracking-id">{{ $document->tracking_id }}</div>
                    
                    <div class="label">Classification / Title</div>
                    <div class="value">{{ $document->title }}</div>
                    
                    <div class="label">Uploader</div>
                    <div class="value" style="margin-bottom: 0;">{{ $document->uploader->username }} ({{ $document->uploader->office->office_name ?? 'Staff' }})</div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="{{ url('/dashboard?filter=review') }}" class="btn">Open Assignment Queue</a>
                </div>
            </div>
            <div class="footer">
                ILOCOS SUR POLYTECHNIC STATE COLLEGE | 2026<br>
                Automated System Notification &bull; Do not reply to this email.
            </div>
        </div>
    </div>
</body>
</html>