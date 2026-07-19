<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentLinkMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $documentType,
        public string $documentNumber,
        public string $url,
        public string $fromAddress,
        public string $fromName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: $this->documentType.' '.$this->documentNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-link',
        );
    }
}
