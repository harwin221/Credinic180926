<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getComplementoQuincenal(int $diaPreferido): int
    {
        if ($diaPreferido > 15) {
            return $diaPreferido - 15;
        }

        return $diaPreferido + 15;
    }

    protected function siguienteFechaCuotaQuincenal(Carbon $fechaActual, int $diaPreferido): Carbon
    {
        $diaPreferido = max(1, min(31, $diaPreferido));
        $diaComplemento = $this->getComplementoQuincenal($diaPreferido);
        $dias = [$diaPreferido, $diaComplemento];
        sort($dias);

        $diaActual = $fechaActual->day;
        foreach ($dias as $dia) {
            if ($dia > $diaActual) {
                $fecha = $fechaActual->copy();
                $fecha->day = min($dia, $fecha->daysInMonth);
                return $fecha;
            }
        }

        $fecha = $fechaActual->copy()->addMonth()->startOfMonth();
        $fecha->day = min($dias[0], $fecha->daysInMonth);
        return $fecha;
    }

    protected function obtenerSiguienteFechaProgramada(Carbon $fechaProgramada, ?Carbon $fechaReal, string $formaPago, int $banderaAumentar, int $diasPreferidos = 0): Carbon
    {
        if ($formaPago === "1") {
            // Si fechaReal es null, usar fechaProgramada
            $fechaBase = $fechaReal ?? $fechaProgramada;
            return $fechaBase->copy()->addDays($banderaAumentar);
        }

        if ($formaPago === "3" && $diasPreferidos > 0) {
            return $this->siguienteFechaCuotaQuincenal($fechaProgramada, $diasPreferidos);
        }

        return $fechaProgramada->copy()->addDays($banderaAumentar);
    }

    protected function ajustarFechaNoLaborable(Carbon $fecha, array $feriados): Carbon
    {
        $fechaAjustada = $fecha->copy();
        // Para SEMANAL, CATORCENAL, QUINCENAL: Solo ajusta DOMINGO o FERIADO
        // SÁBADO es día laborable válido
        while ($fechaAjustada->dayOfWeek === 0 || in_array($fechaAjustada->toDateString(), $feriados)) {
            $fechaAjustada->addDay();
        }
        return $fechaAjustada;
    }

    protected function ajustarFechaNoLaborableDiario(Carbon $fecha, array $feriados): Carbon
    {
        $fechaAjustada = $fecha->copy();
        
        // Para DIARIOS: Solo lunes a viernes son válidos (NO sábado, NO domingo, NO feriados)
        while (true) {
            // Si es sábado o domingo, avanzar
            if ($fechaAjustada->dayOfWeek === 0 || $fechaAjustada->dayOfWeek === 6) {
                $fechaAjustada->addDay();
                continue;
            }
            
            // Si es feriado, avanzar
            if (in_array($fechaAjustada->toDateString(), $feriados)) {
                $fechaAjustada->addDay();
                continue;
            }
            
            // Si llegamos aquí, es un día válido (lunes a viernes, no feriado)
            break;
        }
        
        return $fechaAjustada;
    }

    /**
     * Obtiene todos los feriados aplicables considerando recurrentes y no recurrentes
     * @param int|null $anio Año para el cual obtener feriados (null = año actual)
     * @return array Array de fechas en formato Y-m-d
     */
    protected function obtenerFeriadosAplicables(?int $anio = null): array
    {
        $anio = $anio ?? date('Y');
        $feriadosArray = [];
        
        // Obtener todos los feriados de la base de datos
        $feriados = \App\Models\feriados::all();
        
        foreach ($feriados as $feriado) {
            $fechaOriginal = Carbon::parse($feriado->fecha);
            
            if ($feriado->tipo == 1) {
                // RECURRENTE: Ajustar al año solicitado
                $fechaAjustada = Carbon::create($anio, $fechaOriginal->month, $fechaOriginal->day);
                $feriadosArray[] = $fechaAjustada->format('Y-m-d');
            } else {
                // NO RECURRENTE: Solo incluir si es del año solicitado
                if ($fechaOriginal->year == $anio) {
                    $feriadosArray[] = $fechaOriginal->format('Y-m-d');
                }
            }
        }
        
        // Eliminar duplicados y ordenar
        $feriadosArray = array_unique($feriadosArray);
        sort($feriadosArray);
        
        return $feriadosArray;
    }
}
