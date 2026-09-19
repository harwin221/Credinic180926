<?php

namespace App\Exports;

use App\Models\prestamosModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class detalleColocacionV2Export implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    private $datos;
    private $mesSeleccionado;
    private $anyoSeleccionado;
    private $meses;
private $mesesArr;
    public function __construct($dt, $mes, $anyo)
    {
        $this->datos = $dt;
        $this->mesSeleccionado = $mes;
        $this->anyoSeleccionado = $anyo;

        $fechaInicio = Carbon::parse($this->anyoSeleccionado.'-'.$this->mesSeleccionado.'-01');
        $fechaFin = Carbon::parse(Carbon::now());

        $mesesEnRango = $fechaInicio->diffInMonths($fechaFin);

        $meses = [];
        for ($i = 0; $i <= $mesesEnRango; $i++) {
            $meses[] = $fechaInicio->copy()->addMonths($i)->format('m Y');
        }
        $this->meses = $meses;

        $this->mesesArr = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];
    }

    public function collection()
    {
        $prestamoIds = $this->datos->pluck('id')->toArray();
        
        // OPTIMIZACIÓN: Precalcular todos los abonos por mes en una sola consulta
        $abonosPorPrestamoMes = \DB::table('prestamo_coutas as PC')
            ->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'PC.id')
            ->whereIn('PC.prestamo_id', $prestamoIds)
            ->where('PCA.estado', 1)
            ->select(
                'PC.prestamo_id',
                \DB::raw('MONTH(PCA.fecha_abono) as mes'),
                \DB::raw('YEAR(PCA.fecha_abono) as anio'),
                \DB::raw('SUM(PCA.monto_abono) as total_abono')
            )
            ->groupBy('PC.prestamo_id', \DB::raw('MONTH(PCA.fecha_abono)'), \DB::raw('YEAR(PCA.fecha_abono)'))
            ->get()
            ->groupBy('prestamo_id');
        
        // OPTIMIZACIÓN: Una sola consulta para obtener la última cuota
        $ultimasCuotas = \DB::table('prestamo_coutas')
            ->whereIn('prestamo_id', $prestamoIds)
            ->select(
                'prestamo_id',
                \DB::raw('MAX(fecha_cuota) as ultima_fecha')
            )
            ->groupBy('prestamo_id')
            ->get()
            ->keyBy('prestamo_id');
        
        // OPTIMIZACIÓN: Una sola consulta para obtener el último abono
        $ultimosAbonos = \DB::table('abonos')
            ->whereIn('prestamo_id', $prestamoIds)
            ->where('estado', 1)
            ->select(
                'prestamo_id',
                \DB::raw('MAX(fecha_abono) as ultima_fecha_abono')
            )
            ->groupBy('prestamo_id')
            ->get()
            ->keyBy('prestamo_id');

        $data = [];

        foreach ($this->datos as $dt) {
            $aux = [];
            $aux[] = $dt->cliente->full_name;
            $aux[] = $dt->agente->full_name;
            $aux[] = fecha_d_m_Y($dt->fecha_desembolso);
            
            // Obtener última cuota precalculada
            $ultimaCuota = $ultimasCuotas->get($dt->id);
            $aux[] = $ultimaCuota && $ultimaCuota->ultima_fecha ? fecha_d_m_Y($ultimaCuota->ultima_fecha) : "-";
            
            $aux[] = $dt->forma_pago;
            $aux[] = $dt->plazo_pago;
            $aux[] = $dt->moneda;
            $aux[] = $dt->monto_prestamo;
            $aux[] = $dt->interes_total_pagar;

            // Obtener abonos por mes precalculados
            $abonosPrestamo = $abonosPorPrestamoMes->get($dt->id, collect());
            
            foreach ($this->meses as $mes) {
                $mesActual = explode(' ', $mes)[0];
                $anyoActual = explode(' ', $mes)[1];
                
                $abonoMes = $abonosPrestamo->first(function($abono) use ($mesActual, $anyoActual) {
                    return str_pad($abono->mes, 2, '0', STR_PAD_LEFT) == $mesActual && $abono->anio == $anyoActual;
                });
                
                $aux[] = strval($abonoMes ? $abonoMes->total_abono : 0);
            }
            
            $aux[] = $dt->monto_financiado;
            $aux[] = $dt->pendiente_abono;

            if(count($dt->cuotas_vencidas))
                $aux[] = "Cuotas Pendientes";
            else
                $aux[] = "Al Día";
            $aux[] = ($dt->cliente->prestamos->where('desembolsado',1)->count()==1)?'Nuevo Cliente':'Cliente Recurrente';
            
            if($dt->estado === 2) {
                $ultimoAbono = $ultimosAbonos->get($dt->id);
                $aux[] = $ultimoAbono && $ultimoAbono->ultima_fecha_abono ? fecha_d_m_Y($ultimoAbono->ultima_fecha_abono) : "-";
            }

            $data[]=$aux;
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        $arr = [
            'Cliente',
            'Cobrador',
            'Fecha Colocación',
            'Fecha Vencimiento',
            'Forma Pago',
            'Plazo (Meses)',
            'Moneda',
            'Capital Colocado',
            'Interes Total Ganar',
        ];

        foreach ($this->meses as $m) {
            $mesActual = explode(' ', $m)[0];
            $anyoActual = explode(' ', $m)[1];
            $arr[] = strval($this->mesesArr[$mesActual]." ".$anyoActual);
        }
        $arr[] = 'Total Recuperar';
        $arr[] = 'Saldo';
        $arr[] = 'Pendiente';
        $arr[] = 'Tipo Cliente';
        $arr[] = 'Fecha Último Abono';

        return $arr;
    }
}
