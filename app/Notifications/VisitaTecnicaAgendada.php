<?php

namespace App\Notifications;

use App\Models\Agendamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VisitaTecnicaAgendada extends Notification
{
    use Queueable;

    protected $agendamento;
    protected $dataVisita;
    protected $horaVisita;

    public function __construct(Agendamento $agendamento, $dataVisita, $horaVisita = null)
    {
        $this->agendamento = $agendamento;
        $this->dataVisita = $dataVisita;
        $this->horaVisita = $horaVisita;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('🔍 Visita Técnica Agendada - APP Pest Protect')
            ->greeting('Olá ' . $this->agendamento->nome_cliente . '!')
            ->line('Sua **visita técnica** para avaliação do local foi agendada!')
            ->line('**Detalhes da visita:**')
            ->line('📅 Data: ' . $this->dataVisita->format('d/m/Y'));

        if ($this->horaVisita) {
            $mail->line('⏰ Hora: ' . $this->horaVisita);
        }

        return $mail
            ->line('📍 Endereço: ' . $this->agendamento->endereco_completo . ', ' . $this->agendamento->bairro)
            ->line('👨‍🔬 Nossa equipe técnica estará no local para avaliar a infestação.')
            ->line('Após a visita, enviaremos o orçamento personalizado.')
            ->action('Acompanhar', url('/cliente/agendamentos'))
            ->line('Qualquer alteração, entre em contato: +258 84 3830770');
    }
}