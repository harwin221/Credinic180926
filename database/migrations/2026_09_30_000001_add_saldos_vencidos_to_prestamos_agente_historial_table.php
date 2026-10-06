<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('prestamos_agente_historial', 'saldo_vencido_al_reasignar')) {
            Schema::table('prestamos_agente_historial', function (Blueprint $table) {
                $table->decimal('saldo_vencido_al_reasignar', 12, 2)->default(0)
                    ->after('saldo_al_reasignar')
                    ->comment('Saldo vencido (plazo ya terminado) al momento de la reasignacion');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('prestamos_agente_historial', 'saldo_vencido_al_reasignar')) {
            Schema::table('prestamos_agente_historial', function (Blueprint $table) {
                $table->dropColumn('saldo_vencido_al_reasignar');
            });
        }
    }
};
