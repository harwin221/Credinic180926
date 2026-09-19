<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Migración: Permisos faltantes v2.2.0
 *
 * Crea los permisos que el código usa pero no existían en la BD,
 * los agrupa correctamente por 'description', y los asigna a los roles correspondientes.
 *
 * Permisos corregidos:
 *   - Editar Desembolsos     → movido al grupo [Prestamos]
 *
 * Permisos nuevos:
 *   - Eliminar Desembolsos   → grupo [Prestamos]   → Administrador
 *   - Eliminar Abonos        → grupo [Abonos]       → Administrador
 *   - Ver Sucursales         → grupo [Configuracion]→ Administrador, Gerencia, Desembolsador, Financiero
 *   - Crear Sucursales       → grupo [Configuracion]→ Administrador
 *   - Editar Sucursales      → grupo [Configuracion]→ Administrador
 *   - Eliminar Sucursales    → grupo [Configuracion]→ Administrador
 */
return new class extends Migration
{
    public function up(): void
    {
        // Reset de caché antes de operar
        app()['cache']->forget('spatie.permission.cache');

        // ── 1. Corregir description de "Editar Desembolsos" ──────────────────────
        $editarDesembolsos = Permission::where('name', 'Editar Desembolsos')->first();
        if ($editarDesembolsos) {
            $editarDesembolsos->description = 'Prestamos';
            $editarDesembolsos->save();
        }

        // ── 2. Definir permisos a crear ──────────────────────────────────────────
        $nuevosPermisos = [
            // Grupo Prestamos
            ['name' => 'Eliminar Desembolsos', 'description' => 'Prestamos',
             'roles' => ['Administrador']],

            // Grupo Abonos
            ['name' => 'Eliminar Abonos',      'description' => 'Abonos',
             'roles' => ['Administrador']],

            // Grupo Configuracion — Sucursales
            ['name' => 'Ver Sucursales',        'description' => 'Configuracion',
             'roles' => ['Administrador', 'Gerencia', 'Desembolsador', 'Financiero']],

            ['name' => 'Crear Sucursales',      'description' => 'Configuracion',
             'roles' => ['Administrador']],

            ['name' => 'Editar Sucursales',     'description' => 'Configuracion',
             'roles' => ['Administrador']],

            ['name' => 'Eliminar Sucursales',   'description' => 'Configuracion',
             'roles' => ['Administrador']],
        ];

        // ── 3. Crear y asignar ────────────────────────────────────────────────────
        foreach ($nuevosPermisos as $data) {
            $permiso = Permission::firstOrCreate(
                ['name'       => $data['name'], 'guard_name' => 'web'],
                ['description'=> $data['description']]
            );

            // Asegurar que el description sea el correcto aunque ya existiera
            if ($permiso->description !== $data['description']) {
                $permiso->description = $data['description'];
                $permiso->save();
            }

            foreach ($data['roles'] as $rolNombre) {
                $rol = Role::where('name', $rolNombre)->first();
                if ($rol && !$rol->hasPermissionTo($data['name'])) {
                    $rol->givePermissionTo($permiso);
                }
            }
        }

        // Reset de caché al finalizar
        app()['cache']->forget('spatie.permission.cache');
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $permisosCreados = [
            'Eliminar Desembolsos',
            'Eliminar Abonos',
            'Ver Sucursales',
            'Crear Sucursales',
            'Editar Sucursales',
            'Eliminar Sucursales',
        ];

        foreach ($permisosCreados as $nombre) {
            Permission::where('name', $nombre)->delete();
        }

        // Revertir description de Editar Desembolsos
        $p = Permission::where('name', 'Editar Desembolsos')->first();
        if ($p) {
            $p->description = 'Permite editar préstamos desembolsados';
            $p->save();
        }

        app()['cache']->forget('spatie.permission.cache');
    }
};
