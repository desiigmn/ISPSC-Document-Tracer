<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentReturnedAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $remarks;

    public function __construct(Document $document, $remarks)
    {
        $this->document = $document;
        $this->remarks = $remarks;
    }

    public function build()
    {
        return $this->subject('Action Required: Document Returned - ' . $this->document->tracking_id)
                    ->view('emails.document_returned');
    }
}