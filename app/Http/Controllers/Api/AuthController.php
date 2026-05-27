<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    // 📌 Registrar novo usuário
    public function registrar(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'telefone' => 'nullable',
            'role' => 'required', // admin, barbeiro, cliente
            
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefone' => $request->telefone,
            'role' => $request->role,
            
        ]);

        return response()->json([
            'mensagem' => 'Usuário registrado com sucesso!',
            'usuario' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $creds = $request->validate(['email'=>'required|email','password'=>'required']);
        if (!Auth::attempt($creds)) {
            return response()->json(['message'=>'Credenciais inválidas'], 401);
        }

        $user = $request->user();
        $token = $user->createToken('react-token')->plainTextToken;

        return response()->json(['user'=>$user, 'token'=>$token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Desconectado']);
    }
}
