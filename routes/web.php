<?php

use Illuminate\Support\Facades\Route;
use App\Services\BrevoEmailService;

Route::get('/teste-brevo-api', function (BrevoEmailService $brevoService) {
    
    // Teste simples
    $result = $brevoService->sendEmail(
        'abiliodanieln@gmail.com',
        'Teste Brevo API - Laravel',
        '<h1>✅ Teste de E-mail via Brevo API</h1>
         <p>Este e-mail foi enviado com sucesso usando a API da Brevo!</p>
         <p><strong>Data:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>'
    );

    return response()->json([
        'success' => $result['success'],
        'message_id' => $result['messageId'] ?? null,
        'error' => $result['error'] ?? null,
        'full_response' => $result
    ]);
});

// Rota para testar com template Brevo (se tiver criado um template)
Route::get('/teste-brevo-template', function (BrevoEmailService $brevoService) {
    $result = $brevoService->sendTemplate(
        1, // ID do template no Brevo
        'abiliodanieln@gmail.com',
        [
            'nome' => 'Abílio',
            'data' => now()->format('d/m/Y'),
        ]
    );

    return response()->json($result);
});