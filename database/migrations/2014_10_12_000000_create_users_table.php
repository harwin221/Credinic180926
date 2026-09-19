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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username',75)->nullable();
            $table->string('nombres',255);
            $table->string('apellidos',255);
            $table->string('telefono1',100)->nullable();
            $table->string('telefono2',100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('cedula');
            $table->string('foto')->default('no-photo.jpg');
            $table->integer('sexo')->default('0')->comment('0:Masculino,1:Femenino');
            $table->integer('estado_civil')->default('0')->comment('0:Soltero,1:casado,2:union libre,3:viudo(a),4:divorciado');
            $table->integer('dep_mun')->default(155);//ninguno
            $table->tinyInteger('estado')->default(1)->comment('1:activo 2:inactivo,3:pendiente,4:aprobado,5:rechazado');//activo
            $table->tinyInteger('tipo_usuario')->default(2)->comment('1:administrador 2:administrativo 3:clientes 4:agentes 5:fiador,6:Nuevos');//administrativo
            $table->string('email')->unique();
            $table->integer('created_user_id');
            $table->integer('updated_user_id')->nullable();
            $table->integer('deleted_user_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
