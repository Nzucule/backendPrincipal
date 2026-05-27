<?php
// app/Http/Controllers/Api/ServicoController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicoController extends Controller
{
    // Listar todos os serviços (público)
    // No ServicoController.php
public function index()
{
    $servicos = Servico::all();
    
    // Adiciona a URL completa manualmente
    $servicos->each(function($servico) {
        if ($servico->imagem) {
            $servico->imagem_url = asset('storage/' . $servico->imagem);
        }
    });
    
    return response()->json($servicos);
}

    // Mostrar um serviço específico
    public function show($id)
    {
        $servico = Servico::find($id);
        
        if (!$servico) {
            return response()->json(['message' => 'Serviço não encontrado'], 404);
        }
        
        return response()->json($servico);
    }

    // Criar serviço (admin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria' => 'required|in:fumigacao,desratizacao,termico',
            'tipo_servico' => 'required|string',
            'duracao' => 'required|string',
            'preco' => 'required|string',
            'beneficios' => 'nullable|array',
            'detalhes' => 'nullable|array',
            'imagem' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['imagem']);
        
        if ($request->hasFile('imagem')) {
            $data['imagem'] = $request->file('imagem')->store('servicos', 'public');
        }

        $servico = Servico::create($data);

        return response()->json([
            'message' => 'Serviço criado com sucesso',
            'servico' => $servico
        ], 201);
    }

    // Atualizar serviço (admin)
    public function update(Request $request, $id)
    {
        $servico = Servico::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|string|max:255',
            'descricao' => 'sometimes|string',
            'categoria' => 'sometimes|in:fumigacao,desratizacao,termico',
            'tipo_servico' => 'sometimes|string',
            'duracao' => 'sometimes|string',
            'preco' => 'sometimes|string',
            'beneficios' => 'nullable|array',
            'detalhes' => 'nullable|array',
            'imagem' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['imagem']);
        
        if ($request->hasFile('imagem')) {
            $data['imagem'] = $request->file('imagem')->store('servicos', 'public');
        }

        $servico->update($data);

        return response()->json([
            'message' => 'Serviço atualizado com sucesso',
            'servico' => $servico
        ]);
    }

    // Deletar serviço (admin)
    public function destroy($id)
    {
        $servico = Servico::findOrFail($id);
        $servico->delete();

        return response()->json(['message' => 'Serviço removido com sucesso']);
    }
}