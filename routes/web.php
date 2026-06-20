<?php

use Illuminate\Support\Facades\Mail;

Route::get('/teste-email', function () {

    Mail::raw('Teste de envio do App Pest Protect', function ($message) {
        $message->to('abiliodanieln@gmail.com')
                ->subject('Teste Brevo Laravel');
    });

    return 'Email enviado!';
});