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
        Schema::create('user_negocio_documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_negocio_id');
//            $table->string('nombre',255);
            $table->string('url',255);

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
        Schema::dropIfExists('user_negocio_documentos');
    }
};
