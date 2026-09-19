<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class asignacionClientesExport implements FromCollection,WithHeadings,ShouldAutoSize
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

        foreach ($this->datos as $prest)
        {
            $aux = [
                $prest->consecutivo,
                $prest->cliente->full_name,
                $prest->agente->full_name,
                $prest->moneda,
                number_format($prest->monto_prestamo,2),
                number_format($prest->monto_financiado,2),
            ];
            $data[] = $aux;
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            '# Préstamo',
            'Cliente',
            'Cobrador',
            'Moneda',
            'Monto',
            'Monto Financiado',
        ];
    }
}
