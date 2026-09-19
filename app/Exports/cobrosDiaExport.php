<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class cobrosDiaExport implements FromCollection,WithHeadings,ShouldAutoSize
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
        foreach ($this->datos as $prestamo) {

            $fechaCuota = Carbon::parse($prestamo->fecha_cuota);
            $hoy = Carbon::parse(Carbon::now(),);
            $diferenciaDias = $fechaCuota->diffInDays($hoy);
            $aux = [
                $prestamo->prestamo->consecutivo,
                $prestamo->numero_cuota . " / " . count($prestamo->prestamo->cuotas),
                $prestamo->prestamo->cliente->full_name,
                $prestamo->prestamo->cliente->direccion,
                $prestamo->prestamo->cliente->departamento_municipio->departamento->nombre . " / " . $prestamo->prestamo->cliente->departamento_municipio->nombre,
                $prestamo->prestamo->cliente->telefono1,
                $prestamo->prestamo->cliente->telefono2,
                fecha_d_m_Y($prestamo->prestamo->fecha_desembolso),
                fecha_d_m_Y($prestamo->fecha_cuota),
                strval($diferenciaDias),
                $prestamo->prestamo->moneda,
                $prestamo->monto_cuota,
                $prestamo->total_pendiente_mora,
                number_format($prestamo->monto_pendiente_cuota),
                $prestamo->prestamo->agente->full_name,
            ];
            $data[]=$aux;
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            '# Préstamo',
            '# Cuota',
            'Cliente',
            'Dirección',
            'Dep/Mun',
            'Tel. 1',
            'Tel. 2',
            'Fecha Desembolso',
            'Fecha Cuota',
            'Dias Vencidos',
            'Moneda',
            'Cuota',
            'Mora',
            'Pendiente',
            'Cobrador',
        ];
    }
}
