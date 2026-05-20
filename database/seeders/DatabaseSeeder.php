<?php

namespace Database\Seeders;

use Database\Seeders\Development\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeders de desenvolvimento (apenas em ambiente local/dev)
        if (app()->environment(['local', 'development'])) {
            $this->call([
                UserSeeder::class,
            ]);

            $this->command->info('INFO  Development seeders executed.');
            return;
        }

        // Seeders de produção (staging/production)
        // if (app()->environment(['staging', 'production'])) {
        //     $this->call([
        //         // Adicione seeders de produção aqui se necessário
        //         // Ex: RolesAndPermissionsSeeder::class,
        //     ]);
        // }

        $this->command->info('INFO  No seeders to run for environment: ' . app()->environment() . '.');
    }
}