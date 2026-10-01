<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos_agente_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prestamo_id');
            $table->unsignedBigInteger('agente_anterior_id');  // gestor que tenía el crédito antes
            $table->unsignedBigInteger('agente_nuevo_id');     // gestor al que se reasignó
            $table->unsignedBigInteger('reasignado_por');      // usuario que hizo la reasignación
            $table->decimal('saldo_al_reasignar', 12, 2)->default(0); // saldo pendiente en ese momento
            $table->date('fecha_reasignacion');                // fecha efectiva del cambio
            $table->text('motivo')->nullable();               // motivo de la reasignación
            $table->timestamps();

            $table->foreign('prestamo_id')->references('id')->on('prestamos')->onDelete('cascade');
            $table->index('prestamo_id');
            $table->index('agente_anterior_id');
            $table->index('agente_nuevo_id');
            $table->index('fecha_reasignacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos_agente_historial');
    }
};
