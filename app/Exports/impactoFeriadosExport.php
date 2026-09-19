<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class impactoFeriadosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $prestamosAfectados;

    public function __construct($prestamosAfectados)
    {
        $this->prestamosAfectados = collect($prestamosAfectados);
    }

    public function collection()
    {
        return $this->prestamosAfectados;
    }

    public function headings(): array
    {
        return [
            'Consecutivo',
            'Cliente',
            'Cobrador',
            'Frecuencia',
            'Estado',
            'Monto Préstamo',
            'Total Cuotas',
            'Cuotas Afectadas',
            '% Afectado',
            'Detalle Cuotas Afectadas'
        ];
    }

    public function map($item): array
    {
        $detalleCuotas = '';
        foreach ($item['cuotas_afectadas'] as $cuota) {
            $detalleCuotas .= "Cuota #{$cuota['numero_cuota']} - {$cuota['fecha_actual']} ({$cuota['motivo']}); ";
        }

        return [
            '#' . $item['prestamo']->consecutivo,
            $item['prestamo']->cliente->full_name ?? 'N/A',
            $item['prestamo']->agente->full_name ?? 'N/A',
            $item['prestamo']->forma_pago,
            $item['prestamo']->estado_prestamo,
            $item['prestamo']->moneda . ' ' . number_format($item['prestamo']->monto_prestamo, 2),
            $item['total_cuotas'],
            $item['total_afectadas'],
            $item['porcentaje_afectado'] . '%',
            trim($detalleCuotas)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC000']]],
        ];
    }

    public function title(): string
    {
        return 'Impacto Feriados';
    }
}
