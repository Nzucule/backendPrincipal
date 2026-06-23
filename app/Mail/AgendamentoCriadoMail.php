<?php

namespace App\Mail;

use App\Models\Agendamento;
use App\Services\BrevoEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgendamentoCriadoMail extends Mailable
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
            subject: 'Novo Agendamento - APP Pest Protect',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agendamento-criado',
        );
    }

    /**
     * Método para enviar via Brevo API
     */
    public function sendViaBrevo(BrevoEmailService $brevoService)
    {
        // Renderizar o conteúdo HTML da view
        $htmlContent = view('emails.agendamento-criado', [
            'agendamento' => $this->agendamento
        ])->render();

        // Dados do cliente
        $to = $this->agendamento->email_cliente;

        // Opções adicionais
        $options = [];

        // Adicionar anexos se necessário
        if (isset($this->agendamento->anexo)) {
            // $options['attachments'] = [...];
        }

        // Enviar via Brevo
        return $brevoService->sendEmail(
            $to,
            'Novo Agendamento - APP Pest Protect',
            $htmlContent,
            $options
        );
    }

    public function attachments(): array
    {
        return [];
    }
}