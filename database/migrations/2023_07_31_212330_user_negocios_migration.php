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
        Schema::create('user_negocios', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id', false, true);
            $table->text('nombre');
            $table->text('direccion');
            $table->integer('municipio_id')->default(155);//NINGUNO
//            $table->json('tipo_negocio_id')->nullable();
            $table->string('punto_geografico')->nullable();
            $table->string('telefono_negocio')->nullable();
            $table->text('comentarios')->nullable();

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
        Schema::dropIfExists('negocio_tipos');
    }
};
