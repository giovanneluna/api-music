<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToAdmin extends Command
{
    protected $signature = 'user:promote {email}';
    protected $description = 'Promove um usuário a administrador pelo email';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuário com email {$email} não encontrado");
            return 1;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("Usuário {$user->name} promovido a administrador com sucesso");
        return 0;
    }
}
