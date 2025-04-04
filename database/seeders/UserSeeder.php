<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Usuário Comum',
                'email' => 'usuario@teste.com',
                'password' => Hash::make('senha123'),
                'is_admin' => false,
            ],
            [
                'name' => 'João Silva',
                'email' => 'joao@teste.com',
                'password' => Hash::make('senha123'),
                'is_admin' => false,
            ],
            [
                'name' => 'Maria Souza',
                'email' => 'maria@teste.com',
                'password' => Hash::make('senha123'),
                'is_admin' => false,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
