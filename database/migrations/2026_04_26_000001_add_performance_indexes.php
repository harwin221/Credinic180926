<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para agregar índices que mejoran el rendimiento de los reportes
 * 
 * IMPORTANTE: Ejecutar con: php artisan migrate
 * 
 * Estos índices optimizan las consultas más frecuentes en los reportes:
 * - Búsquedas por prestamo_id
 * - Filtros por fecha
 * - Agrupaciones por agente/cobrador
 * - Joins entre tablas relacionadas
 */
class AddPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prestamos', function (Blueprint $table) {
            // Índices para filtros comunes en reportes
            if (!$this->indexExists('prestamos', 'idx_prestamos_desembolsado_estado')) {
                $table->index(['desembolsado', 'estado'], 'idx_prestamos_desembolsado_estado');
            }
            if (!$this->indexExists('prestamos', 'idx_prestamos_agente_id')) {
                $table->index('agente_id', 'idx_prestamos_agente_id');
            }
            if (!$this->indexExists('prestamos', 'idx_prestamos_user_id')) {
                $table->index('user_id', 'idx_prestamos_user_id');
            }
            if (!$this->indexExists('prestamos', 'idx_prestamos_fecha_desembolso')) {
                $table->index('fecha_desembolso', 'idx_prestamos_fecha_desembolso');
            }
            if (!$this->indexExists('prestamos', 'idx_prestamos_forma_pago_tipo')) {
                $table->index('forma_pago_tipo', 'idx_prestamos_forma_pago_tipo');
            }
        });

        Schema::table('prestamo_coutas', function (Blueprint $table) {
            // Índices para joins y filtros de cuotas
            if (!$this->indexExists('prestamo_coutas', 'idx_cuotas_prestamo_fecha')) {
                $table->index(['prestamo_id', 'fecha_cuota'], 'idx_cuotas_prestamo_fecha');
            }
            if (!$this->indexExists('prestamo_coutas', 'idx_cuotas_estado')) {
                $table->index('estado', 'idx_cuotas_estado');
            }
            if (!$this->indexExists('prestamo_coutas', 'idx_cuotas_fecha_cuota')) {
                $table->index('fecha_cuota', 'idx_cuotas_fecha_cuota');
            }
        });

        Schema::table('prestamo_cuota_abono', function (Blueprint $table) {
            // Índices para cálculos de abonos
            if (!$this->indexExists('prestamo_cuota_abono', 'idx_abonos_cuota_fecha')) {
                $table->index(['prestamo_cuota_id', 'fecha_abono'], 'idx_abonos_cuota_fecha');
            }
            if (!$this->indexExists('prestamo_cuota_abono', 'idx_abonos_estado')) {
                $table->index('estado', 'idx_abonos_estado');
            }
            if (!$this->indexExists('prestamo_cuota_abono', 'idx_abonos_fecha')) {
                $table->index('fecha_abono', 'idx_abonos_fecha');
            }
        });

        Schema::table('abonos', function (Blueprint $table) {
            // Índices para reportes de abonos
            if (!$this->indexExists('abonos', 'idx_abonos_prestamo_fecha')) {
                $table->index(['prestamo_id', 'fecha_abono'], 'idx_abonos_prestamo_fecha');
            }
            if (!$this->indexExists('abonos', 'idx_abonos_created_user')) {
                $table->index('created_user_id', 'idx_abonos_created_user');
            }
            if (!$this->indexExists('abonos', 'idx_abonos_estado')) {
                $table->index('estado', 'idx_abonos_estado');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Índices para filtros de usuarios
            if (!$this->indexExists('users', 'idx_users_tipo')) {
                $table->index('tipo_usuario', 'idx_users_tipo');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropIndex('idx_prestamos_desembolsado_estado');
            $table->dropIndex('idx_prestamos_agente_id');
            $table->dropIndex('idx_prestamos_user_id');
            $table->dropIndex('idx_prestamos_fecha_desembolso');
            $table->dropIndex('idx_prestamos_forma_pago_tipo');
        });

        Schema::table('prestamo_coutas', function (Blueprint $table) {
            $table->dropIndex('idx_cuotas_prestamo_fecha');
            $table->dropIndex('idx_cuotas_estado');
            $table->dropIndex('idx_cuotas_fecha_cuota');
        });

        Schema::table('prestamo_cuota_abono', function (Blueprint $table) {
            $table->dropIndex('idx_abonos_cuota_fecha');
            $table->dropIndex('idx_abonos_estado');
            $table->dropIndex('idx_abonos_fecha');
        });

        Schema::table('abonos', function (Blueprint $table) {
            $table->dropIndex('idx_abonos_prestamo_fecha');
            $table->dropIndex('idx_abonos_created_user');
            $table->dropIndex('idx_abonos_estado');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_tipo');
        });
    }

    /**
     * Verifica si un índice ya existe
     */
    private function indexExists($table, $index)
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);
        
        return array_key_exists($index, $indexes);
    }
}
