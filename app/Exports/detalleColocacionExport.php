<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class detalleColocacionExport implements FromCollection,ShouldAutoSize,WithHeadings
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
        $formas = ['1' => 'Diario', '2' => 'Semanal', '3' => 'Quincenal', '4' => 'Mensual', '5' => 'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'];
        $data = [];
        foreach ($this->datos as $dt) {
            $tipoAbono = 'Ordinario';
            if (isset($dt->tipo_abono)) {
                if ($dt->tipo_abono == 1) $tipoAbono = 'Deducción';
                elseif ($dt->tipo_abono == 2) $tipoAbono = 'Dispensa';
            }

            $aux = [];
            $aux[] = $dt->consecutivo;
            $aux[] = $dt->cliente_cedula;
            $aux[] = $dt->cliente_nombre;
            $aux[] = $formas[$dt->forma_pago_tipo] ?? '';
            $aux[] = fecha_d_m_Y_h_i($dt->created_at);
            $aux[] = $dt->agente_nombre;
            $aux[] = $dt->total_abonado_capital;
            $aux[] = $dt->total_abonado_interes;
            $aux[] = $dt->total_abonado;
            $aux[] = $tipoAbono;
            $data[]=$aux;
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
        return $head;
    }
}
