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
        Schema::create('departamento_municipio', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('departamento_id');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('departamento', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamento_municipio');
        Schema::dropIfExists('departamento');
    }
};
