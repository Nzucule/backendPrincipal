<?php

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Route;

Route::get('/teste-email', function () {

    try {
        Mail::raw('Teste de envio do App Pest Protect', function ($message) {
            $message->to('abiliodanieln@gmail.com')
                    ->subject('Teste Brevo Laravel');
        });

        return 'EMAIL ENVIADO COM SUCESSO';

    } catch (\Exception $e) {
        return 'ERRO: ' . $e->getMessage();
    }
});