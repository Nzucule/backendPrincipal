<h2>Agendamento Recebido</h2>

<p>Prezado(a) {{ $agendamento->nome_cliente }},</p>

<p>Confirmamos a receção do seu pedido de agendamento em nosso sistema.</p>

<hr>

<p><b>Data solicitada:</b> {{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }}</p>

<p><b>Próximo passo:</b> O seu agendamento encontra-se em análise. Em breve, enviará uma notificação de confirmação para o seu e-mail ou via WhatsApp através do canal oficial da <b>APP Pest Protect</b>.</p>

<hr>

<!-- Bloco de Diferencial Institucional -->
<div style="background-color: #fcfcfc; border-left: 4px solid #0056b3; padding: 15px; margin: 20px 0; border-radius: 4px; font-family: sans-serif;">
    <h3 style="margin-top: 0; color: #111; font-size: 16px;">Porquê escolher a Pest Protect?</h3>
    <p style="margin-bottom: 8px; font-size: 14px; color: #333;"><b>Excelência em Proteção:</b> Garantimos ambientes seguros e livres de pragas através de equipas devidamente certificadas e metodologias avançadas.</p>
    <p style="margin-bottom: 8px; font-size: 14px; color: #333;"><b>Responsabilidade Ambiental:</b> Utilizamos produtos de alta eficácia que cumprem rigorosos padrões de segurança para a saúde humana e preservação do meio ambiente.</p>
    <p style="font-size: 14px; color: #333;"><b>Gestão Integrada:</b> Através da nossa plataforma Pest Protect, disponibilizamos o controlo e acompanhamento em tempo real de todos os seus serviços.</p>
</div>

<hr>

<p>Caso necessite de esclarecimentos adicionais ou assistência imediata, por favor, contacte os nossos serviços de apoio:</p>
<ul style="list-style-type: none; padding-left: 0;">
    <li style="margin-bottom: 5px;"><b>Telefone:</b> +258 82 299 6958</li>
    <li><b>Telefone:</b> +258 87 383 0003</li>
</ul>

<p>Agradecemos a sua preferência e a confiança depositada nos nossos serviços.</p>

<p>Com os melhores cumprimentos,<br>
<b>Equipa APP Pest Protect</b></p>