<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoEmailService
{
    protected $apiKey;
    protected $senderEmail;
    protected $senderName;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('BREVO_API_KEY');
        $this->senderEmail = env('BREVO_SENDER_EMAIL', 'abiliodanieln@gmail.com');
        $this->senderName = env('BREVO_SENDER_NAME', 'App Pest Protect');
        $this->baseUrl = 'https://api.brevo.com/v3';
    }

    /**
     * Envia um e-mail usando a API da Brevo
     *
     * @param string|array $to Email ou array de emails
     * @param string $subject Assunto
     * @param string $htmlContent Conteúdo HTML
     * @param array $options Opções adicionais (cc, bcc, anexos, etc)
     * @return array
     */
    public function sendEmail($to, string $subject, string $htmlContent, array $options = [])
    {
        try {
            // Normalizar destinatários
            $recipients = $this->normalizeRecipients($to);

            $payload = [
                'sender' => [
                    'name' => $this->senderName,
                    'email' => $this->senderEmail
                ],
                'to' => $recipients,
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ];

            // Adicionar CC se fornecido
            if (isset($options['cc'])) {
                $payload['cc'] = $this->normalizeRecipients($options['cc']);
            }

            // Adicionar BCC se fornecido
            if (isset($options['bcc'])) {
                $payload['bcc'] = $this->normalizeRecipients($options['bcc']);
            }

            // Adicionar anexos se fornecidos
            if (isset($options['attachments']) && !empty($options['attachments'])) {
                $payload['attachment'] = $options['attachments'];
            }

            // Adicionar replyTo se fornecido
            if (isset($options['replyTo'])) {
                $payload['replyTo'] = $options['replyTo'];
            }

            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/smtp/email', $payload);

            if ($response->successful()) {
                Log::info('E-mail enviado com sucesso via Brevo', [
                    'to' => $to,
                    'subject' => $subject,
                    'messageId' => $response->json('messageId')
                ]);

                return [
                    'success' => true,
                    'messageId' => $response->json('messageId'),
                    'response' => $response->json()
                ];
            } else {
                Log::error('Erro ao enviar e-mail via Brevo', [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => $response->body(),
                    'status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar e-mail via Brevo', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Normaliza destinatários para o formato esperado pela Brevo
     */
    private function normalizeRecipients($recipients): array
    {
        if (is_string($recipients)) {
            return [['email' => $recipients]];
        }

        if (is_array($recipients) && isset($recipients['email'])) {
            return [$recipients];
        }

        if (is_array($recipients)) {
            $normalized = [];
            foreach ($recipients as $recipient) {
                if (is_string($recipient)) {
                    $normalized[] = ['email' => $recipient];
                } elseif (is_array($recipient) && isset($recipient['email'])) {
                    $normalized[] = $recipient;
                }
            }
            return $normalized;
        }

        return [];
    }

    /**
     * Envia e-mail com template HTML renderizado
     */
    public function sendView($to, string $subject, string $view, array $data = [], array $options = [])
    {
        $htmlContent = view($view, $data)->render();
        return $this->sendEmail($to, $subject, $htmlContent, $options);
    }

    /**
     * Envia e-mail para múltiplos destinatários
     */
    public function sendToMultiple(array $recipients, string $subject, string $htmlContent, array $options = [])
    {
        return $this->sendEmail($recipients, $subject, $htmlContent, $options);
    }

    /**
     * Envia e-mail com template da Brevo (se usar templates salvos na plataforma)
     */
    public function sendTemplate(int $templateId, array $recipients, array $params = [], array $options = [])
    {
        try {
            $payload = [
                'templateId' => $templateId,
                'to' => $this->normalizeRecipients($recipients),
                'params' => $params,
            ];

            if (isset($options['cc'])) {
                $payload['cc'] = $this->normalizeRecipients($options['cc']);
            }

            if (isset($options['bcc'])) {
                $payload['bcc'] = $this->normalizeRecipients($options['bcc']);
            }

            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/smtp/templates/' . $templateId . '/send', $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'messageId' => $response->json('messageId')
                ];
            }

            return [
                'success' => false,
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}