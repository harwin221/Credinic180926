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
//        Schema::create('solicitud_prestamo', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('user_id');
//            $table->integer('moneda')->default(1);
//            $table->decimal('monto_solicitado',10);
//            $table->decimal('plazo_solicitado',3);
//            $table->integer('forma_pago_solicitada');
//            $table->decimal('tasa_propuesta',10);
//            $table->date('fecha_primer_pago',10);
//            $table->integer('estado')->comment('1: Activo,2:Rechazado,2:Aprobado');
//            $table->text('observaciones')->nullable();
//            $table->integer('created_user_id');
//            $table->integer('updated_user_id')->nullable();
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
//        Schema::dropIfExists('solicitud_prestamo');
    }
};
