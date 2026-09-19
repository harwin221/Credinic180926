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
        Schema::create('tipo_documentos', function (Blueprint $table) {
            $table->id();
            $table->text('nombre');
            $table->tinyInteger('tipo')->default('1')->comment('1:requerido 2:opcional');
            $table->integer('tipo_documento')->comment('1: imagen, 2:documento')->default(1);
            $table->integer('created_user_id');
            $table->integer('updated_user_id')->nullable();
            $table->timestamps();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_documentos');
    }
};
