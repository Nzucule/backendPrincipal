<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // 🔹 Listar clientes
    public function clientes()
    {
        return User::where('role', 'cliente')->get();
    }

    // 🔹 Excluir cliente
    public function deleteCliente($id)
    {
        $cliente = User::where('role', 'cliente')->findOrFail($id);
        $cliente->delete();

        return response()->json(["mensagem" => "Cliente removido com sucesso"]);
    }

    // 🔹 Obter perfil do usuário logado
    public function getPerfil(Request $request)
    {
        try {
            $user = $request->user();
            
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar perfil: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar perfil.'
            ], 500);
        }
    }

    // 🔹 Atualizar perfil do usuário logado
    public function updatePerfil(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'telefone' => 'sometimes|string|max:20',
                'password' => 'sometimes|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['name', 'email', 'telefone']);
            
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao atualizar perfil: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil.'
            ], 500);
        }
    }
}