<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/teste-brevo-api', function () {

    $response = Http::withHeaders([
        'api-key' => env('BREVO_API_KEY'),
    ])->post('https://api.brevo.com/v3/smtp/email', [
        "sender" => [
            "name" => "App Pest Protect",
            "email" => env('BREVO_SENDER_EMAIL', 'abiliodanieln@gmail.com')
        ],
        "to" => [
            ["email" => "abiliodanieln@gmail.com"]
        ],
        "subject" => "Teste Brevo API",
        "htmlContent" => "<p>Teste email via API</p>"
    ]);

    return $response->json();
});