<?php

namespace App\Mail;

use App\Models\Agendamento;
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
            subject: 'Novo Agendamento Criado - Pest Protect',
        );
    }

    public function content(): Content
    {
        \Log::info('[AgendamentoCriadoMail] Mailable sendo construída', [
            'agendamento_id' => $this->agendamento->id,
            'email_destino'  => $this->agendamento->email_cliente,
            'nome_cliente'   => $this->agendamento->nome_cliente,
        ]);

        return new Content(
            view: 'emails.agendamento-criado', 
        );
    }

    public function attachments(): array
    {
        return [];
    }
}