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
        Schema::create('arqueo', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_arqueo');
            $table->integer('estado')->comment('1:Activo (Aún puede modificarse),2:Finalizado (No se puede modificar)');

            $table->decimal('moneda_c_050',10)->default(0.00)->nullable();
            $table->decimal('moneda_c_1',10)->default(0.00)->nullable();
            $table->decimal('moneda_c_5',10)->default(0.00)->nullable();
            $table->decimal('billete_c_5',10)->default(0.00)->nullable();
            $table->decimal('billete_c_10',10)->default(0.00)->nullable();
            $table->decimal('billete_c_20',10)->default(0.00)->nullable();
            $table->decimal('billete_c_50',10)->default(0.00)->nullable();
            $table->decimal('billete_c_100',10)->default(0.00)->nullable();
            $table->decimal('billete_c_200',10)->default(0.00)->nullable();
            $table->decimal('billete_c_500',10)->default(0.00)->nullable();
            $table->decimal('billete_c_1000',10)->default(0.00)->nullable();

            $table->decimal('billete_d_1',10)->default(0.00)->nullable();
            $table->decimal('billete_d_2',10)->default(0.00)->nullable();
            $table->decimal('billete_d_5',10)->default(0.00)->nullable();
            $table->decimal('billete_d_10',10)->default(0.00)->nullable();
            $table->decimal('billete_d_20',10)->default(0.00)->nullable();
            $table->decimal('billete_d_50',10)->default(0.00)->nullable();
            $table->decimal('billete_d_100',10)->default(0.00)->nullable();

            $table->decimal('total_cordoba',10)->default(0.00)->nullable();
            $table->decimal('total_dolar',10)->default(0.00)->nullable();

            $table->integer('created_user_id');
            $table->integer('updated_user_id')->nullable();
            $table->softDeletesTz();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arqueo');
    }
};
