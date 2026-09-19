<?php

namespace Database\Seeders;

use App\Models\negocioTiposModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class negocioTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        negocioTiposModel::create([
            'nombre'=>'Sin Asignar',
            'created_user_id'=>1,
        ]);
    }
}
