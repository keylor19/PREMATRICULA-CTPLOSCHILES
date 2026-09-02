<?php

namespace App\Mail;

use App\Models\Prematricula;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrematriculaRecibida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Prematricula $prematricula,
        public ?string $rutaPdf = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Prematrícula recibida — ' . $this->prematricula->codigo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prematricula-recibida',
        );
    }

    public function attachments(): array
    {
        if (!$this->rutaPdf) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $this->rutaPdf)
                ->as('Matricula-' . $this->prematricula->codigo . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}