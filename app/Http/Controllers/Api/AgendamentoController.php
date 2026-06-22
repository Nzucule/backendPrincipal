<?php
// app/Http/Controllers/Api/AgendamentoController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Notifications\AgendamentoConfirmado;
use App\Notifications\AgendamentoCancelado;
use App\Notifications\VisitaTecnicaAgendada;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoCriadoMail;
use App\Mail\AgendamentoConfirmadoMail;
use App\Mail\AgendamentoCanceladoMail;
use Carbon\Carbon;

class AgendamentoController extends Controller
{
    // Cliente: Criar novo agendamento
    public function store(Request $request)
    {
        // 1. Validação condicional: os campos do cliente só são obrigatórios se for anónimo
        $validator = Validator::make($request->all(), [
            'servico_id' => 'required|exists:servicos,id',
            'endereco_completo' => 'required|string',
            'bairro' => 'required|string',
            'cidade' => 'required|string',
            'zona' => 'required|in:cidade,fora_cidade',
            'data_agendamento' => 'required|date|after_or_equal:today',
            'quantidade_compartimentos' => 'required|integer|min:1',
            'observacoes' => 'nullable|string',
            
            // Novos campos validados apenas se o utilizador não estiver autenticado
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
            $user = $request->user(); // Será null se o utilizador for anónimo
            $servico = Servico::findOrFail($request->servico_id);

            // 2. Definição dinâmica dos dados do cliente (Fallback)
            $userId          = $user ? $user->id : null; // Requer que user_id seja nullable na DB
            $nomeCliente     = $user ? $user->name : $request->nome_cliente;
            $emailCliente    = $user ? $user->email : $request->email_cliente;
            $telefoneCliente = $user ? $user->telefone : $request->telefone_cliente;

            // 3. Criar o agendamento baseado na categoria
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
                // Para fumigação e desratização (cálculo normal)
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

            // 🔥 LOGICA DE ENVIO DE E-MAILS
            try {
                $emailAdmin = 'castrofranciscozavale@gmail.com';

                // 1. Envia para o Cliente (Usa o e-mail dinâmico guardado)
                Mail::to($agendamento->email_cliente)->send(new AgendamentoCriadoMail($agendamento));

                // 2. Envia Notificação para o Admin
                Mail::raw(
"📌 NOVO AGENDAMENTO REALIZADO

👤 Cliente: {$agendamento->nome_cliente}
📧 Email: {$agendamento->email_cliente}
📞 Telefone: {$agendamento->telefone_cliente}

🛠 Serviço: {$servico->nome}
📅 Data do agendamento: {$agendamento->data_agendamento}

📍 Endereço: {$agendamento->endereco_completo}, {$agendamento->bairro}, {$agendamento->cidade}

⚠️ Status: {$agendamento->status}
",
function ($message) use ($emailAdmin) {
    $message->to($emailAdmin)
            ->subject('Novo Agendamento - APP Pest Protect');
});

            } catch (\Exception $e) {
                \Log::error('Erro ao enviar e-mails de criação: ' . $e->getMessage());
            }

            // 3. Retornar a resposta correta para o Frontend
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

    private function calcularPrecos($categoria, $zona, $quantidade)
    {
        $precos = [];

        if ($categoria === 'fumigacao') {
            if ($zona === 'cidade') {
                $precos['unitario'] = 925;
                $precos['logistica'] = 300;
            } else {
                $precos['unitario'] = 1025;
                $precos['logistica'] = 500;
            }
        } elseif ($categoria === 'desratizacao') {
            if ($zona === 'cidade') {
                $precos['unitario'] = 510;
                $precos['logistica'] = 300;
            } else {
                $precos['unitario'] = 610;
                $precos['logistica'] = 500;
            }
        }

        $precos['subtotal'] = $precos['unitario'] * $quantidade;
        $precos['total'] = $precos['subtotal'] + $precos['logistica'];

        return $precos;
    }

    // Cliente: Listar meus agendamentos
    public function meus(Request $request)
    {
        $agendamentos = Agendamento::with('servico')
            ->where('user_id', $request->user()->id)
            ->orderBy('data_agendamento', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $agendamentos
        ]);
    }

    // Cliente: Ver histórico (apenas concluídos e cancelados)
    public function historico(Request $request)
    {
        try {
            $historico = Agendamento::with('servico')
                ->where('user_id', $request->user()->id)
                ->whereIn('status', ['concluido', 'cancelado'])
                ->orderBy('data_agendamento', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $historico
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar histórico'
            ], 500);
        }
    }

    // Cliente: Cancelar agendamento
    public function cancelar(Request $request, $id)
    {
        try {
            $agendamento = Agendamento::where('user_id', $request->user()->id)
                ->where('id', $id)
                ->first();

            if (!$agendamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agendamento não encontrado'
                ], 404);
            }

            if (!in_array($agendamento->status, ['pendente', 'confirmado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este agendamento não pode ser cancelado.'
                ], 422);
            }

            $agendamento->status = 'cancelado';
            $agendamento->save();

            return response()->json([
                'success' => true,
                'message' => 'Agendamento cancelado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar agendamento'
            ], 500);
        }
    }

    // Admin: Listar todos agendamentos
    public function index(Request $request)
    {
        $agendamentos = Agendamento::with(['user', 'servico'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($agendamentos);
    }

    // Admin: Atualizar status do agendamento
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

        try {
            if ($oldStatus !== $request->status) {
                switch ($request->status) {
                    case 'confirmado':
                        Mail::to($agendamento->email_cliente)->queue(new AgendamentoConfirmadoMail($agendamento));
                        break;
                        
                    case 'cancelado':
                        Mail::to($agendamento->email_cliente)->queue(new AgendamentoCanceladoMail($agendamento));
                        break;
                }
            }

            // Apenas envia a notificação via base de dados se o agendamento possuir um user associado (não anónimo)
            if ($agendamento->servico->categoria === 'termico' && $request->has('data_visita') && $agendamento->user) {
                $agendamento->user->notify(new VisitaTecnicaAgendada(
                    $agendamento, 
                    new \Carbon\Carbon($request->data_visita),
                    $request->hora_visita
                ));
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar notificação: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Agendamento updated com sucesso',
            'agendamento' => $agendamento
        ]);
    }

    // Admin: Deletar agendamento
    public function destroy($id)
    {
        $agendamento = Agendamento::findOrFail($id);
        $agendamento->delete();

        return response()->json(['message' => 'Agendamento removido com sucesso']);
    }
}