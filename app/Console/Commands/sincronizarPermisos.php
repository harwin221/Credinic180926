<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class sincronizarPermisos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sincronizar-permisos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea los permisos de reportes faltantes y los asigna a los roles existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1) Permisos de reportes que deben existir
        $permisosReportes = [
            'Ver Reportes',
            'Reporte Clientes (1)',
            'Reporte Cuotas (2)',
            'Reporte Recuperación (3)',
            'Reporte Cuotas Vencidas (4)',
            'Reporte Desembolsos Vencidos (5)',
            'Reporte Arqueo (6)',
            'Reporte Asignación Clientes (7)',
            'Reportes Cobros del Dia (8)',
            'Detalle Colocacion V2 (9)',
            'Cobranza (10)',
            'Saldo Cartera (11)',
            'Estado Clientes (12)',
            'Estado Cuenta Cliente (13)',
            'Plan de Pago (14)',
            'Antiguedad de Saldos (15)',
            'Cartera Diaria (16)',
        ];

        $creados = 0;
        foreach ($permisosReportes as $nombre) {
            $permiso = Permission::firstOrCreate(
                ['name' => $nombre, 'guard_name' => 'web'],
                ['description' => 'Reportes']
            );
            if ($permiso->wasRecentlyCreated) {
                $creados++;
                $this->info("+ Permiso creado: {$nombre}");
            }
        }
        $this->info("Permisos nuevos creados: {$creados}");

        // 2) El rol Administrador debe tener TODOS los permisos
        $admin = Role::where('name', 'Administrador')->first();
        if ($admin) {
            $admin->syncPermissions(Permission::all());
            $this->info('Administrador sincronizado con TODOS los permisos.');
        }

        // 3) A los demás roles que ya tenían reportes, agregarles los nuevos
        $todosReportes = Permission::where('description', 'Reportes')
            ->get();

        foreach (Role::where('name', '!=', 'Administrador')->get() as $rol) {
            $actuales = $rol->permissions->pluck('name')->toArray();
            // Solo los roles que ya tenían algún reporte reciben los reportes nuevos
            if (in_array('Reporte Clientes (1)', $actuales)) {
                foreach ($todosReportes as $p) {
                    if (!in_array($p->name, $actuales)) {
                        $rol->givePermissionTo($p->name);
                        $this->info("  [{$rol->name}] + {$p->name}");
                    }
                }
            }
        }

        $this->info('Sincronización completada.');
        return 0;
    }
}
