<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Notifications\VisitaTecnicaAgendada;
use App\Mail\AgendamentoCriadoMail;
use App\Mail\AgendamentoConfirmadoMail;
use App\Mail\AgendamentoCanceladoMail;
use App\Services\BrevoEmailService;
use Carbon\Carbon;

class AgendamentoController extends Controller
{
    protected $brevoService;

    public function __construct(BrevoEmailService $brevoService)
    {
        $this->brevoService = $brevoService;
    }

    // Cliente: Criar novo agendamento
    public function store(Request $request)
    {
        // Validação (mesma do seu código original)
        $validator = Validator::make($request->all(), [
            'servico_id' => 'required|exists:servicos,id',
            'endereco_completo' => 'required|string',
            'bairro' => 'required|string',
            'cidade' => 'required|string',
            'zona' => 'required|in:cidade,fora_cidade',
            'data_agendamento' => 'required|date|after_or_equal:today',
            'quantidade_compartimentos' => 'required|integer|min:1',
            'observacoes' => 'nullable|string',
            'nome_cliente' => 'required_if:anonimo,true|nullable|string|max:255',
            'email_cliente' => 'required_if:anonimo,true|nullable|email|max:255',
            'telefone_cliente' => 'required_if:anonimo,true|nullable|string|max:20',
            'anonimo' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $user = $request->user();
            $servico = Servico::findOrFail($request->servico_id);

            $userId = $user ? $user->id : null;
            $nomeCliente = $user ? $user->name : $request->nome_cliente;
            $emailCliente = $user ? $user->email : $request->email_cliente;
            $telefoneCliente = $user ? $user->telefone : $request->telefone_cliente;

            if ($servico->categoria === 'termico') {
                $agendamento = Agendamento::create([
                    'user_id' => $userId,
                    'servico_id' => $servico->id,
                    'nome_cliente' => $nomeCliente,
                    'email_cliente' => $emailCliente,
                    'telefone_cliente' => $telefoneCliente,
                    'endereco_completo' => $request->endereco_completo,
                    'bairro' => $request->bairro,
                    'cidade' => $request->cidade,
                    'zona' => $request->zona,
                    'data_agendamento' => $request->data_agendamento,
                    'quantidade_compartimentos' => 1,
                    'preco_unitario' => 0,
                    'taxa_logistica' => 0,
                    'subtotal' => 0,
                    'total' => 0,
                    'status' => 'pendente',
                    'observacoes' => $request->observacoes . ' [AGUARDANDO VISITA TÉCNICA]'
                ]);
            } else {
                $precos = $this->calcularPrecos($servico->categoria, $request->zona, $request->quantidade_compartimentos);

                $agendamento = Agendamento::create([
                    'user_id' => $userId,
                    'servico_id' => $servico->id,
                    'nome_cliente' => $nomeCliente,
                    'email_cliente' => $emailCliente,
                    'telefone_cliente' => $telefoneCliente,
                    'endereco_completo' => $request->endereco_completo,
                    'bairro' => $request->bairro,
                    'cidade' => $request->cidade,
                    'zona' => $request->zona,
                    'data_agendamento' => $request->data_agendamento,
                    'quantidade_compartimentos' => $request->quantidade_compartimentos,
                    'preco_unitario' => $precos['unitario'],
                    'taxa_logistica' => $precos['logistica'],
                    'subtotal' => $precos['subtotal'],
                    'total' => $precos['total'],
                    'status' => 'pendente',
                    'observacoes' => $request->observacoes
                ]);
            }

            DB::commit();

            // 🔥 ENVIO DE E-MAILS VIA BREVO API
            try {
                $emailAdmin = env('ADMIN_EMAIL', 'castrofranciscozavale@gmail.com');

                // 1. Enviar e-mail para o Cliente
                $clienteMail = new AgendamentoCriadoMail($agendamento);
                $clienteResult = $clienteMail->sendViaBrevo($this->brevoService);

                if (!$clienteResult['success']) {
                    Log::warning('Falha ao enviar e-mail para o cliente', [
                        'email' => $agendamento->email_cliente,
                        'error' => $clienteResult['error'] ?? 'Erro desconhecido'
                    ]);
                }

                // 2. Enviar notificação para o Admin
                $adminHtmlContent = $this->gerarEmailAdmin($agendamento, $servico);
                $adminResult = $this->brevoService->sendEmail(
                    $emailAdmin,
                    'Novo Agendamento - APP Pest Protect',
                    $adminHtmlContent
                );

                if (!$adminResult['success']) {
                    Log::warning('Falha ao enviar e-mail para o admin', [
                        'email' => $emailAdmin,
                        'error' => $adminResult['error'] ?? 'Erro desconhecido'
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Erro ao enviar e-mails via Brevo: ' . $e->getMessage());
                // Não interrompe o fluxo - o agendamento já foi criado
            }

            // Retornar resposta (mesma do seu código original)
            if ($servico->categoria === 'termico') {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação de visita técnica recebida! Entraremos em contato em até 24h.',
                    'data' => ['agendamento' => $agendamento]
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Agendamento realizado com sucesso!',
                'data' => [
                    'agendamento' => $agendamento,
                    'fatura' => [
                        'servico' => $servico->nome,
                        'quantidade' => $request->quantidade_compartimentos,
                        'preco_unitario' => $precos['unitario'],
                        'taxa_logistica' => $precos['logistica'],
                        'subtotal' => $precos['subtotal'],
                        'total' => $precos['total']
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gera o HTML do e-mail para o admin
     */
    private function gerarEmailAdmin($agendamento, $servico)
    {
        return "
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
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📌 NOVO AGENDAMENTO REALIZADO</h2>
                </div>
                <div class='content'>
                    <div class='info'><span class='label'>👤 Cliente:</span> {$agendamento->nome_cliente}</div>
                    <div class='info'><span class='label'>📧 Email:</span> {$agendamento->email_cliente}</div>
                    <div class='info'><span class='label'>📞 Telefone:</span> {$agendamento->telefone_cliente}</div>
                    <hr>
                    <div class='info'><span class='label'>🛠 Serviço:</span> {$servico->nome}</div>
                    <div class='info'><span class='label'>📅 Data do agendamento:</span> {$agendamento->data_agendamento}</div>
                    <div class='info'><span class='label'>📍 Endereço:</span> {$agendamento->endereco_completo}, {$agendamento->bairro}, {$agendamento->cidade}</div>
                    <div class='info'><span class='label'>⚠️ Status:</span> {$agendamento->status}</div>
                    <hr>
                    <p><strong>Ação necessária:</strong> Acesse o painel administrativo para confirmar ou agendar a visita técnica.</p>
                </div>
                <div class='footer'>
                    <p>Este e-mail foi enviado automaticamente pelo sistema APP Pest Protect.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    // Admin: Atualizar status do agendamento (MODIFICADO)
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pendente,confirmado,concluido,cancelado',
            'hora_agendamento' => 'nullable|date_format:H:i',
            'mensagem' => 'nullable|string',
            'data_visita' => 'nullable|date',
            'hora_visita' => 'nullable|date_format:H:i'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agendamento = Agendamento::with('servico', 'user')->findOrFail($id);
        
        $data = ['status' => $request->status];
        $oldStatus = $agendamento->status;
        
        if ($request->has('hora_agendamento')) {
            $data['hora_agendamento'] = $request->hora_agendamento;
        }

        $agendamento->update($data);

        // 🔥 ENVIO DE E-MAILS VIA BREVO API
        try {
            if ($oldStatus !== $request->status) {
                switch ($request->status) {
                    case 'confirmado':
                        $confirmMail = new AgendamentoConfirmadoMail($agendamento);
                        $result = $confirmMail->sendViaBrevo($this->brevoService);
                        if (!$result['success']) {
                            Log::warning('Falha ao enviar e-mail de confirmação', [
                                'email' => $agendamento->email_cliente,
                                'error' => $result['error'] ?? 'Erro desconhecido'
                            ]);
                        }
                        break;
                        
                    case 'cancelado':
                        $cancelMail = new AgendamentoCanceladoMail($agendamento);
                        $result = $cancelMail->sendViaBrevo($this->brevoService);
                        if (!$result['success']) {
                            Log::warning('Falha ao enviar e-mail de cancelamento', [
                                'email' => $agendamento->email_cliente,
                                'error' => $result['error'] ?? 'Erro desconhecido'
                            ]);
                        }
                        break;
                }
            }

            // Notificação de visita técnica (apenas para usuários logados)
            if ($agendamento->servico->categoria === 'termico' && $request->has('data_visita') && $agendamento->user) {
                $agendamento->user->notify(new VisitaTecnicaAgendada(
                    $agendamento, 
                    new \Carbon\Carbon($request->data_visita),
                    $request->hora_visita
                ));
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação via Brevo: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Agendamento atualizado com sucesso',
            'agendamento' => $agendamento
        ]);
    }

    // O restante dos métodos permanece igual...
    private function calcularPrecos($categoria, $zona, $quantidade) { /* ... */ }
    public function meus(Request $request) { /* ... */ }
    public function historico(Request $request) { /* ... */ }
    public function cancelar(Request $request, $id) { /* ... */ }
    public function index(Request $request) { /* ... */ }
    public function destroy($id) { /* ... */ }
}