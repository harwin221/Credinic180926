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
        Schema::create('prestamo_cuota_abono', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('abono_id');
            $table->unsignedBigInteger('prestamo_cuota_id');
            $table->decimal('monto_abono',10);
            $table->dateTime('fecha_abono');
            $table->integer('tipo_abono')->default(1)->comment('1:abono, 2:interes, 3:otro monto 4:mora');
            $table->integer('estado')->default(1)->comment('1:Activo, 2:Anulado');

            $table->decimal('total_capital',10);
            $table->decimal('total_interes',10);
            $table->decimal('total_mora',10);

            $table->integer('created_user_id');
            $table->integer('updated_user_id')->nullable();
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
        Schema::dropIfExists('prestamo_cuota_abono');
    }
};
