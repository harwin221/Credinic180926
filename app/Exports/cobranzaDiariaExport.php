<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;


class cobranzaDiariaExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    private $cuotasDia;
    private $cuotasMora;
    private $cuotasVencidas;
    private $fecha;

    public function __construct($cuotasDia, $cuotasMora, $cuotasVencidas, $fecha)
    {
        $this->cuotasDia = $cuotasDia;
        $this->cuotasMora = $cuotasMora;
        $this->cuotasVencidas = $cuotasVencidas;
        $this->fecha = $fecha;
    }

    public function collection()
    {
        $dt_aux = [];
        
        // ─── SECCIÓN 1: CUOTAS DEL DÍA ───
        if($this->cuotasDia->count() > 0) {
            $dt_aux[] = ['CUOTAS DEL DÍA'];
            $sumaCuotas1 = 0; $sumaAtraso1 = 0; $sumaAtrasoCuota1 = 0; $sumaVencidas1 = 0; $sumaPendiente1 = 0;
            
            foreach ($this->cuotasDia as $item) {
                $p = $item['prestamo'];
                $dt_aux[] = [
                    $p->agente->username ?? 'N/A',
                    $p->consecutivo,
                    $p->tipo_prestamo,
                    $p->cliente->full_name ?? 'N/A',
                    $p->cliente->direccion ?? '-',
                    $p->cliente->telefono1 ?? '-',
                    $p->fecha_desembolso,
                    $item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : '-',
                    $this->fecha,
                    $p->moneda,
                    $item['cuota_hoy'],
                    $item['atraso'],
                    $item['atraso'] + $item['cuota_hoy'],
                    $item['cant_venc'],
                    $item['dias_atraso'],
                    $item['saldo_total'],
                    $p->forma_pago,
                    $p->estado_prestamo
                ];
                $sumaCuotas1 += $item['cuota_hoy'];
                $sumaAtraso1 += $item['atraso'];
                $sumaAtrasoCuota1 += $item['atraso'] + $item['cuota_hoy'];
                $sumaVencidas1 += $item['cant_venc'];
                $sumaPendiente1 += $item['saldo_total'];
            }
            $dt_aux[] = ['', '', '', '', '', '', '', '', '', 'SUBTOTAL DÍA:', $sumaCuotas1, $sumaAtraso1, $sumaAtrasoCuota1, $sumaVencidas1, '', $sumaPendiente1];
            $dt_aux[] = ['']; // Espacio
        }

        // ─── SECCIÓN 2: CLIENTES EN MORA ───
        if($this->cuotasMora->count() > 0) {
            $dt_aux[] = ['CLIENTES EN MORA'];
            $sumaAtraso2 = 0; $sumaVencidas2 = 0; $sumaPendiente2 = 0;
            
            foreach ($this->cuotasMora as $item) {
                $p = $item['prestamo'];
                $dt_aux[] = [
                    $p->agente->username ?? 'N/A',
                    $p->consecutivo,
                    $p->tipo_prestamo,
                    $p->cliente->full_name ?? 'N/A',
                    $p->cliente->direccion ?? '-',
                    $p->cliente->telefono1 ?? '-',
                    $p->fecha_desembolso,
                    $item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : '-',
                    '-',
                    $p->moneda,
                    '-',
                    $item['atraso'],
                    '-',
                    $item['cant_venc'],
                    $item['dias_atraso'],
                    $item['saldo_total'],
                    $p->forma_pago,
                    $p->estado_prestamo
                ];
                $sumaAtraso2 += $item['atraso'];
                $sumaVencidas2 += $item['cant_venc'];
                $sumaPendiente2 += $item['saldo_total'];
            }
            $dt_aux[] = ['', '', '', '', '', '', '', '', '', 'SUBTOTAL MORA:', '', $sumaAtraso2, '', $sumaVencidas2, '', $sumaPendiente2];
            $dt_aux[] = ['']; // Espacio
        }

        // ─── SECCIÓN 3: PRÉSTAMOS VENCIDOS ───
        if($this->cuotasVencidas->count() > 0) {
            $dt_aux[] = ['PRÉSTAMOS VENCIDOS'];
            $sumaVencidas3 = 0; $sumaPendiente3 = 0;
            
            foreach ($this->cuotasVencidas as $item) {
                $p = $item['prestamo'];
                $dt_aux[] = [
                    $p->agente->username ?? 'N/A',
                    $p->consecutivo,
                    $p->tipo_prestamo,
                    $p->cliente->full_name ?? 'N/A',
                    $p->cliente->direccion ?? '-',
                    $p->cliente->telefono1 ?? '-',
                    $p->fecha_desembolso,
                    $item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : '-',
                    '-',
                    $p->moneda,
                    '-',
                    '-',
                    '-',
                    $item['cant_venc'],
                    $item['dias_atraso'],
                    $item['saldo_total'],
                    $p->forma_pago,
                    $p->estado_prestamo
                ];
                $sumaVencidas3 += $item['cant_venc'];
                $sumaPendiente3 += $item['saldo_total'];
            }
            $dt_aux[] = ['', '', '', '', '', '', '', '', '', 'SUBTOTAL VENCIDOS:', '', '', '', $sumaVencidas3, '', $sumaPendiente3];
            $dt_aux[] = ['']; // Espacio
        }

        return new Collection($dt_aux);
    }

    public function headings(): array
    {
        return [
            'Cobrador',
            'Consecutivo',
            'Tipo',
            'Cliente',
            'Dirección',
            'Teléfono',
            'Fecha Desembolso',
            'Fecha Vencimiento',
            'Fecha Cuota',
            'Moneda',
            'Cuota',
            'Saldo Atraso',
            'Cuota + Atraso',
            'Cuotas Vencidas',
            'Días Atraso',
            'Saldo Total',
            'Frecuencia',
            'Estado Prestamo',
        ];
    }
}
