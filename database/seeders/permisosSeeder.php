<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class permisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //////////PERMISOS//////////////
        Permission::create([
            'name' => 'Ver Roles',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'Crear Roles',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'Editar Roles',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'Eliminar Roles',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Asignar Roles',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Ver Permisos',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Crear Permisos',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Editar Permisos',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Eliminar Permisos',
            'description' => 'Roles',
            'guard_name' => 'web',
        ]);

        ////////////////////////////////////

        //DOCUMENTOS
        Permission::create([
            'name' => 'Ver Documentos',
            'description' => 'Documentos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Crear Documentos',
            'description' => 'Documentos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Editar Documentos',
            'description' => 'Documentos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Eliminar Documentos',
            'description' => 'Documentos',
            'guard_name' => 'web',
        ]);
        //////////////////////

        //ADMINISTRATIVOS
        Permission::create([
            'name' => 'Ver Usuarios',
            'description' => 'Usuarios',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Ver Administrativos',
            'description' => 'Usuarios',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Ver Clientes',
            'description' => 'Usuarios',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Ver Agentes',
            'description' => 'Usuarios',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Crear Administrativos',
            'description' => 'Administrativos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Editar Administrativos',
            'description' => 'Administrativos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Eliminar Administrativos',
            'description' => 'Administrativos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Cambiar Contraseña Administrativos',
            'description' => 'Administrativos',
            'guard_name' => 'web',
        ]);

        /////////

        ///CLIENTES

        Permission::create([
            'name' => 'Crear Clientes',
            'description' => 'Clientes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Editar Clientes',
            'description' => 'Clientes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Eliminar Clientes',
            'description' => 'Clientes',
            'guard_name' => 'web',
        ]);

        //AGENTES

        Permission::create([
            'name' => 'Crear Agentes',
            'description' => 'Agentes',
            'guard_name' => 'web',
        ]);


        Permission::create([
            'name' => 'Editar Agentes',
            'description' => 'Agentes',
            'guard_name' => 'web',
        ]);


        Permission::create([
            'name' => 'Eliminar Agentes',
            'description' => 'Agentes',
            'guard_name' => 'web',
        ]);

        //////

        //PRESTAMOS

        Permission::create([
            'name' => 'Ver Desembolsos',
            'description' => 'Prestamos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Crear Desembolsos',
            'description' => 'Prestamos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Realizar Représtamo',
            'description' => 'Prestamos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reasignar Vendedor',
            'description' => 'Prestamos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reasignar Cobrador',
            'description' => 'Prestamos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Anular Desembolsos',
            'description' => 'Prestamos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Editar Desembolsos',
            'description' => 'Prestamos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Eliminar Desembolsos',
            'description' => 'Prestamos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Crear Negocios',
            'description' => 'Negocios',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Eliminar Negocios',
            'description' => 'Negocios',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Editar Negocios',
            'description' => 'Negocios',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Crear Fiadores',
            'description' => 'Fiadores',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Editar Fiadores',
            'description' => 'Fiadores',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Eliminar Fiadores',
            'description' => 'Fiadores',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Crear Abonos',
            'description' => 'Abonos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Ver Abonos',
            'description' => 'Abonos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Anular Abonos',
            'description' => 'Abonos',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reimprimir Recibos',
            'description' => 'Abonos',
            'guard_name' => 'web',
        ]);

        // SUCURSALES
        Permission::create([
            'name' => 'Ver Sucursales',
            'description' => 'Sucursales',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Crear Sucursales',
            'description' => 'Sucursales',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Editar Sucursales',
            'description' => 'Sucursales',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Eliminar Sucursales',
            'description' => 'Sucursales',
            'guard_name' => 'web',
        ]);
        //////////////////////

        Permission::create([
            'name' => 'Ver Reportes',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reporte Clientes (1)',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reporte Cuotas (2)',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reporte Recuperación (3)',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reporte Cuotas Vencidas (4)',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reporte Desembolsos Vencidos (5)',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reporte Arqueo (6)',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reporte Asignación Clientes (7)',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'Reportes Cobros del Dia (8)',
            'description' => 'Reportes',
            'guard_name' => 'web',
        ]);


        //
    }
}
