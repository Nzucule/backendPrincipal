<?php

namespace App\Notifications;

use App\Models\Agendamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AgendamentoCancelado extends Notification
{
    use Queueable;

    protected $agendamento;
    protected $motivo;

    public function __construct(Agendamento $agendamento, $motivo = null)
    {
        $this->agendamento = $agendamento;
        $this->motivo = $motivo;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('❌ Agendamento Cancelado - APP Pest Protect')
            ->greeting('Olá ' . $this->agendamento->nome_cliente . '!')
            ->line('Informamos que seu agendamento foi **cancelado**.')
            ->line('**Detalhes do agendamento cancelado:**')
            ->line('📋 Serviço: ' . $this->agendamento->servico->nome)
            ->line('📅 Data solicitada: ' . $this->agendamento->data_agendamento->format('d/m/Y'));

        if ($this->motivo) {
            $mail->line('📝 Motivo: ' . $this->motivo);
        }

        return $mail
            ->line('Se desejar, você pode realizar um novo agendamento em nosso site.')
            ->action('Agendar Novamente', url('/servicos'))
            ->line('Estamos à disposição para esclarecer dúvidas.')
            ->line('APP Pest Protect - Sua vida longe de pragas!');
    }
}