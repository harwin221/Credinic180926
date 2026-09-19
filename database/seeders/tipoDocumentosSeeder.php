<?php

namespace Database\Seeders;

use App\Models\tipoDocumentosModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class tipoDocumentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        tipoDocumentosModel::create([
            'nombre'=>'COPIA CEDULA DE IDENTIDAD - FRONTAL',
            'tipo'=>'1',
            'tipo_documento'=>'1',
            'created_user_id'=>1,
        ]);

        tipoDocumentosModel::create([
            'nombre'=>'COPIA CEDULA DE IDENTIDAD - TRASERA',
            'tipo'=>'1',
            'tipo_documento'=>'1',
            'created_user_id'=>1,
        ]);
    }
}
