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
        Schema::create('prestamo_coutas', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('prestamo_id',false,true);
            $table->integer('numero_cuota',false,true);
            $table->decimal('monto_cuota',10);
            $table->decimal('monto_interes',10);
            $table->date('fecha_cuota');
            $table->dateTime('fecha_pagado')->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('forma_pago')->comment('1:Efectivo,2:Tarjeta,3:Transferencia,4:Cheque');
            $table->integer('pago_interes')->default(0)->comment('1: Es abono de intereses');
            $table->tinyInteger('estado')->default(1)->comment('1:pendiente,2:vencido,3:pagado');
            $table->decimal('monto_mora',10)->nullable();

            $table->integer('created_user_id');
            $table->integer('updated_user_id')->nullable();
            $table->integer('deleted_user_id')->nullable();
            $table->timestamps();
            $table->softDeletesTz();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamo_coutas');
    }
};
