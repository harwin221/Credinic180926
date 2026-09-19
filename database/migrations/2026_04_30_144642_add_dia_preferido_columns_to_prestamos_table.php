<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            if (!Schema::hasColumn('prestamos', 'dia_pago_preferido')) {
                $table->integer('dia_pago_preferido')->nullable()->after('dias_pago')
                    ->comment('Día del mes preferido para préstamos quincenales (1-31)');
            }
            
            if (!Schema::hasColumn('prestamos', 'dia_semana_preferido')) {
                $table->integer('dia_semana_preferido')->nullable()->after('dia_pago_preferido')
                    ->comment('Día de la semana preferido: 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn(['dia_pago_preferido', 'dia_semana_preferido']);
        });
    }
};
