<?php

namespace Database\Seeders\Production;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'key' => 'cpf',
                'label' => 'CPF',
                'mask' => '###.###.###-##',
            ],
            [
                'key' => 'cnpj',
                'label' => 'CNPJ',
                'mask' => '##.###.###/####-##',
            ],
            [
                'key' => 'rg',
                'label' => 'RG',
                'mask' => '##.###.###-#',
            ],
            [
                'key' => 'cnh',
                'label' => 'CNH',
                'mask' => '###########',
            ],
        ];

        foreach ($types as $type) {
            DB::table('hr.document_type')->insert([
                'uuid' => Str::uuid(),
                'key' => $type['key'],
                'label' => $type['label'],
                'mask' => $type['mask'],
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
