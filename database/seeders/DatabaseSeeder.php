<?php

namespace Database\Seeders;

use Database\Seeders\Production\DocumentTypeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DocumentTypeSeeder::class,
        ]);
    }
}
