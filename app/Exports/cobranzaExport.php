<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class cobranzaExport implements FromCollection,WithHeadings,ShouldAutoSize
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
        $dt_aux = [];

        foreach ($this->datos as $cuota){
            $aux = [];
            $aux[]=$cuota->prestamo->consecutivo;
            $aux[]=$cuota->prestamo->tipo_prestamo;
            $aux[]=$cuota->prestamo->cliente->cedula;
            $aux[]=$cuota->prestamo->cliente->full_name;
            $aux[]=$cuota->prestamo->fecha_desembolso;
            $aux[]=$cuota->prestamo->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($cuota->prestamo->cuotas()->orderBy('id','desc')->first()->fecha_cuota):"-";
            $aux[]=$cuota->fecha_cuota;

            if ($cuota->prestamo->forma_pago_tipo == 1) {
                $aux[]=number_format($cuota->monto_pendiente_cuota,2);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
            } elseif ($cuota->prestamo->forma_pago_tipo == 2) {
                $aux[] = strval(0);
                $aux[] = number_format($cuota->monto_pendiente_cuota, 2);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
            } elseif ($cuota->prestamo->forma_pago_tipo == 3) {
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = number_format($cuota->monto_pendiente_cuota, 2);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
            } elseif ($cuota->prestamo->forma_pago_tipo == 4) {
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = number_format($cuota->monto_pendiente_cuota, 2);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
            }
            elseif ($cuota->prestamo->forma_pago_tipo == 5) {
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = number_format($cuota->monto_pendiente_cuota, 2);
                $aux[] = strval(0);
                $aux[] = strval(0);
            }
            elseif ($cuota->prestamo->forma_pago_tipo == 6) {
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = number_format($cuota->monto_pendiente_cuota, 2);
                $aux[] = strval(0);

            }
            elseif ($cuota->prestamo->forma_pago_tipo == 7) {
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = strval(0);
                $aux[] = number_format($cuota->monto_pendiente_cuota, 2);
            }
            $aux[]=$cuota->prestamo->agente->username;
            $dt_aux[]=$aux;
        }

        return new Collection($dt_aux);
    }

    public function headings(): array
    {
        return [
            'Consecutivo',
            'Tipo',
            'Cedula',
            'Cliente',
            'Fecha Desembolso',
            'Fecha Vencimiento',
            'Fecha Cuota',
            'Diario',
            'Semanal',
            'Quincenal',
            'Mensual',
            'Trimestral',
            'Bimestral',
            'Catorcenal',
            'Cobrador',
        ];
    }
}
