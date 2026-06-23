<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2e7d32; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f5f5f5; }
        .info { margin: 10px 0; }
        .label { font-weight: bold; color: #333; }
        .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
        .status-confirmado { color: #2e7d32; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>✅ Agendamento Confirmado!</h2>
        </div>
        <div class="content">
            <p>Olá <strong>{{ $agendamento->nome_cliente }}</strong>,</p>
            
            <p>Seu agendamento para <strong>{{ $agendamento->servico->nome }}</strong> foi <strong class="status-confirmado">CONFIRMADO</strong>!</p>

            <p>📅 <strong>Data:</strong> {{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }}</p>
            @if($agendamento->hora_agendamento)
                <p>🕐 <strong>Horário:</strong> {{ $agendamento->hora_agendamento }}</p>
            @endif

            <p>📍 <strong>Endereço:</strong> {{ $agendamento->endereco_completo }}</p>

            <p>Equipe está a caminho!</p>

            <p>Atenciosamente,<br>
            <strong>Equipe APP Pest Protect</strong></p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} APP Pest Protect - Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>