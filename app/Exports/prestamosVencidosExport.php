<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class prestamosVencidosExport implements FromCollection,ShouldAutoSize,WithHeadings
{
    private $data;
    public function __construct($dt)
    {
        $this->data = $dt;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $datos = array();

        foreach ($this->data as $prestamo) {
            $aux = [];
            $aux[] = $prestamo->consecutivo;
            $aux[] = $prestamo->cliente->full_name;
            $aux[] = $prestamo->agente->full_name;
            $aux[] = $prestamo->forma_pago;
            $aux[] = $prestamo->monto_prestamo;
            $aux[] = $prestamo->monto_financiado;
            $aux[] = $prestamo->suma_abonos;
            $aux[] = ($prestamo->monto_financiado - $prestamo->suma_abonos);
            $aux[] = $prestamo->fecha_prestamo;
            $aux[] = $prestamo->cuotas()->orderBy('id', 'desc')->first() ? fecha_d_m_Y($prestamo->cuotas()->orderBy('id', 'desc')->first()->fecha_cuota) : "-";
            $datos[] = $aux;
        }
        return new Collection($datos);
    }

    public function headings(): array
    {
        return [
          '# Prestamo',
          'Cliente',
          'Cobrador',
          'Frecuencia',
          'Monto Préstamo',
          'Financiado',
          'Abonado',
          'Pendiente',
          'Fecha Préstamo',
          'Fecha Última Cuota',
        ];
    }
}
