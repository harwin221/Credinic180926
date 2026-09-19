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
        Schema::create('negocio_tipos', function (Blueprint $table) {
            $table->id();
            $table->text('nombre');

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
        Schema::dropIfExists('user_negocios');
    }
};
