<?php

namespace App\Exports;

use App\Models\prestamosModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class saldoCarteraExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    private $datos;
    private $mesInicio;
    private $anyoInicio;
    private $meses;
    private $mesesArr;
    private $fin;
    public function __construct($dt,$inicioM,$inicioY,$fin)
    {
        $this->datos = $dt;
        $this->mesInicio = $inicioM;
        $this->anyoInicio = $inicioY;
        $this->fin = $fin;

        $fechaInicio = Carbon::parse($inicioY.'-'.$inicioM.'-01');
        $fechaFin = Carbon::parse(Carbon::now());

        $mesesEnRango = $fechaInicio->diffInMonths($fechaFin);

        $meses = [];
        for ($i = 0; $i <= $mesesEnRango; $i++) {
            $meses[] = $fechaInicio->copy()->addMonths($i)->format('m Y');
        }
        $this->meses = $meses;

        $this->mesesArr = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $prestamoIds = $this->datos->pluck('id')->toArray();
        
        // OPTIMIZACIÓN: Obtener fechas de cuotas y abonos en lote
        $ultimasFechas = \DB::table('prestamos as p')
            ->whereIn('p.id', $prestamoIds)
            ->whereNull('p.deleted_at')
            ->leftJoin(\DB::raw('(SELECT prestamo_id, MAX(fecha_cuota) as ultima_cuota FROM prestamo_coutas GROUP BY prestamo_id) as c'), 'c.prestamo_id', '=', 'p.id')
            ->leftJoin(\DB::raw('(SELECT prestamo_id, MAX(fecha_abono) as ultimo_abono FROM abonos WHERE estado = 1 GROUP BY prestamo_id) as a'), 'a.prestamo_id', '=', 'p.id')
            ->select('p.id', 'c.ultima_cuota', 'a.ultimo_abono')
            ->get()
            ->keyBy('id');

        // Obtener cantidad de préstamos por cliente para saber si es "Nuevo" o "Recurrente"
        $clienteIds = $this->datos->pluck('user_id')->unique()->toArray();
        $conteoPrestamos = \DB::table('prestamos')
            ->whereIn('user_id', $clienteIds)
            ->where('desembolsado', 1)
            ->whereNull('deleted_at')
            ->select('user_id', \DB::raw('count(*) as total_prestamos'))
            ->groupBy('user_id')
            ->pluck('total_prestamos', 'user_id');

        // Obtener cuotas vencidas al corte (Base)
        $cuotasVencidasBase = \DB::table('prestamo_coutas')
            ->whereIn('prestamo_id', $prestamoIds)
            ->where('estado', '!=', 3) // No pagadas por completo
            ->whereDate('fecha_cuota', '<=', $this->fin) // Vencidas a la fecha
            ->select('prestamo_id', 
                \DB::raw('COUNT(id) as total_vencidas'),
                \DB::raw('SUM(monto_cuota - monto_interes) as capital_base'),
                \DB::raw('SUM(monto_interes) as interes_base')
            )
            ->groupBy('prestamo_id')
            ->get()
            ->keyBy('prestamo_id');

        // Obtener abonos realizados a esas cuotas vencidas
        $abonosVencidos = \DB::table('prestamo_cuota_abono as pca')
            ->join('abonos as ab', 'ab.id', '=', 'pca.abono_id')
            ->join('prestamo_coutas as pc', 'pc.id', '=', 'pca.prestamo_cuota_id')
            ->whereIn('pc.prestamo_id', $prestamoIds)
            ->where('pc.estado', '!=', 3)
            ->whereDate('pc.fecha_cuota', '<=', $this->fin)
            ->where('pca.estado', 1)
            ->where('ab.estado', 1)
            ->whereDate('ab.fecha_abono', '<=', $this->fin)
            ->select('pc.prestamo_id', 
                \DB::raw('SUM(pca.total_capital) as capital_abonado'),
                \DB::raw('SUM(pca.total_interes) as interes_abonado')
            )
            ->groupBy('pc.prestamo_id')
            ->get()
            ->keyBy('prestamo_id');

        $data = [];

        foreach ($this->datos as $dt) {
            $fechas = $ultimasFechas->get($dt->id);
            $base = $cuotasVencidasBase->get($dt->id);
            $abono = $abonosVencidos->get($dt->id);
            
            $cap_vencido = $base ? $base->capital_base - ($abono ? $abono->capital_abonado : 0) : 0;
            $int_vencido = $base ? $base->interes_base - ($abono ? $abono->interes_abonado : 0) : 0;
            $total_vencidas = $base ? $base->total_vencidas : 0;

            $esRecurrente = ($conteoPrestamos->get($dt->user_id) > 1);

            $aux = [];
            $aux[] = strtoupper($dt->cliente_nombre);
            $aux[] = strtoupper($dt->agente_nombre);
            $aux[] = fecha_d_m_Y($dt->fecha_desembolso);
            $aux[] = $fechas && $fechas->ultima_cuota ? fecha_d_m_Y($fechas->ultima_cuota) : "-";
            $aux[] = $dt->forma_pago;
            $aux[] = $dt->plazo;
            $aux[] = $dt->moneda;
            $aux[] = $dt->monto_prestamo;
            
            $interes_total = $dt->monto_financiado - $dt->monto_prestamo;
            $aux[] = $interes_total;
            
            $capital_pendiente = $dt->monto_prestamo - $dt->suma_abonos_capital;
            $interes_pendiente = $interes_total - $dt->suma_abonos_interes;

            $aux[] = strval($capital_pendiente);
            $aux[] = strval($interes_pendiente);
            $aux[] = strval($capital_pendiente + $interes_pendiente);

            $aux[] = strval($dt->suma_abonos_capital);
            $aux[] = strval($dt->suma_abonos_interes);
            $aux[] = strval($dt->suma_abonos_capital + $dt->suma_abonos_interes);

            $aux[] = strval($dt->monto_financiado);
            $aux[] = strval($dt->monto_financiado - ($dt->suma_abonos_capital + $dt->suma_abonos_interes));
            
            // Si tiene cuotas vencidas, mostrar lo que debe de esas cuotas
            $aux[] = strval($total_vencidas > 0 ? $cap_vencido : 0);
            $aux[] = strval($total_vencidas > 0 ? $int_vencido : 0);
            
            // Pendiente actual general (Saldo actual = Total Pendiente)
            $aux[] = strval($capital_pendiente + $interes_pendiente);

            if ($total_vencidas > 0) {
                $aux[] = "Cuotas Pendientes";
            } else {
                $aux[] = "Al Día";
            }

            $aux[] = $esRecurrente ? 'Cliente Recurrente' : 'Nuevo Cliente';
            $aux[] = $dt->estado_prestamo;
            $aux[] = $dt->clasificacion ?? '';
            $aux[] = $dt->motivo_clasificacion ?? '';
            $aux[] = $dt->fecha_clasificacion ? fecha_d_m_Y($dt->fecha_clasificacion) : '';
            
            if ($dt->estado == 2) { // Cancelado
                $aux[] = $fechas && $fechas->ultimo_abono ? fecha_d_m_Y($fechas->ultimo_abono) : "-";
            } else {
                $aux[] = $fechas && $fechas->ultimo_abono ? fecha_d_m_Y($fechas->ultimo_abono) : "-";
            }

            $data[] = $aux;
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Cobrador',
            'Fecha Colocación',
            'Fecha Vencimiento',
            'Forma Pago',
            'Plazo (Meses)',
            'Moneda',
            'Capital Colocado',
            'Interes Total Ganar',
            'Capital Pendiente',
            'Interes Total Pendiente',
            'Total Pendiente',
            'Total Abonado Capital al corte',
            'Total Abonado Interes al corte',
            'Total Abonado al corte',
            'Total Recuperar',
            'Saldo al cierre',
            'Capital Pendiente al corte',
            'Interes Pendiente al corte',
            'Saldo actual',
            'Pendiente',
            'Tipo Cliente',
            'Estado del Desembolso',
            'Clasificación',
            'Motivo Clasificación',
            'Fecha Clasificación',
            'Fecha Ultimo Abono',
        ];
    }
}
