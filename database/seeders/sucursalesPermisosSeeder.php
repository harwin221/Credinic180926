<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class sucursalesPermisosSeeder extends Seeder
{
    /**
     * Inserta los permisos de Sucursales si no existen.
     */
    public function run(): void
    {
        $permisos = [
            'Ver Sucursales',
            'Crear Sucursales',
            'Editar Sucursales',
            'Eliminar Sucursales',
        ];

        foreach ($permisos as $nombre) {
            Permission::firstOrCreate(
                ['name' => $nombre, 'guard_name' => 'web'],
                ['description' => 'Sucursales']
            );
        }

        $this->command->info('Permisos de Sucursales creados correctamente.');
    }
}
