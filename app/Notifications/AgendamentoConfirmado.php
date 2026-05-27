<?php

namespace App\Notifications;

use App\Models\Agendamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AgendamentoConfirmado extends Notification
{
    use Queueable;

    protected $agendamento;
    protected $mensagem;

    public function __construct(Agendamento $agendamento, $mensagem = null)
    {
        $this->agendamento = $agendamento;
        $this->mensagem = $mensagem;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('✅ Agendamento Confirmado - APP Pest Protect')
            ->greeting('Olá ' . $this->agendamento->nome_cliente . '!')
            ->line('Seu agendamento foi **confirmado** com sucesso!')
            ->line('**Detalhes do agendamento:**')
            ->line('📋 Serviço: ' . $this->agendamento->servico->nome)
            ->line('📅 Data: ' . $this->agendamento->data_agendamento->format('d/m/Y'))
            ->line('📍 Endereço: ' . $this->agendamento->endereco_completo . ', ' . $this->agendamento->bairro);

        if ($this->agendamento->hora_agendamento) {
            $mail->line('⏰ Hora: ' . substr($this->agendamento->hora_agendamento, 0, 5) . 'h');
        }

        if ($this->agendamento->servico->categoria === 'termico') {
            $mail->line('🔍 Nossa equipe técnica visitará o local conforme agendado.');
        }

        if ($this->mensagem) {
            $mail->line('📝 Observação: ' . $this->mensagem);
        }

        return $mail
            ->action('Ver Detalhes', url('/cliente/agendamentos'))
            ->line('Obrigado por escolher a APP Pest Protect!')
            ->line('Qualquer dúvida, entre em contato: comercial@app.co.mz');
    }
}