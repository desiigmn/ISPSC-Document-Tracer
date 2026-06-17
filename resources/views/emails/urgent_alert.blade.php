<!DOCTYPE html>
<html>
<head>
    <style>
        .btn { 
            background: #800000; 
            color: #ffffff !important; 
            padding: 12px 25px; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: bold; 
            display: inline-block;
        }
        .info-box { 
            background: #f8f9fa; 
            padding: 15px; 
            border-left: 4px solid #800000; 
            margin: 20px 0; 
        }
        .reason-box { 
            background: #fff4f4; 
            padding: 15px; 
            border-left: 4px solid #dc3545; 
            margin: 20px 0; 
            color: #333;
        }
        .explanation-box {
            background: #f0f7ff;
            padding: 15px;
            border-left: 4px solid #0056b3;
            margin: 20px 0;
            color: #333;
        }
    </style>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <h2 style="color: #800000;">
        @if($uploaderNote)
            Document Explanation Provided
        @elseif($isResubmit)
            Document Corrected & Resubmitted
        @elseif($isReminder)
            Action Reminder: Document Hub
        @else
            Urgent Action Required
        @endif
    </h2>
    
    <p>This is a system-generated alert regarding the following transaction:</p>
    
    <div class="info-box">
        <strong>Tracking ID:</strong> <span style="font-family: monospace;">{{ $document->tracking_id }}</span><br>
        <strong>Document Title:</strong> {{ $document->title }}<br>
        <strong>Priority Level:</strong> 
        <span style="color: {{ $document->priority == 3 ? '#dc3545' : '#800000' }}; font-weight: bold;">
            {{ $document->priority == 3 ? 'EXTREMELY URGENT' : 'URGENT' }}
        </span>
    </div>

    {{-- Scenario 1: Uploader disputes the return and maintains original file --}}
    @if($uploaderNote)
        <div class="explanation-box">
            <strong style="color: #0056b3;">Creator's Justification:</strong>
            <p style="font-style: italic; margin-top: 5px; margin-bottom: 0;">"{{ $uploaderNote }}"</p>
        </div>
        <p>The document creator has reviewed your request for correction but has chosen to maintain the current version with the explanation provided above. Please review the document again.</p>
    
    {{-- Scenario 2: Uploader revised the file --}}
    @elseif($isResubmit)
        <div class="reason-box">
            <h4 style="margin-top: 0; color: #dc3545;">Original Correction Request:</h4>
            <p style="font-style: italic;">"{{ $reason }}"</p>
            <p style="margin-bottom: 0; font-size: 0.9rem;">
                A new version of the document has been uploaded addressing your feedback.
            </p>
        </div>
        <p>Please review the corrected file and apply your signature to proceed.</p>

    {{-- Scenario 3: Standard Alert --}}
    @else
        <p>This document is currently at your step in the sequence. Your signature or receipt confirmation is required to proceed.</p>
    @endif

    <p style="margin-top: 30px;">Please log in to the portal to take action:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('documents.view', $document->tracking_id) }}" class="btn">
            VIEW DOCUMENT HUB
        </a>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
    
    <small style="color: #666; display: block; text-align: center;">
        <strong>Ilocos Sur Polytechnic State College</strong><br>
        V-Action Slip Tracer System &copy; {{ date('Y') }}
    </small>

</body>
</html>