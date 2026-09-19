<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            permisosSeeder::class,
            rolesSeeder::class,
            userAdminSeeder::class,
            tipoDocumentosSeeder::class,
            negocioTipoSeeder::class,
            departamento_municipio_seeder::class
        ]);
    }
}
