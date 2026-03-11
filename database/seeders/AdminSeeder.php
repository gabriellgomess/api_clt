<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@dataprev.local');
        $password = env('ADMIN_PASSWORD', 'dataprev@admin');
        $name = env('ADMIN_NAME', 'Administrador');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
            ]
        );

        $this->command->info("Usuário admin criado: {$email}");
        $this->command->info("Senha: {$password}");
        $this->command->warn("Altere a senha após o primeiro acesso.");
    }
}
