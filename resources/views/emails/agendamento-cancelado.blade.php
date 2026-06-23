<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #c62828; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f5f5f5; }
        .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
        .status-cancelado { color: #c62828; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>❌ Agendamento Cancelado</h2>
        </div>
        <div class="content">
            <p>Olá <strong>{{ $agendamento->nome_cliente }}</strong>,</p>
            
            <p>Seu agendamento para <strong>{{ $agendamento->servico->nome }}</strong> foi <strong class="status-cancelado">CANCELADO</strong>.</p>

            <p>📅 <strong>Data:</strong> {{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }}</p>

            <p>Se você não solicitou este cancelamento, entre em contato conosco.</p>

            <p>Atenciosamente,<br>
            <strong>Equipe APP Pest Protect</strong></p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} APP Pest Protect - Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>