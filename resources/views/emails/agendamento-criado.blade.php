<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a237e; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f5f5f5; }
        .info { margin: 10px 0; }
        .label { font-weight: bold; color: #333; }
        .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
        .card { background: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .status-pendente { color: #f57c00; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>✅ Agendamento Realizado com Sucesso!</h2>
        </div>
        <div class="content">
            <p>Olá <strong>{{ $agendamento->nome_cliente }}</strong>,</p>
            
            <p>Recebemos o seu agendamento para o serviço de <strong>{{ $agendamento->servico->nome }}</strong>.</p>
            
            <div class="card">
                <h3>📋 Detalhes do Agendamento</h3>
                <div class="info"><span class="label">🛠 Serviço:</span> {{ $agendamento->servico->nome }}</div>
                <div class="info"><span class="label">📅 Data:</span> {{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }}</div>
                <div class="info"><span class="label">📍 Endereço:</span> {{ $agendamento->endereco_completo }}</div>
                <div class="info"><span class="label">📦 Compartimentos:</span> {{ $agendamento->quantidade_compartimentos }}</div>
                <div class="info"><span class="label">💰 Total:</span> {{ number_format($agendamento->total, 2, ',', '.') }} MZN</div>
                <div class="info"><span class="label">⚠️ Status:</span> <span class="status-pendente">{{ ucfirst($agendamento->status) }}</span></div>
            </div>

            @if($agendamento->servico->categoria === 'termico')
                <p><strong>🔔 Próximos Passos:</strong> Entraremos em contato em até 24h para agendar a visita técnica.</p>
            @else
                <p><strong>🔔 Próximos Passos:</strong> Aguarde a confirmação do seu agendamento.</p>
            @endif

            <p>Atenciosamente,<br>
            <strong>Equipe APP Pest Protect</strong></p>
        </div>
        <div class="footer">
            <p>Este e-mail foi enviado automaticamente. Por favor, não responda.</p>
            <p>© {{ date('Y') }} APP Pest Protect - Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>