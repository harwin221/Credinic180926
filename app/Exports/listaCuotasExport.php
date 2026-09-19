<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class listaCuotasExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $datos;
    private $tipo;

    public function __construct($lista, $tp)
    {
        $this->datos = $lista;
        $this->tipo = $tp;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];

        if ($this->tipo == 1) {//detallado
            foreach ($this->datos as $abono) {
                $aux = [];
                $aux[] = $abono->prestamo_cuota->numero_cuota;
                $aux[] = $abono->prestamo_cuota->prestamo->cliente->full_name;
                $aux[] = $abono->prestamo_cuota->prestamo->agente->full_name;
                $aux[] = fecha_d_m_Y_h_i($abono->fecha_abono);

                if (isset($abono->abono))
                    if ($abono->abono->estado == 2)
                        $aux[] = "Anulado";
                    else
                        $aux[] = $abono->abono->estado_abono;
                else
                    $aux[] = 'N-D';

                $aux[] = $abono->prestamo_cuota->estado_cuota;
                $aux[] = $abono->prestamo_cuota->prestamo->moneda;
                $aux[] = number_format($abono->prestamo_cuota->monto_cuota, 2);
                $aux[] = number_format($abono->monto_abono, 2);
                $aux[] = number_format($abono->prestamo_cuota->monto_pendiente_cuota, 2);
                $aux[] = "";
                $data[] = $aux;
            }
        } else {//consolidado

            foreach ($this->datos as $abono) {
                $aux = [];
                $aux[] = $abono->fecha_abono;
                $aux[] = $abono->prestamo->consecutivo;
                $aux[] = $abono->prestamo->cliente->full_name;
                $aux[] = $abono->prestamo->agente->full_name;
                $aux[] = $abono->total_abonado_capital;
                $aux[] = $abono->total_abonado_interes;
                $aux[] = $abono->total_abonado;
                $data[] = $aux;
            }
        }
        return new Collection($data);
    }

    public function headings(): array
    {

        if ($this->tipo == 1) {
            return [
                '# Cuota',
                'Cliente',
                'Cobrador',
                'Fecha',
                'Estado Abono',
                'Estado Cuota',
                'Moneda',
                'Cuota',
                'Pagado',
                'Pendiente',
            ];
        } else {
            return [
                'Fecha Aplicación',
                'Consecutivo',
                'Cliente',
                'Cobrador',
                'Capital',
                'Interes',
                'Total Abonado',
            ];
        }
    }
}
