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

    /**
     * Create a new message instance.
     */
    public function __construct($document, $isReminder = false)
    {
        $this->document = $document;
        $this->isReminder = $isReminder;
    }

    /**
     * Get the message envelope (Subject line logic).
     */
    public function envelope(): Envelope
    {
        $prefix = $this->isReminder ? "[NUDGE] " : "URGENT: ";
        
        return new Envelope(
            // FIXED: Added "$this->" before document
            subject: $prefix . $this->document->tracking_id . " - Action Required",
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