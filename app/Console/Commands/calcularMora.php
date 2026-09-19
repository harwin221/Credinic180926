<?php

namespace App\Console\Commands;

use App\Models\prestamoCuotasModel;
use Illuminate\Console\Command;

class calcularMora extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calcular-mora';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aplicar Mora';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cuotasVencidas = prestamoCuotasModel::where('fecha_cuota','<',date('Y-m-d'))->where('estado',1)->get();
        if (count($cuotasVencidas)) {
            foreach ($cuotasVencidas as $cuota) {
                if ($cuota->prestamo->monto_mora > 0) {
                    if ($cuota->prestamo->tipo_mora == 2)//porcentaje
                    {
                        $porcent = $cuota->prestamo->monto_mora / 100;
                        $montoAplicar = $cuota->monto_pendiente_cuota * $porcent;
                        $cuota->monto_mora = $montoAplicar;
                    } else {
                        $montoAplicar = $cuota->prestamo->monto_mora;
                        $cuota->monto_mora += $montoAplicar;
                    }
                    $cuota->save();
                }
            }
        }
    }
}
