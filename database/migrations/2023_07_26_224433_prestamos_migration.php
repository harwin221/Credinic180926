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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id', false, true);
            $table->bigInteger('agente_id', false, true);
            $table->bigInteger('negocio_id', false, true)->nullable();
            $table->bigInteger('vendedor_id', false, true);
            $table->bigInteger('fiador_id', false, true);

            $table->integer('consecutivo',false,true);
            $table->boolean('represtamo')->default(false);
            $table->date('fecha_prestamo');
            $table->integer('desembolsado')->default(0);
            $table->bigInteger('user_desembolso',false,true)->comment('Quien realizó el desembolso');
            $table->date('fecha_desembolso')->comment('Fecha que se realizo el desembolso');

            $table->tinyInteger('moneda_prestamo')->default(0)->comment('0:Cordobas 1: Dolares');
            $table->decimal('monto_prestamo',10);
            $table->decimal('monto_financiado',10);
            $table->integer('forma_pago_tipo')->comment('1: Diario 2:Semanal 3:Quincenal 4:Mensual 5:Anual');
            $table->decimal('plazo_pago',4)->comment('Plazos en meses');
            $table->tinyInteger('dia_pago')->comment('Dia que realizará el pago, Lunes ...');
            $table->date('fecha_primer_pago')->comment('Fecha del primer pago');
            $table->decimal('monto_cuota',10);
            $table->decimal('tasa_prestamo',10);

            $table->decimal('interes_pagar',10);
            $table->decimal('interes_mes',10);
            $table->decimal('interes_total_pagar',10);

            $table->integer('dias_aplicar_mora')->default(0)->nullable();
            $table->integer('tipo_mora')->nullable()->comment('1: Valor Fijo, 2:Valor Porcentual');
            $table->decimal('monto_mora',10)->default(0)->nullable();


            $table->tinyInteger('estado')->comment('1:Activo 2:Pagado,3:Vencido,4:Anulado');


            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_anulado')->nullable();

            $table->integer('created_user_id');
            $table->integer('anulado_user_id')->nullable();
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
        Schema::dropIfExists('prestamos');
    }
};
