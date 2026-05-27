<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run() {
        $admin = User::create([
            'name'=>'Admin Paulo',
            'email'=>'admin@gmail.com',
            'password'=>Hash::make('123456'),
            'telefone'=>'847181457',
            'role'=>'admin',
        ]);




        // cria o registro na tabela alunos (precisa de curso_id e turma_id válidos)
        // Aluno::create([...]);
    }
}
