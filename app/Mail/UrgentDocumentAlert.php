<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UrgentDocumentAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $isReminder;
    public $isResubmit;
    public $reason;

    /**
     * Create a new message instance.
     * 
     * @param mixed $document
     * @param bool $isResubmit - True if document was corrected and sent back
     * @param string|null $reason - The return reason from the logs
     * @param bool $isReminder - True if this is a follow-up nudge
     */
public $uploaderNote; // New property

public function __construct($document, $isResubmit = false, $reason = null, $isReminder = false, $uploaderNote = null)
{
    $this->document = $document;
    $this->isResubmit = $isResubmit;
    $this->reason = $reason;
    $this->isReminder = $isReminder;
    $this->uploaderNote = $uploaderNote;
}

    /**
     * Get the message envelope (Subject line logic).
     */
    public function envelope(): Envelope
    {
        // Dynamic Subject Logic
        if ($this->isResubmit) {
            $subject = "CORRECTED DOCUMENT: " . $this->document->tracking_id;
        } elseif ($this->isReminder) {
            $subject = "[NUDGE] " . $this->document->tracking_id . " - Action Required";
        } else {
            $subject = "URGENT: " . $this->document->tracking_id . " - Action Required";
        }
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition (The View).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.urgent_alert',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    
}