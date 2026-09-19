<?php

namespace App\Exports;

use App\Models\prestamosModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class antiguedadSaldosExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    private $datos;
    public function __construct($data)
    {
        $this->datos = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $fechaHoy = Carbon::now()->toDateString();
        $prestamoIds = $this->datos->pluck('id')->toArray();
        
        // OPTIMIZACIÓN: Una sola consulta para obtener todos los abonos de capital e interés
        $abonosPorPrestamo = \DB::table('prestamo_coutas as PC')
            ->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'PC.id')
            ->whereIn('PC.prestamo_id', $prestamoIds)
            ->whereDate('PCA.fecha_abono', '<=', $fechaHoy)
            ->where('PCA.estado', 1)
            ->select(
                'PC.prestamo_id',
                \DB::raw('SUM(PCA.total_capital) as total_capital'),
                \DB::raw('SUM(PCA.total_interes) as total_interes')
            )
            ->groupBy('PC.prestamo_id')
            ->get()
            ->keyBy('prestamo_id');
        
        // OPTIMIZACIÓN: Una sola consulta para obtener la última cuota de cada préstamo
        $ultimasCuotas = \DB::table('prestamo_coutas')
            ->whereIn('prestamo_id', $prestamoIds)
            ->select(
                'prestamo_id',
                \DB::raw('MAX(fecha_cuota) as ultima_fecha')
            )
            ->groupBy('prestamo_id')
            ->get()
            ->keyBy('prestamo_id');

        $data = [];
        foreach ($this->datos as $dt) {
            $aux = [];

            // Obtener abonos precalculados
            $abonos = $abonosPorPrestamo->get($dt->id);
            $total_capital_abonado = $abonos ? $abonos->total_capital : 0;
            $total_interes_abonado = $abonos ? $abonos->total_interes : 0;

            $aux[] = $dt->cliente->cedula;
            $aux[] = $dt->cliente->full_name;
            $aux[] = $dt->consecutivo;
            $aux[] = $dt->agente->full_name;
            $aux[] = $dt->monto_prestamo;
            $aux[] = $dt->monto_financiado;
            $aux[] = strval($dt->monto_prestamo - $total_capital_abonado);
            $aux[] = strval($dt->interes_total_pagar - $total_interes_abonado);
            $aux[] = strval($dt->pendiente_abono);
            $aux[] = $dt->plazo_pago;
            $aux[] = $dt->forma_pago;
            $aux[] = 20 * $dt->plazo_pago;
            $aux[] = $dt->fecha_desembolso;

            // Obtener última cuota precalculada
            $ultimaCuota = $ultimasCuotas->get($dt->id);
            
            if($ultimaCuota && $ultimaCuota->ultima_fecha){
                $ultimaFecha = fecha_d_m_Y($ultimaCuota->ultima_fecha);
                $diasVencidos = Carbon::createFromFormat('d-m-Y', $ultimaFecha)->diffInDays(Carbon::now(),false);
                $aux[] = $ultimaFecha;
                $aux[] = $diasVencidos;
                
                if ($diasVencidos < 0) {
                    $aux[] = strval($dt->pendiente_abono);
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = '';
                } elseif ($diasVencidos <= 30) {
                    $aux[] = '';
                    $aux[] = strval($dt->pendiente_abono);
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = '';
                } elseif ($diasVencidos <= 60) {
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = strval($dt->pendiente_abono);
                    $aux[] = '';
                    $aux[] = '';
                } elseif ($diasVencidos <= 90) {
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = strval($dt->pendiente_abono);
                    $aux[] = '';
                } else {
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = '';
                    $aux[] = strval($dt->pendiente_abono);
                }
            }
            else{
                $aux[] = 'N-D';
                $aux[] = 'N-D';
                $aux[] = '';
                $aux[] = '';
                $aux[] = '';
                $aux[] = '';
                $aux[] = '';
            }

            $data[]=$aux;
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        return [
          'Cédula',
          'Cliente',
          'Consecutivo',
          'Cobrador',
          'Monto Soliciado',
          'Monto Financiado',
          'Pendiente Capital',
          'Pendiente Interes',
          'Saldo Actual',
          'Plazo en Meses',
          'Forma de Pago',
          'Forma de Pago DIAS',
          'Fecha Desembolso',
          'Fecha Vencimiento',
          'Dias Vencidos',
          'Crédito Corriente',
          '0 - 30 días',
          '31 - 60 días',
          '61 - 90 días',
          '90 días a más',
        ];
    }
}
