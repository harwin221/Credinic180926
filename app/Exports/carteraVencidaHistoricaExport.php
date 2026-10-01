<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class carteraVencidaHistoricaExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithTitle,
    WithEvents
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Cartera Vencida Histórica';
    }

    public function collection(): Collection
    {
        $rows = [];
        $num  = 1;

        $gestorActual = null;

        // Subtotales por gestor
        $subVencer    = 0;
        $subActual    = 0;
        $subDif       = 0;
        $subCantidad  = 0;

        foreach ($this->data as $p) {
            // Fila separadora de gestor
            if ($gestorActual !== $p->gestor_original) {
                // Si ya había un gestor anterior, insertar subtotal
                if ($gestorActual !== null) {
                    $rows[] = [
                        '', '', "SUBTOTAL {$gestorActual} ({$subCantidad} créditos)",
                        '', '', '', '', '', '',
                        number_format($subVencer, 2),
                        number_format($subActual, 2),
                        number_format($subDif, 2),
                    ];
                    $rows[] = array_fill(0, 12, ''); // fila en blanco
                    $subVencer = $subActual = $subDif = $subCantidad = 0;
                }

                // Fila de cabecera del gestor
                $rows[] = [
                    "GESTOR ORIGINAL: {$p->gestor_original}",
                    '', '', '', '', '', '', '', '', '', '', '',
                ];
                $gestorActual = $p->gestor_original;
                $num = 1;
            }

            $dias   = (int)$p->dias_vencido;
            $clasif = $dias <= 15 ? 'A' : ($dias <= 30 ? 'B' : ($dias <= 60 ? 'C' : ($dias <= 90 ? 'D' : 'E')));

            $rows[] = [
                $num++,
                $p->consecutivo,
                $p->cliente_nombre,
                $p->gestor_original,
                $p->gestor_actual,
                $p->fecha_desembolso
                    ? Carbon::parse($p->fecha_desembolso)->format('d/m/Y')
                    : 'N/A',
                $p->fecha_vencimiento
                    ? Carbon::parse($p->fecha_vencimiento)->format('d/m/Y')
                    : 'N/A',
                $dias,
                $clasif,
                number_format((float)$p->saldo_al_vencer, 2),
                number_format((float)$p->saldo_actual, 2),
                number_format((float)$p->diferencia_cobrada, 2),
            ];

            $subVencer   += (float)$p->saldo_al_vencer;
            $subActual   += (float)$p->saldo_actual;
            $subDif      += (float)$p->diferencia_cobrada;
            $subCantidad++;
        }

        // Último subtotal
        if ($gestorActual !== null && $subCantidad > 0) {
            $rows[] = [
                '', '', "SUBTOTAL {$gestorActual} ({$subCantidad} créditos)",
                '', '', '', '', '', '',
                number_format($subVencer, 2),
                number_format($subActual, 2),
                number_format($subDif, 2),
            ];
            $rows[] = array_fill(0, 12, '');
        }

        // Fila de total general
        $totalVencer = $this->data->sum('saldo_al_vencer');
        $totalActual = $this->data->sum('saldo_actual');
        $totalDif    = $this->data->sum('diferencia_cobrada');
        $totalCant   = $this->data->count();

        $rows[] = [
            '', '', "TOTAL GENERAL ({$totalCant} créditos)",
            '', '', '', '', '', '',
            number_format($totalVencer, 2),
            number_format($totalActual, 2),
            number_format($totalDif, 2),
        ];

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            '#',
            'N° Crédito',
            'Cliente',
            'Gestor Original',
            'Gestor Actual',
            'Fecha Desembolso',
            'Fecha Vencimiento',
            'Días Vencido',
            'Clasificación',
            'Saldo al Vencer (C$)',
            'Saldo Actual (C$)',
            'Diferencia Cobrada (C$)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Fila de encabezados (fila 1)
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A2E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Recorrer filas para aplicar estilos dinámicos
                for ($row = 2; $row <= $lastRow; $row++) {
                    $cellA = $sheet->getCell("A{$row}")->getValue();
                    $cellC = $sheet->getCell("C{$row}")->getValue();

                    // Fila de cabecera de gestor
                    if (is_string($cellA) && str_starts_with($cellA, 'GESTOR ORIGINAL:')) {
                        $sheet->mergeCells("A{$row}:L{$row}");
                        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2D3748']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                        ]);
                        continue;
                    }

                    // Fila de subtotal
                    if (is_string($cellC) && str_starts_with($cellC, 'SUBTOTAL')) {
                        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF1A365D']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEBF8FF']],
                            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF2B6CB0']]],
                        ]);
                        continue;
                    }

                    // Fila de total general
                    if (is_string($cellC) && str_starts_with($cellC, 'TOTAL GENERAL')) {
                        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A2E']],
                            'borders' => ['top' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FFFFFFFF']]],
                        ]);
                        continue;
                    }

                    // Filas de datos normales — alternar color
                    if (is_numeric($cellA) || $cellA === '') {
                        $bgColor = ($row % 2 === 0) ? 'FFF7FAFC' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']],
                            ],
                        ]);

                        // Columnas numéricas alineadas a la derecha
                        $sheet->getStyle("J{$row}:L{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("H{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("I{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                // Encabezado título arriba del reporte
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'CREDINICA — REPORTE DE CARTERA VENCIDA HISTÓRICA — Generado: ' . now()->format('d/m/Y H:i'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A2E']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Freeze header rows
                $sheet->freezePane('A4');
            },
        ];
    }
}
