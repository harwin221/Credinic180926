<?php

namespace App\Exports;

use App\Models\abonosModel;
use App\Models\prestamoCuotaAbonoModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class detalleRecuperacionDetalladoExport implements FromCollection,ShouldAutoSize,WithHeadings
{
    private $data;
    private $desde;
    private $hasta;
    private $cobrador;
    public function __construct($dt,$desde,$hasta,$cobrador = null)
    {
        $this->data = $dt;
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->cobrador = $cobrador;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $cobrador = $this->cobrador;
        $datos = [];
        foreach ($this->data as $fecha => $dt){
            $totalRecuperado = abonosModel::whereDate('fecha_abono', $fecha)
                ->where('estado', 1)
                ->when($cobrador, function ($query) use ($cobrador) {
                    $query->where('created_user_id', decode($cobrador));
                })->get();

            $totalInteresRecuperadoFecha = prestamoCuotaAbonoModel::whereIn('abono_id', $totalRecuperado->pluck('id'))
                ->sum('total_interes');
            $totalCapitalRecuperadoFecha = prestamoCuotaAbonoModel::whereIn('abono_id', $totalRecuperado->pluck('id'))
                ->sum('total_capital');


            $aux = [];
            $aux[] = $fecha;
            $aux[] = '';
            $aux[] = $totalCapitalRecuperadoFecha;
            $aux[] = $totalInteresRecuperadoFecha;
            $aux[] = $totalRecuperado->sum('total_efectivo')+$totalRecuperado->sum('total_tarjeta')+$totalRecuperado->sum('total_cheque')+$totalRecuperado->sum('total_transferencia');
            $datos[] = $aux;

            foreach ($dt as $detalle){
                $aux = [];
                $aux[] = '';
                $aux[] = "(".$detalle->prestamo->consecutivo.") ".$detalle->prestamo->cliente->full_name;
                $aux[] = $detalle->abono_detalle->sum('total_capital');
                $aux[] = $detalle->abono_detalle->sum('total_interes');
                $aux[] = $detalle->abono_detalle->sum('total_capital') + $detalle->abono_detalle->sum('total_interes');
                $aux[] = strval($detalle->prestamo->total_pendiente_interes + $detalle->prestamo->total_pendiente_capital);
                $aux[] = $detalle->tipo;
                $datos[] = $aux;
            }
        }
         return new Collection($datos);
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Cliente',
            'Principal',
            'Interes',
            'Total Recuperado',
            'Total Pendiente',
            'Tipo',
        ];
    }
}
