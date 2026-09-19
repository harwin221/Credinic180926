<?php

namespace App\Exports;

use App\Models\abonosModel;
use App\Models\prestamoCuotaAbonoModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class detalleRecuperacionV3Export implements FromCollection, ShouldAutoSize, WithHeadings
{
    private $datos;

    public function __construct($dt)
    {
        $this->datos = $dt;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];
        foreach ($this->datos as $dt) {

            $ultimaFecha = fecha_d_m_Y($dt->prestamo_cuota->prestamo->cuotas()->orderBy('id', 'desc')->first()->fecha_cuota);

            $aux = [];
            $aux[] = $dt->prestamo_cuota->prestamo->consecutivo;
            $aux[] = $dt->prestamo_cuota->prestamo->cliente->cedula;
            $aux[] = $dt->prestamo_cuota->prestamo->cliente->full_name;
            $aux[] = $dt->prestamo_cuota->prestamo->forma_pago;
            $aux[] = fecha_d_m_Y_h_i($dt->abono->fecha_abono);
            $aux[] = $dt->prestamo_cuota->prestamo->agente->full_name;
            $aux[] = $dt->total_capital;
            $aux[] = $dt->total_interes;
            $aux[] = $dt->monto_abono;
            $aux[] = $dt->abono->tipo;
            $aux[] = $dt->prestamo_cuota->numero_cuota;
            $aux[] = date('d-m-Y', strtotime($dt->prestamo_cuota->fecha_cuota));

            $fechaAbono = $dt->abono->fecha_abono;
            $fechaCuota = $dt->prestamo_cuota->fecha_cuota;

            if (Carbon::create($fechaAbono)->toDateString() === Carbon::create($fechaCuota)->toDateString()) {
                $aux[] = "Cuota del Día";
            } elseif (Carbon::create($ultimaFecha)->toDateString() < Carbon::create($fechaAbono)->toDateString()) {
                $aux[] = "Cuota de Préstamo Vencido";
            } elseif (Carbon::create($fechaCuota)->toDateString() < Carbon::create($fechaAbono)->toDateString()) {
                $aux[] = "Cuota de Fecha Pendiente";
            }elseif (Carbon::create($fechaCuota)->toDateString() > Carbon::create($fechaAbono)->toDateString()) {
                $aux[] = "Adelanto de Cuota";
            }
            $aux[] = $dt->prestamo_cuota->prestamo->pendiente_abono;

            $data[] = $aux;
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        $head[] = 'Consecutivo';
        $head[] = 'Cedula';
        $head[] = 'Cliente';
        $head[] = 'Forma de Pago';
        $head[] = 'Fecha de Pago';
        $head[] = 'Cobrador';
        $head[] = 'Abonado Capital';
        $head[] = 'Abonado Interes';
        $head[] = 'Total Abonado';
        $head[] = 'Tipo';
        $head[] = '# Cuota';
        $head[] = 'Fecha Cuota';
        $head[] = 'Estado';
        $head[] = 'Saldo Actual';
        return $head;
    }
}
