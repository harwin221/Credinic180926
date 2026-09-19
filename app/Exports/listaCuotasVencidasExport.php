<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class listaCuotasVencidasExport implements FromCollection,WithHeadings,ShouldAutoSize
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
        foreach ($this->datos as $cuota) {
            $aux = [];
            $aux[]=[
                $cuota->numero_cuota,
                $cuota->prestamo->consecutivo,
                $cuota->prestamo->cliente->full_name,
                $cuota->prestamo->agente->full_name,
                fecha_d_m_Y($cuota->fecha_cuota),
                $cuota->estado_cuota,
                $cuota->prestamo->moneda,
                $cuota->monto_cuota,
                $cuota->abonos()->sum('monto_abono'),
                $cuota->monto_cuota - $cuota->abonos()->sum('monto_abono'),
            ];

            $data[]=$aux;
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
          '# Cuota',
          '# Prestamo',
          'Cliente',
          'Cobrador',
          'Fecha Cuota',
          'Estado',
          'Moneda',
          'Cuota',
          'Pagado',
          'Pendiente',
        ];
    }
}
