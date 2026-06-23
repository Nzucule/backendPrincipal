<?php

namespace App\Mail;

use App\Models\Agendamento;
use App\Services\BrevoEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgendamentoCanceladoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $agendamento;

    public function __construct(Agendamento $agendamento)
    {
        $this->agendamento = $agendamento;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Agendamento Cancelado - APP Pest Protect',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agendamento-cancelado',
        );
    }

    /**
     * Método para enviar via Brevo API
     */
    public function sendViaBrevo(BrevoEmailService $brevoService)
    {
        $htmlContent = view('emails.agendamento-cancelado', [
            'agendamento' => $this->agendamento
        ])->render();

        $to = $this->agendamento->email_cliente;

        return $brevoService->sendEmail(
            $to,
            'Agendamento Cancelado - APP Pest Protect',
            $htmlContent
        );
    }

    public function attachments(): array
    {
        return [];
    }
}