<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the admin user with fixed credentials.
     * Uses updateOrCreate so re-running the seeder is idempotent.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Admin Castro Zavale',
                'password' => Hash::make('123456'),
                'telefone' => '842893357',
                'role'     => 'admin',
            ]
        );
    }
}
