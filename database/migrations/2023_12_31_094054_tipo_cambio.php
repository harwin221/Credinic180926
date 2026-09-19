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
          Schema::create('tipo_cambio', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->decimal('valor',10);
            $table->integer('created_user_id');
            $table->integer('updated_user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_cambio');
    }
};
