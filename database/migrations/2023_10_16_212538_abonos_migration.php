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
        Schema::create('abonos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prestamo_id');
            $table->dateTime('fecha_abono');
            $table->integer('estado')->default(1)->comment('1:Activo, 2:Anulado');
            $table->integer('user_anulado')->nullable();
            $table->dateTime('fecha_anulado')->nullable();
            $table->text('detalle_anulado')->nullable();

            $table->decimal('total_efectivo',10)->nullable();
            $table->decimal('total_tarjeta',10)->nullable();
            $table->decimal('total_cheque',10)->nullable();
            $table->decimal('total_transferencia',10)->nullable();

            $table->text('referencia_tarjeta')->nullable();
            $table->text('referencia_cheque')->nullable();
            $table->text('referencia_transferencia')->nullable();
            $table->decimal('tipo_cambio',10)->nullable();

            $table->integer('created_user_id');
            $table->integer('updated_user_id')->nullable();
            $table->integer('anulado_user_id')->nullable();
            $table->integer('deleted_user_id')->nullable();
            $table->softDeletesTz();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonos');
    }
};
