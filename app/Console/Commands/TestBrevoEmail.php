<?php

namespace App\Console\Commands;

use App\Services\BrevoEmailService;
use Illuminate\Console\Command;

class TestBrevoEmail extends Command
{
    protected $signature = 'brevo:test {email?}';
    protected $description = 'Testa o envio de e-mail via Brevo API';

    public function handle(BrevoEmailService $brevoService)
    {
        $email = $this->argument('email') ?? 'abiliodanieln@gmail.com';

        $this->info("Enviando e-mail de teste para: {$email}");

        $result = $brevoService->sendEmail(
            $email,
            '🧪 Teste Brevo API - Laravel Command',
            '<h1>✅ Teste Concluído!</h1>
             <p>Este e-mail foi enviado via comando Artisan.</p>
             <p><strong>Data:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>'
        );

        if ($result['success']) {
            $this->info('✅ E-mail enviado com sucesso!');
            $this->info('Message ID: ' . ($result['messageId'] ?? 'N/A'));
        } else {
            $this->error('❌ Falha ao enviar e-mail');
            $this->error('Erro: ' . ($result['error'] ?? 'Erro desconhecido'));
        }

        return $result['success'] ? 0 : 1;
    }
}