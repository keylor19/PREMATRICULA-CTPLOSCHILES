<?php

namespace App\Mail;

use App\Models\Prematricula;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrematriculaDecidida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prematricula $prematricula) {}

    public function envelope(): Envelope
    {
        $asunto = $this->prematricula->estado === 'aprobada'
            ? 'Prematrícula aprobada — ' . $this->prematricula->codigo
            : 'Prematrícula rechazada — ' . $this->prematricula->codigo;

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prematricula-decidida',
        );
    }
}