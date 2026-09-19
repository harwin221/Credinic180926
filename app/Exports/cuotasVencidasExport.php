<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class cuotasVencidasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $lista;

    public function __construct($lista)
    {
        $this->lista = $lista;
    }

    public function collection()
    {
        return collect($this->lista);
    }

    public function headings(): array
    {
        return [
            '# PRÉSTAMO',
            'CLIENTE',
            'COBRADOR',
            'N° CUOTA',
            'FECHA VENCIMIENTO',
            'MONTO CUOTA',
            'MONTO ABONADO',
            'MONTO PENDIENTE',
            'DÍAS VENCIDOS'
        ];
    }

    public function map($cuota): array
    {
        $abonado = $cuota->suma_abono ?? 0;
        $pendiente = $cuota->monto_cuota - $abonado;
        $diasVencidos = \Carbon\Carbon::parse($cuota->fecha_cuota)->diffInDays(now());

        return [
            $cuota->consecutivo,
            $cuota->cliente_nombre,
            $cuota->agente_nombre,
            $cuota->numero_cuota,
            fecha_d_m_Y($cuota->fecha_cuota),
            $cuota->monto_cuota,
            $abonado,
            $pendiente,
            $diasVencidos . ' días'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F9CB5']]],
        ];
    }
}
