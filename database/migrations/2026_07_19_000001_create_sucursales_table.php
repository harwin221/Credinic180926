<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->tinyInteger('estado')->default(1)->comment('1:activo 2:inactivo');
            $table->integer('created_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
