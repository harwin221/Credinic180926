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
    /** Total de columnas del reporte; los subtotales se arman con este total */
    const COLS = 14;

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

        // Subtotales por agente
        $subInicio  = 0;
        $subCobrado = 0;
        $subCierre  = 0;
        $subCantidad = 0;

        foreach ($this->data as $p) {
            // Fila separadora de agente
            if ($gestorActual !== $p->agente_origen) {
                // Si ya había un agente anterior, insertar subtotal
                if ($gestorActual !== null) {
                    $rows[] = [
                        '', '', "SUBTOTAL {$gestorActual} ({$subCantidad} créditos)",
                        '', '', '', '', '', '', '', '', '', '',
                        $this->veredicto($subCierre - $subInicio),
                    ];
                    $rows[] = array_fill(0, self::COLS, ''); // fila en blanco
                    $subInicio = $subCobrado = $subCierre = $subCantidad = 0;
                }

                // Fila de cabecera del agente
                $rows[] = array_merge(["AGENTE: {$p->agente_origen}"], array_fill(0, self::COLS - 1, ''));
                $gestorActual = $p->agente_origen;
                $num = 1;
            }

            $dias   = (int)$p->dias_vencido;
            $clasif = $dias <= 15 ? 'A' : ($dias <= 30 ? 'B' : ($dias <= 60 ? 'C' : ($dias <= 90 ? 'D' : 'E')));

            $rows[] = [
                $num++,
                $p->consecutivo,
                $p->cliente_nombre,
                $p->agente_origen_nombre,
                $p->agente_actual_nombre . ($p->agente_reasignado ? ' (reasignado)' : ''),
                number_format((float)$p->monto_colocado, 2),
                $p->fecha_desembolso
                    ? Carbon::parse($p->fecha_desembolso)->format('d/m/Y')
                    : 'N/A',
                $p->fecha_vencimiento
                    ? Carbon::parse($p->fecha_vencimiento)->format('d/m/Y')
                    : 'N/A',
                $dias,
                $clasif,
                number_format((float)$p->vencido_inicio, 2),
                number_format((float)$p->cobrado_periodo, 2),
                number_format((float)$p->vencido_cierre, 2),
                $this->veredicto((float)$p->diferencia),
            ];

            $subInicio   += (float)$p->vencido_inicio;
            $subCobrado  += (float)$p->cobrado_periodo;
            $subCierre   += (float)$p->vencido_cierre;
            $subCantidad++;
        }

        // Último subtotal
        if ($gestorActual !== null && $subCantidad > 0) {
            $rows[] = [
                '', '', "SUBTOTAL {$gestorActual} ({$subCantidad} créditos)",
                '', '', '', '', '', '', '', '', '', '',
                $this->veredicto($subCierre - $subInicio),
            ];
            $rows[] = array_fill(0, self::COLS, '');
        }

        // Fila de total general
        $totalInicio  = $this->data->sum('vencido_inicio');
        $totalCobrado = $this->data->sum('cobrado_periodo');
        $totalCierre  = $this->data->sum('vencido_cierre');
        $totalCant    = $this->data->count();

        $rows[] = [
            '', '', "TOTAL GENERAL ({$totalCant} créditos)",
            '', '', '', '', '', '', '', '', '', '',
            $this->veredicto($totalCierre - $totalInicio),
        ];
        $rows[] = array_fill(0, self::COLS, '');
        $rows[] = [
            '', '', 'TOTALES', '', '', '', '', '', '',
            '',
            number_format($totalInicio, 2),
            number_format($totalCobrado, 2),
            number_format($totalCierre, 2),
            '',
        ];

        return new Collection($rows);
    }

    /** Etiqueta de veredicto según si el vencido bajó, subió o quedó igual */
    private function veredicto(float $dif): string
    {
        if ($dif < 0) return 'BAJÓ ' . number_format(abs($dif), 2);
        if ($dif > 0) return 'SUBIÓ ' . number_format($dif, 2);
        return 'SIN CAMBIO';
    }

    public function headings(): array
    {
        return [
            '#',
            'N° Crédito',
            'Cliente',
            'Agente con el Crédito',
            'Agente Actual',
            'Monto Colocado (C$)',
            'Fecha Desembolso',
            'Fecha Vencimiento',
            'Días Vencido',
            'Clasif.',
            'Vencido Inicio (C$)',
            'Cobrado Período (C$)',
            'Vencido Cierre (C$)',
            'Diferencia',
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
                    if (is_string($cellA) && str_starts_with($cellA, 'AGENTE:')) {
                        $sheet->mergeCells("A{$row}:N{$row}");
                        $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2D3748']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                        ]);
                        continue;
                    }

                    // Fila de subtotal
                    if (is_string($cellC) && str_starts_with($cellC, 'SUBTOTAL')) {
                        $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF1A365D']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEBF8FF']],
                            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF2B6CB0']]],
                        ]);
                        continue;
                    }

                    // Fila de totales al pie
                    if (is_string($cellC) && $cellC === 'TOTALES') {
                        $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF1A365D']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEDF2F7']],
                        ]);
                        continue;
                    }

                    // Fila de total general
                    if (is_string($cellC) && str_starts_with($cellC, 'TOTAL GENERAL')) {
                        $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A2E']],
                            'borders' => ['top' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FFFFFFFF']]],
                        ]);
                        continue;
                    }

                    // Filas de datos normales — alternar color
                    if (is_numeric($cellA) || $cellA === '') {
                        $bgColor = ($row % 2 === 0) ? 'FFF7FAFC' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']],
                            ],
                        ]);

                        // Columnas numéricas alineadas a la derecha
                        $sheet->getStyle("K{$row}:N{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("I{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("J{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                // Encabezado título arriba del reporte
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:N1');
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
