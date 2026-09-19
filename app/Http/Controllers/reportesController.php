<?php

namespace App\Http\Controllers;

use App\Exports\antiguedadSaldosExport;
use App\Exports\arqueoExport;
use App\Exports\asignacionClientesExport;
use App\Exports\cobranzaDiariaExport;
use App\Exports\cobranzaExport;
use App\Exports\cobrosDiaExport;
use App\Exports\cuotasVencidasExport;
use App\Exports\detalleColocacionExport;
use App\Exports\detalleColocacionV2Export;
use App\Exports\detalleRecuperacionDetalladoExport;
use App\Exports\detalleRecuperacionV3Export;
use App\Exports\estadoCuentaClienteExport;
use App\Exports\impactoFeriadosExport;
use App\Exports\listaClientesExport;
use App\Exports\listaCuotasExport;
use App\Exports\listaCuotasVencidasExport;
use App\Exports\prestamosVencidosExport;
use App\Exports\saldoCarteraExport;
use App\Models\abonosModel;
use App\Models\arqueoModel;
use App\Models\prestamoCuotaAbonoModel;
use App\Models\prestamoCuotasModel;
use App\Models\prestamosModel;
use App\Models\User;
use App\Models\userAsignadoModel;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class reportesController extends Controller
{
    public function index()
    {
        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
            
        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $listaVendedores = User::admin()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        
        return view('reportes.index', compact('listaCobradores', 'listaClientes', 'listaVendedores'));
    }

    public function listClientes(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $clienteSel = $request->cliente;
        $cobradorSel = $request->cobrador; // array o null
        $vendedorSel = $request->vendedor;
        $estadoSel = $request->estado;
        $frecuenciaSel = $request->frecuencia;
        $inicioSel = $request->fecha_inicio;
        $finSel = $request->fecha_fin;

        if (!$inicioSel && !$finSel)
            $inicioSel = Carbon::now();
        if (!$finSel)
            $finSel = Carbon::now();

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $listaVendedores = User::admin()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $query = \DB::table('prestamos as p')
            ->join('users as u', 'p.user_id', '=', 'u.id')
            ->leftJoin('users as a_u', 'p.agente_id', '=', 'a_u.id')
            ->leftJoin('users as v_u', 'p.vendedor_id', '=', 'v_u.id')
            ->leftJoin('departamento_municipio as dm', 'u.dep_mun', '=', 'dm.id')
            ->leftJoin('departamento as d', 'dm.departamento_id', '=', 'd.id')
            // Optimización: Traer abonos totales
            ->leftJoin(DB::raw('(SELECT a.prestamo_id, SUM(pca.monto_abono) as suma_abonos 
                                FROM prestamo_cuota_abono pca 
                                JOIN abonos a ON a.id = pca.abono_id 
                                WHERE a.estado = 1 AND pca.estado = 1 
                                GROUP BY a.prestamo_id) as t_abonos'), 'p.id', '=', 't_abonos.prestamo_id')
            // Optimización: Contar total de cuotas por préstamo
            ->leftJoin(DB::raw('(SELECT prestamo_id, COUNT(*) as total_cuotas FROM prestamo_coutas GROUP BY prestamo_id) as t_cuotas'), 'p.id', '=', 't_cuotas.prestamo_id')
            // Optimización: Obtener fecha de la primera cuota pendiente vencida para Clasificación
            ->leftJoin(DB::raw('(SELECT prestamo_id, MIN(fecha_cuota) as fecha_atraso FROM prestamo_coutas WHERE estado != 3 AND fecha_cuota < NOW() GROUP BY prestamo_id) as t_atraso'), 'p.id', '=', 't_atraso.prestamo_id')
            ->select('p.*', 'p.plazo_pago as plazo',
                \DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_nombre"),
                'u.direccion as cliente_direccion', 'u.telefono1', 'u.telefono2',
                'd.nombre as departamento_nombre', 'dm.nombre as municipio_nombre',
                \DB::raw("CONCAT(a_u.nombres, ' ', a_u.apellidos) as agente_nombre"),
                \DB::raw("CONCAT(v_u.nombres, ' ', v_u.apellidos) as vendedor_nombre"),
                \DB::raw("(CASE WHEN p.moneda_prestamo = 1 THEN 'C$' ELSE 'U$' END) as moneda"),
                \DB::raw("(CASE WHEN p.forma_pago_tipo = 1 THEN 'Diario' WHEN p.forma_pago_tipo = 2 THEN 'Semanal' WHEN p.forma_pago_tipo = 3 THEN 'Quincenal' WHEN p.forma_pago_tipo = 4 THEN 'Mensual' WHEN p.forma_pago_tipo = 7 THEN 'Catorcenal' ELSE 'Otro' END) as forma_pago"),
                \DB::raw("(CASE WHEN p.tipo_desembolso = 1 THEN 'Nuevo' WHEN p.tipo_desembolso = 2 THEN 'Represtamo' WHEN p.tipo_desembolso = 3 THEN 'Reactivación' WHEN p.tipo_desembolso = 4 THEN 'Reestructuración' ELSE 'N/A' END) as tipo_prestamo_texto"),
                \DB::raw("(CASE WHEN p.tipo_destino = 1 THEN 'Comercio' WHEN p.tipo_destino = 2 THEN 'Servicio' WHEN p.tipo_destino = 3 THEN 'Producción' WHEN p.tipo_destino = 4 THEN 'Vivienda' WHEN p.tipo_destino = 5 THEN 'Consumo' ELSE 'N/A' END) as tipo_destino_texto"),
                \DB::raw("(CASE WHEN p.estado = 1 THEN 'Activo' WHEN p.estado = 2 THEN 'Pagado' WHEN p.estado = 3 THEN 'Vencido' WHEN p.estado = 4 THEN 'Anulado' ELSE 'Desconocido' END) as estado_texto"),
                't_abonos.suma_abonos', 't_cuotas.total_cuotas', 't_atraso.fecha_atraso'
            )
            ->when($clienteSel, function ($query) use ($clienteSel) {
                $query->where('p.user_id', decode($clienteSel));
            })
            ->when($cobradorSel, function ($query) use ($cobradorSel) {
                $ids = array_map('decode', (array)$cobradorSel);
                $query->whereIn('p.agente_id', $ids);
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('p.agente_id', $agentesAsignados);
            })
            ->when($vendedorSel, function ($query) use ($vendedorSel) {
                $query->where('p.vendedor_id', decode($vendedorSel));
            })
            ->when($frecuenciaSel, function ($query) use ($frecuenciaSel) {
                $query->where('p.forma_pago_tipo', $frecuenciaSel);
            })
            ->when($inicioSel, function ($query) use ($inicioSel) {
                $query->whereDate('p.fecha_prestamo', '>=', $inicioSel);
            })
            ->when($finSel, function ($query) use ($finSel) {
                $query->whereDate('p.fecha_prestamo', '<=', $finSel);
            })
            ->where('p.desembolsado', 1)
            ->where('p.estado', $estadoSel)
            ->where(function ($query) {
                $query->whereNull('p.estado_aprobacion')
                    ->orwhere('p.estado_aprobacion', 2);
            })
            ->whereNull('p.deleted_at')
            ->orderBy('p.id');

        $prestamos = $query;

        if ($request->pdf) {
            $prestamos = $prestamos->get();
            $pdf = Pdf::loadView('reportes.clientes.listClientesPDF', compact('prestamos'));
            $pdf->setPaper('letter', 'landscape');
            return $pdf->stream('Lista_Desembolsos_'.date('Y-m-d').'.pdf');
        }
        if ($request->excel) {
            $prestamos = $prestamos->get();
            return Excel::download(new listaClientesExport($prestamos), 'Lista de Desembolsos.xlsx');
        }
        
        // Si tiene parámetros de filtro, mostrar vista HTML con datos
        if($clienteSel || $cobradorSel || $vendedorSel || $frecuenciaSel || $estadoSel || $inicioSel || $finSel) {
            $prestamos = $prestamos->get();
            return view('reportes.clientes.listClientesHTML', compact('prestamos', 'clienteSel', 'cobradorSel', 'vendedorSel', 'frecuenciaSel', 'estadoSel', 'inicioSel', 'finSel', 'listaClientes', 'listaCobradores', 'listaVendedores'));
        }
        
        $prestamos = $prestamos->paginate(20);
        return view('reportes.clientes.listaClientes', compact('prestamos', 'listaClientes', 'listaCobradores','listaVendedores'));
    }

    public function listaCuotas(Request $request)
    {
        $desdeSel = $request->desde;
        $hastaSel = $request->hasta;
        $cobradorSel = $request->cobrador; // array o null

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $abonos = [];
        if ($desdeSel && $hastaSel) {
            // Usar abonosModel para evitar duplicados cuando un abono cubre múltiples cuotas
            $abonos = abonosModel::with(['prestamo.cliente', 'prestamo.agente'])
                ->when($desdeSel && $hastaSel, function ($query) use ($desdeSel, $hastaSel) {
                    $query->whereDate('fecha_abono', '>=', $desdeSel)
                        ->whereDate('fecha_abono', '<=', $hastaSel);
                })
                ->when($cobradorSel, function ($query) use ($cobradorSel) {
                    $ids = array_map('decode', (array)$cobradorSel);
                    $query->whereHas('prestamo', function($q) use ($ids) {
                        $q->whereIn('agente_id', $ids);
                    });
                })
                ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                    $query->whereHas('prestamo', function ($query) use ($agentesAsignados) {
                        $query->whereIn('agente_id', $agentesAsignados);
                    });
                })
                ->where('estado', 1)
                ->orderBy('fecha_abono', 'desc');

            if ($request->pdf) {
                $abonos = $abonos->get();
                $pdf = Pdf::loadView('reportes.cuotas.listaCuotasPDF', compact('abonos'));
                $pdf->setPaper('letter', 'landscape');
                return $pdf->stream('Lista_Cuotas_'.date('Y-m-d').'.pdf');
            }
            if ($request->excel1) {
                $abonos = $abonos->get();
                return Excel::download(new listaCuotasExport($abonos, 1), 'Lista de Cuotas.xlsx');
            }
            if ($request->excel2) {
                return Excel::download(new listaCuotasExport($abonos, 2), 'Lista de Cuotas.xlsx');
            }

            // Si tiene parámetros de filtro, mostrar vista HTML con datos
            $abonos = $abonos->get();
            return view('reportes.cuotas.listaCuotasHTML', compact('abonos', 'desdeSel', 'hastaSel', 'cobradorSel', 'listaCobradores'));
        }


        return view('reportes.cuotas.listaCuotas', compact('listaCobradores', 'abonos'));
    }

    public function detalleColocacion(Request $request)
    {
        $desde = $request->desde;
        $hasta = $request->hasta;
        $cobrador = $request->cobrador; // array o null
        $tipo = $request->tipo;
        $tipo_vista = $request->tipo_vista ?? 'detallado';

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();

        $listaCobradores = User::whereIn('tipo_usuario', [2, 4])->where('estado', 1)
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
            
        $abonosDia = array();
        if ($desde && $hasta) {
            $abonosDia = \DB::table('abonos as a')
                ->join('prestamos as p', 'p.id', '=', 'a.prestamo_id')
                ->join('users as u', 'u.id', '=', 'p.user_id')
                ->join('users as a_u', 'a_u.id', '=', 'p.agente_id')
                ->join('users as c_u', 'c_u.id', '=', 'a.created_user_id')
                // Sumas del abono (capital, interes, mora, total)
                ->leftJoin(DB::raw('(SELECT abono_id,
                                        SUM(total_capital)  as capital,
                                        SUM(total_interes)  as interes,
                                        SUM(total_mora)     as mora,
                                        SUM(monto_abono)    as total
                                    FROM prestamo_cuota_abono
                                    WHERE estado = 1
                                    GROUP BY abono_id) as t_pca'), 'a.id', '=', 't_pca.abono_id')
                // Fecha de la cuota MÁS ANTIGUA aplicada en este abono
                // → si es anterior a la fecha del abono = cobro en mora
                ->leftJoin(DB::raw('(SELECT pca.abono_id, MIN(pc.fecha_cuota) as fecha_cuota_min
                                    FROM prestamo_cuota_abono pca
                                    JOIN prestamo_coutas pc ON pc.id = pca.prestamo_cuota_id
                                    WHERE pca.estado = 1
                                    GROUP BY pca.abono_id) as t_fc'), 'a.id', '=', 't_fc.abono_id')
                // Detectar préstamos vencidos: sin cuotas futuras pendientes Y con saldo pendiente
                ->leftJoin(DB::raw('(SELECT prestamo_id,
                                        COUNT(*) as cuotas_futuras_pend
                                    FROM prestamo_coutas
                                    WHERE fecha_cuota >= CURDATE()
                                      AND estado IN (1,2)
                                    GROUP BY prestamo_id) as t_fut'), 'p.id', '=', 't_fut.prestamo_id')
                ->leftJoin(DB::raw('(SELECT pc2.prestamo_id,
                                        SUM(pc2.monto_cuota) as total_cuotas,
                                        COALESCE((SELECT SUM(pca2.monto_abono)
                                                  FROM prestamo_cuota_abono pca2
                                                  JOIN prestamo_coutas pc3 ON pc3.id = pca2.prestamo_cuota_id
                                                  WHERE pc3.prestamo_id = pc2.prestamo_id
                                                    AND pca2.estado = 1), 0) as total_abonado_prest
                                    FROM prestamo_coutas pc2
                                    GROUP BY pc2.prestamo_id) as t_saldo'), 'p.id', '=', 't_saldo.prestamo_id')
                ->select(
                    'a.id', 'a.prestamo_id', 'a.fecha_abono', 'a.tipo_abono',
                    'a.created_user_id', 'a.created_at',
                    'p.consecutivo', 'p.moneda_prestamo', 'p.forma_pago_tipo',
                    'p.estado as prestamo_estado',
                    'p.user_id as cliente_id',
                    'u.cedula as cliente_cedula',
                    \DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_nombre"),
                    \DB::raw("CONCAT(a_u.nombres, ' ', a_u.apellidos) as agente_nombre"),
                    'a_u.username as cobrador_username',
                    \DB::raw("CONCAT(c_u.nombres, ' ', c_u.apellidos) as creador_nombre"),
                    't_pca.capital as total_abonado_capital',
                    't_pca.interes as total_abonado_interes',
                    't_pca.mora as total_abonado_mora',
                    't_pca.total as total_abonado',
                    't_fc.fecha_cuota_min',
                    \DB::raw('COALESCE(t_fut.cuotas_futuras_pend, 0) as cuotas_futuras_pend'),
                    \DB::raw('COALESCE(t_saldo.total_cuotas, 0) - COALESCE(t_saldo.total_abonado_prest, 0) as saldo_pendiente_prest')
                )
                ->whereDate('a.fecha_abono', '>=', $desde)
                ->whereDate('a.fecha_abono', '<=', $hasta)
                ->where('a.estado', 1)
                ->whereNull('p.deleted_at')
                ->when($tipo >= 0 && $tipo !== null, function ($query) use ($tipo) {
                    $query->where('a.tipo_abono', $tipo);
                })
                ->when($cobrador, function ($query) use ($cobrador) {
                    $ids = array_map('decode', (array)$cobrador);
                    $query->whereIn('a.created_user_id', $ids);
                })
                ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                    $query->whereIn('a.created_user_id', $agentesAsignados);
                })
                ->orderBy('a.created_user_id')
                ->orderBy('a.fecha_abono')
                ->get();

            if ($request->pdf) {
                $pdf = Pdf::loadView('reportes.detalleColocacion.detalleColocacionPDF', [
                    'abonosDia' => $abonosDia,
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'tipo_vista' => $tipo_vista
                ]);
                $pdf->setPaper('letter', 'landscape');
                return $pdf->stream('Detalle_Recuperacion_'.date('Y-m-d').'.pdf');
            }
            if ($request->excel1) {
                return Excel::download(new detalleColocacionExport($abonosDia), 'Detalle de recuperacion.xlsx');
            }
            if ($request->excel2) {
                $abonosDiaResult = $abonosDia->groupBy('fecha_abono');
                return Excel::download(new detalleRecuperacionDetalladoExport($abonosDiaResult, $desde, $hasta, $cobrador), 'Detalle de recuperación Detallado.xlsx');
            }
            if ($request->excel3) {
                $abonosDiaResult = prestamoCuotaAbonoModel::join('abonos as a', 'a.id', 'prestamo_cuota_abono.abono_id')
                    ->whereDate('a.fecha_abono', '>=', $desde)
                    ->whereDate('a.fecha_abono', '<=', $hasta)
                    ->where('a.estado', 1)
                    ->when($tipo >= 0 && $tipo !== null, function ($query) use ($tipo) {
                        $query->where('a.tipo_abono', $tipo);
                    })
                    ->when($cobrador, function ($query) use ($cobrador) {
                        $ids = array_map('decode', (array)$cobrador);
                        $query->whereIn('a.created_user_id', $ids);
                    })->select('prestamo_cuota_abono.*')->get();
                return Excel::download(new detalleRecuperacionV3Export($abonosDiaResult), 'Detalle de recuperación V3.xlsx');
            }

            // MODO RESUMIDO (Si aplica)
            $resumen = [];
            if ($tipo_vista == 'resumido') {
                $resumen = $abonosDia->groupBy('created_user_id')->map(function ($items, $userId) {
                    return (object)[
                        'cobrador_nombre' => $items->first()->creador_nombre ?? 'Sin asignar',
                        'monto_capital' => $items->sum('total_abonado_capital'),
                        'monto_interes' => $items->sum('total_abonado_interes'),
                        'monto_mora' => $items->sum('total_abonado_mora'),
                        'total_abono' => $items->sum('total_abonado'),
                        'clientes' => $items->pluck('prestamo.user_id')->unique()->count(),
                        'abonos_cant' => $items->count()
                    ];
                });
            }

            // ─── CUADRO RESUMEN DE CLASIFICACIÓN DEL DÍA ──────────────────────────────
            // Clasificación de cada abono por la fecha de la cuota más antigua aplicada:
            //   fecha_cuota_min < fecha_abono  → MORA   (cuota atrasada)
            //   fecha_cuota_min = fecha_abono  → DÍA    (cuota del día)
            //   fecha_cuota_min > fecha_abono  → PRÓXIMO (pago adelantado)
            //   prestamo_estado = 3            → VENCIDO (plazo terminado)
            $resumenDia = [
                'dia_recaudado'     => 0,
                'mora_recaudada'    => 0,
                'proximo_recaudado' => 0,
                'vencido_recaudado' => 0,
                'total_clientes'    => 0,
            ];
            $clientesUnicos = [];

            $abonoIdsRpt = $abonosDia->pluck('id')->toArray();
            $detallesPorAbonoRpt = \DB::table('prestamo_cuota_abono as pca')
                ->join('prestamo_coutas as pc', 'pc.id', '=', 'pca.prestamo_cuota_id')
                ->whereIn('pca.abono_id', $abonoIdsRpt)
                ->where('pca.estado', 1)
                ->select('pca.abono_id', 'pca.monto_abono', 'pc.fecha_cuota')
                ->get()
                ->groupBy('abono_id');

            foreach ($abonosDia as $abono) {
                $totalAbono = (float) ($abono->total_abonado ?? 0);
                $clientesUnicos[$abono->cliente_id] = true;

                if ($abono->prestamo_estado == 3 ||
                    ($abono->cuotas_futuras_pend == 0 && $abono->saldo_pendiente_prest > 0)) {
                    $resumenDia['vencido_recaudado'] += $totalAbono;
                } else {
                    $fechaAbono = substr($abono->fecha_abono, 0, 10);
                    $detalles   = $detallesPorAbonoRpt->get($abono->id, collect());

                    foreach ($detalles as $detalle) {
                        $montoCuota = (float) $detalle->monto_abono;
                        $fechaCuota = substr($detalle->fecha_cuota, 0, 10);

                        if ($fechaCuota < $fechaAbono) {
                            $resumenDia['mora_recaudada']    += $montoCuota;
                        } elseif ($fechaCuota > $fechaAbono) {
                            $resumenDia['proximo_recaudado'] += $montoCuota;
                        } else {
                            $resumenDia['dia_recaudado']     += $montoCuota;
                        }
                    }
                }
            }
            $resumenDia['total_clientes'] = count($clientesUnicos);
            $resumenDia['total_general']  = $resumenDia['dia_recaudado']
                                          + $resumenDia['mora_recaudada']
                                          + $resumenDia['proximo_recaudado']
                                          + $resumenDia['vencido_recaudado'];

            return view('reportes.detalleColocacion.detalleColocacionHTML', compact('abonosDia', 'resumen', 'resumenDia', 'desde', 'hasta', 'cobrador', 'tipo', 'tipo_vista', 'listaCobradores'));
        }

        return view('reportes.detalleColocacion.detalleColocacionIndex', compact('abonosDia', 'listaCobradores'));
    }

    public function cuotasVencidas(Request $request)
    {
        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $fecha_inicio = $request->desde;
        $fecha_corte = $request->hasta;
        $agente = $request->cobrador; // array o null

        $cuotasVencidas = \DB::table('prestamo_coutas as pc')
            ->join('prestamos as p', 'p.id', '=', 'pc.prestamo_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('users as a_u', 'p.agente_id', '=', 'a_u.id')
            // Subconsulta optimizada para traer el abono de cada cuota
            ->leftJoin(DB::raw('(SELECT prestamo_cuota_id, SUM(monto_abono) as suma_abono 
                                FROM prestamo_cuota_abono 
                                WHERE estado = 1 
                                GROUP BY prestamo_cuota_id) as t_abono'), 'pc.id', '=', 't_abono.prestamo_cuota_id')
            ->select('pc.*', 'p.consecutivo', 'p.moneda_prestamo', 'p.agente_id',
                \DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_nombre"),
                \DB::raw("CONCAT(a_u.nombres, ' ', a_u.apellidos) as agente_nombre"),
                \DB::raw("(CASE WHEN p.moneda_prestamo = 1 THEN 'C$' ELSE 'U$' END) as moneda"),
                't_abono.suma_abono'
            )
            ->when($fecha_corte, function ($query) use ($fecha_corte, $fecha_inicio) {
                $query->whereDate('pc.fecha_cuota', '<=', $fecha_corte)
                    ->whereDate('pc.fecha_cuota', '>=', $fecha_inicio);
            }, function ($query) {
                $query->whereRaw('pc.fecha_cuota < NOW()');
            })
            ->when($agente, function ($query) use ($agente) {
                $query->where('p.agente_id', decode($agente));
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('p.agente_id', $agentesAsignados);
            })
            ->when($cliente, function ($query) use ($cliente) {
                $query->where('p.user_id', decode($cliente));
            })
            ->where('p.estado', 1)
            ->whereNull('p.fecha_clasificacion')
            ->whereNull('p.deleted_at')
            ->where('pc.estado', 1)
            ->orderBy('a_u.nombres')
            ->orderBy('a_u.apellidos')
            ->orderBy('u.nombres')
            ->orderBy('u.apellidos')
            ->orderBy('pc.numero_cuota', 'asc');

        if ($request->pdf) {
            $cuotasVencidas = $cuotasVencidas->get();
            $pdf = Pdf::loadView('reportes.cuotasVencidas.listaCuotasVencidasPDF', compact('cuotasVencidas', 'fecha_corte'));
            $pdf->setPaper('letter', 'landscape');
            return $pdf->stream('Cuotas_Vencidas_'.date('Y-m-d').'.pdf');
        }
        if ($request->excel) {
            $cuotasVencidas = $cuotasVencidas->get();
            return Excel::download(new cuotasVencidasExport($cuotasVencidas), 'Cuotas_Vencidas.xlsx');
        }

        // Si tiene parámetros de filtro, mostrar vista HTML con datos
        if($fecha_inicio || $fecha_corte || $agente || $cliente) {
            $cuotasVencidas = $cuotasVencidas->get();
            $cobradorSel = $agente;
            $clienteSel = $cliente;
            $desde = $fecha_inicio;
            $hasta = $fecha_corte;
            return view('reportes.cuotasVencidas.listaCuotasVencidasHTML', compact('cuotasVencidas', 'desde', 'hasta', 'cobradorSel', 'clienteSel', 'listaCobradores', 'listaClientes'));
        }

        $cuotasVencidas = $cuotasVencidas->paginate(50);
        return view('reportes.cuotasVencidas.listaCuotasVencidas', compact('listaCobradores', 'cuotasVencidas','listaClientes'));
    }

    public function prestamosVencidos(Request $request)
    {
        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $cliente = $request->get('cliente');
        $cobrador = $request->get('cobrador');
        $frecuencia = $request->get('frecuencia');

        //todas las cuotas del prestamo tienen que estar vencidas para que retorne los datos
        // Optimización: Usar un join más directo para obtener la última fecha de cuota
        $prestamosVencidos = prestamosModel::with('cliente', 'agente')
            ->select('prestamos.*', 'ultimas_cuotas.ultima_fecha_vencimiento')
            ->join(DB::raw('(SELECT prestamo_id, MAX(fecha_cuota) as ultima_fecha_vencimiento 
                             FROM prestamo_coutas 
                             GROUP BY prestamo_id) as ultimas_cuotas'), 'prestamos.id', '=', 'ultimas_cuotas.prestamo_id')
            ->when($cliente, function ($query) use ($cliente) {
                $query->where('prestamos.user_id', decode($cliente));
            })
            ->when($cobrador, function ($query) use ($cobrador) {
                $ids = array_map('decode', (array)$cobrador);
                $query->whereIn('prestamos.agente_id', $ids);
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('prestamos.agente_id', $agentesAsignados);
            })
            ->when($frecuencia, function ($query) use ($frecuencia) {
                $query->where('prestamos.forma_pago_tipo', $frecuencia);
            })
            ->where('prestamos.estado', 1) // Activo
            ->whereNull('prestamos.fecha_clasificacion')
            ->where('ultimas_cuotas.ultima_fecha_vencimiento', '<=', Carbon::now());

        if ($request->get('pdf')) {
            $prestamosVencidos = $prestamosVencidos->get();
            $pdf = Pdf::loadView('reportes.prestamosVencidos.prestamosVencidosPDF', compact('prestamosVencidos'));
            $pdf->setPaper('letter', 'landscape');
            return $pdf->stream('Prestamos_Vencidos_'.date('Y-m-d').'.pdf');
        }
        if ($request->get('excel')) {
            $prestamosVencidos = $prestamosVencidos->get();
            return Excel::download(new prestamosVencidosExport($prestamosVencidos), 'Préstamos Vencidos.xlsx');
        }
        
        // Si tiene parámetros de filtro, mostrar vista HTML con datos
        if($cliente || $cobrador || $frecuencia) {
            $prestamosVencidos = $prestamosVencidos->get();
            return view('reportes.prestamosVencidos.prestamosVencidosHTML', compact('prestamosVencidos', 'cliente', 'cobrador', 'frecuencia', 'listaClientes', 'listaCobradores'));
        }

        $prestamosVencidos = $prestamosVencidos->paginate(50);
        return view('reportes.prestamosVencidos.prestamosVencidosIndex', compact('prestamosVencidos', 'listaClientes', 'listaCobradores'));
    }

    public function creditosVencidos(Request $request)
    {
        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $desde = $request->desde;
        $hasta = $request->hasta;
        $cobrador = $request->cobrador;

        $prestamos = \DB::table('prestamos as p')
            ->join('users as u', 'p.user_id', '=', 'u.id')
            ->leftJoin('users as a_u', 'p.agente_id', '=', 'a_u.id')
            ->leftJoin(\DB::raw('(SELECT prestamo_id, MAX(fecha_cuota) as fecha_vencimiento FROM prestamo_coutas GROUP BY prestamo_id) as v'), 'p.id', '=', 'v.prestamo_id')
            ->leftJoin(\DB::raw('(SELECT prestamo_id, MIN(fecha_cuota) as fecha_mora FROM prestamo_coutas WHERE estado IN (1, 2) GROUP BY prestamo_id) as m'), 'p.id', '=', 'm.prestamo_id')
            ->leftJoin(\DB::raw('(SELECT prestamo_id, SUM(monto_cuota) as total_esperado, SUM(monto_interes) as total_interes 
                                 FROM prestamo_coutas 
                                 WHERE estado != 4 
                                 GROUP BY prestamo_id) as s'), 'p.id', '=', 's.prestamo_id')
            ->leftJoin(\DB::raw('(SELECT pc.prestamo_id, SUM(pca.monto_abono) as total_abonado 
                                 FROM prestamo_cuota_abono pca 
                                 JOIN prestamo_coutas pc ON pc.id = pca.prestamo_cuota_id 
                                 WHERE pca.estado = 1 
                                 GROUP BY pc.prestamo_id) as t_abono'), 'p.id', '=', 't_abono.prestamo_id')
            ->leftJoin(\DB::raw('(SELECT pc2.prestamo_id,
                                    SUM(pc2.monto_cuota) as total_cuotas_vencidas,
                                    COALESCE((SELECT SUM(pca2.monto_abono)
                                              FROM prestamo_cuota_abono pca2
                                              JOIN prestamo_coutas pc3 ON pc3.id = pca2.prestamo_cuota_id
                                              WHERE pc3.prestamo_id = pc2.prestamo_id
                                                AND pc3.estado IN (1,2)
                                                AND pc3.fecha_cuota < CURDATE()
                                                AND pca2.estado = 1), 0) as total_abonado_vencidas
                                 FROM prestamo_coutas pc2
                                 WHERE pc2.estado IN (1,2)
                                   AND pc2.fecha_cuota < CURDATE()
                                 GROUP BY pc2.prestamo_id) as t_mora'), 'p.id', '=', 't_mora.prestamo_id')
            ->select('p.*', 'v.fecha_vencimiento', 'm.fecha_mora',
                \DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_nombre"),
                \DB::raw("CONCAT(a_u.nombres, ' ', a_u.apellidos) as agente_nombre"),
                's.total_interes as suma_interes',
                's.total_esperado as suma_cuotas',
                \DB::raw("(COALESCE(s.total_esperado, 0) - COALESCE(t_abono.total_abonado, 0)) as saldo_pendiente"),
                \DB::raw("GREATEST(0, COALESCE(t_mora.total_cuotas_vencidas, 0) - COALESCE(t_mora.total_abonado_vencidas, 0)) as monto_atrasado")
            )
            ->whereIn('p.estado', [1, 3])
            ->whereNull('p.deleted_at')
            ->whereRaw("(COALESCE(s.total_esperado, 0) - COALESCE(t_abono.total_abonado, 0)) > 0.5")
            ->when($cobrador, function ($query) use ($cobrador) {
                $ids = array_map('decode', (array)$cobrador);
                $query->whereIn('p.agente_id', $ids);
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('p.agente_id', $agentesAsignados);
            })
            ->when($desde && $hasta, function ($query) use ($desde, $hasta) {
                $query->whereBetween('v.fecha_vencimiento', [$desde, $hasta]);
            })
            ->get();

        $fechaCalculo = $hasta ? Carbon::parse($hasta)->endOfDay() : now();
        $prestamosResult = $prestamos->map(function($p) use ($fechaCalculo) {
            $p->dias_vencido = 0;
            $p->dias_mora = 0;

            $fechaVencimiento = Carbon::parse($p->fecha_vencimiento)->startOfDay();
            if ($p->fecha_vencimiento && $fechaCalculo->startOfDay() > $fechaVencimiento) {
                $p->dias_vencido = $fechaCalculo->startOfDay()->diffInDays($fechaVencimiento);
            } elseif ($p->fecha_mora) {
                $fechaMora = Carbon::parse($p->fecha_mora)->startOfDay();
                if ($fechaCalculo->startOfDay() > $fechaMora) {
                    $p->dias_mora = $fechaCalculo->startOfDay()->diffInDays($fechaMora);
                }
            }

            // Promedio de días de atraso (igual que estado de cuenta)
            $cuotas = \App\Models\prestamoCuotasModel::where('prestamo_id', $p->id)->get();
            $totalDiasAtraso = 0;
            $totalCuotas = $cuotas->count();
            foreach ($cuotas as $cuota) {
                $fechaPlan = Carbon::parse($cuota->fecha_cuota);
                if ($cuota->estado == 3) {
                    $ultimoAbono = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuota->id)
                        ->where('estado', 1)->orderBy('created_at', 'desc')->first();
                    if ($ultimoAbono) {
                        $dias = $fechaPlan->diffInDays(Carbon::parse($ultimoAbono->created_at), false);
                        if ($dias > 0) $totalDiasAtraso += $dias;
                    }
                } elseif (in_array($cuota->estado, [1, 2]) && $fechaPlan->isPast()) {
                    $dias = $fechaPlan->diffInDays(Carbon::now(), false);
                    if ($dias > 0) $totalDiasAtraso += $dias;
                }
            }
            $p->promedio_dias_atraso = $totalCuotas > 0 ? round($totalDiasAtraso / $totalCuotas, 2) : 0;

            return $p;
        });

        if ($request->route()->getName() == 'reportes.creditosVencidos.html') {
            $prestamosGrouped = $prestamosResult->groupBy('agente_id');
            return view('reportes.creditosVencidos.creditosVencidosHTML', compact('prestamosGrouped', 'desde', 'hasta', 'cobrador', 'listaCobradores'));
        }

        return view('reportes.creditosVencidos.creditosVencidosIndex', compact('listaCobradores'));
    }

    public function listaArqueo(Request $request)
    {
        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $fecha = $request->get('fecha');
        $cobrador = $request->get('cobrador');

        if(!$fecha)
            $fecha = Carbon::now()->toDateString();
        $arqueos = arqueoModel::when($fecha, function ($query) use ($fecha) {
            $query->whereDate('fecha_arqueo', $fecha);
        }, function ($query) {
            $query->whereDate('fecha_arqueo', Carbon::now()->toDateString());
        })->when($cobrador, function ($query) use ($cobrador) {
            $query->where('cobrador_id', decode($cobrador));
        })->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
            $query->whereIn('cobrador_id', $agentesAsignados);
        })
            ->paginate(50);
        return view('reportes.arqueo.listaArqueo',compact('arqueos','listaCobradores','fecha'));
    }

    public function arqueo(Request $request, $id)
    {
        $abonos = [];
        $total_recuperado = 0;

        $arqueo = arqueoModel::where('id', decode($id))->first();
        $abonos = abonosModel::whereDate('fecha_abono', $arqueo->fecha_arqueo)
            ->where('created_user_id', $arqueo->cobrador_id)
            ->where('estado', 1)
            ->with(['prestamo', 'prestamo.cliente'])
            ->get();

        if ($abonos) {
            $total_recuperado = 0;
            foreach ($abonos as $ab) {
                $total_recuperado += $ab->total_abonado;
            }
        }

        return view('reportes.arqueo.arqueo', compact('arqueo','abonos','total_recuperado'));
    }

    public function exportarArqueo($arqueoId)
    {
        if ($arqueoId) {
            $arqueo = arqueoModel::where('id', decode($arqueoId))->first();
            return Excel::download(new arqueoExport($arqueo), 'Arqueo.xlsx');
        }
    }

    public function eliminarArqueo($id)
    {
        $arqueo = arqueoModel::where('id', decode($id))->firstOrFail();
        $arqueo->delete();
        return redirect()->route('reportes.arqueo.list')->with('success', 'Arqueo eliminado correctamente');
    }

    public function nuevoArqueo(Request $request)
    {
        $cobrador = $request->get('cobrador');
        $fecha = $request->get('fecha');
        $abonos = [];
        $listaCobradores = User::agente()->activo()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $total_recuperado = 0;
        if ($cobrador) {
            $abonos = abonosModel::whereDate('fecha_abono', $fecha)
                ->where('created_user_id', decode($cobrador))
                ->where('estado', 1)
                ->with(['prestamo', 'prestamo.cliente'])
                ->get();

            if ($abonos) {
                $total_recuperado = 0;
                foreach ($abonos as $ab) {
                    $total_recuperado += $ab->total_abonado;
                }
            }
        }
        $arqueo = null;
        return view('reportes.arqueo.nuevoArqueo',compact('arqueo','abonos','total_recuperado','listaCobradores'));
    }

    public function storeArqueo(Request $request)
    {
        $fecha = $request->fecha;
        $efectivo = $request->efectivo;
        $tipoCambio = $request->tipocambio;
        if (!$fecha)
            return redirect()->back()->with('warning', 'El campo fecha es requerido');

        if ($arqueo = arqueoModel::where('cobrador_id', decode($request->get('cobrador')))->where('fecha_arqueo', $fecha)->exists()) {
            return redirect()->back()->with('warning', 'No se pudo guardar el arqueo debudo a que ya existe un arqueo de este cobrador en la fecha ingresada');
        }
        $arqueo = new arqueoModel($request->all());

        $arqueo->estado = 1; //1:solo guardar, 2:guadar y finalizar
        $arqueo->fecha_arqueo = $fecha;
        $arqueo->efectivo = $efectivo;
        $arqueo->tipocambio = $tipoCambio;
        $arqueo->cobrador_id = decode($request->get('cobrador'));
        $arqueo->created_user_id = userLogeado()->id;
        $arqueo->desembolsos = $request->get('desembolsos') ?? 0;

        $arreglo_montos_cordobas = [
            'moneda_c_050' => 0.50,
            'moneda_c_1' => 1,
            'moneda_c_5' => 5,
            'billete_c_5' => 5,
            'billete_c_10' => 10,
            'billete_c_20' => 20,
            'billete_c_50' => 50,
            'billete_c_100' => 100,
            'billete_c_200' => 200,
            'billete_c_500' => 500,
            'billete_c_1000' => 1000,
        ];

        $arreglo_montos_dolares = [
            'billete_d_1' => 1,
            'billete_d_2' => 2,
            'billete_d_5' => 5,
            'billete_d_10' => 10,
            'billete_d_20' => 20,
            'billete_d_50' => 50,
            'billete_d_100' => 100,
        ];

        $totalCordoba = 0;
        $totalDolar = 0;

        $valores = $request->all();
        foreach ($valores as $ind => $monedas) {
            $totalCordoba += isset($arreglo_montos_cordobas[$ind]) ? $arreglo_montos_cordobas[$ind] * $monedas : 0;
            $totalDolar += isset($arreglo_montos_dolares[$ind]) ? $arreglo_montos_dolares[$ind] * $monedas : 0;
        }

        $arqueo->total_dolar = $totalDolar;
        $arqueo->total_cordoba = $totalCordoba;

        if ($arqueo->save()) {
            return redirect()->route('reportes.arqueo.list');
        }
        return redirect()->back()->with('error', 'Ha ocurrido un error al intentar guardar el arqueo');
    }

    public function saveOrUpdateAquero(Request $request)
    {
        $fecha = $request->fecha;
        $efectivo = $request->efectivo;
        $tipoCambio = $request->tipocambio;

        if (!$fecha)
            return redirect()->back()->with('warning', 'El campo fecha es requerido');

//        if ($fecha != date('Y-m-d'))
//            return redirect()->back()->with('warning', 'No se puede crear/modificar arqueo de una fecha inferior o posterior a la actual');

        $tipo_guardado = 1;
        if ($arqueo = arqueoModel::where('cobrador_id', decode($request->get('cobrador')))->where('fecha_arqueo', $fecha)->first()) {
            $arqueo->fill($request->all());
            $arqueo->updated_user_id = userLogeado()->id;
        } else {
            $arqueo = new arqueoModel($request->all());
        }

        $arqueo->estado = $tipo_guardado; //1:solo guardar, 2:guadar y finalizar
        $arqueo->fecha_arqueo = $fecha;
        $arqueo->efectivo = $efectivo;
        $arqueo->tipocambio = $tipoCambio;
        $arqueo->cobrador_id = decode($request->get('cobrador'));
        $arqueo->created_user_id = userLogeado()->id;
        $arqueo->desembolsos = $request->get('desembolsos') ?? 0;

        $arreglo_montos_cordobas = [
            'moneda_c_050' => 0.50,
            'moneda_c_1' => 1,
            'moneda_c_5' => 5,
            'billete_c_5' => 5,
            'billete_c_10' => 10,
            'billete_c_20' => 20,
            'billete_c_50' => 50,
            'billete_c_100' => 100,
            'billete_c_200' => 200,
            'billete_c_500' => 500,
            'billete_c_1000' => 1000,
        ];

        $arreglo_montos_dolares = [
            'billete_d_1' => 1,
            'billete_d_2' => 2,
            'billete_d_5' => 5,
            'billete_d_10' => 10,
            'billete_d_20' => 20,
            'billete_d_50' => 50,
            'billete_d_100' => 100,
        ];

        $totalCordoba = 0;
        $totalDolar = 0;

        $valores = $request->all();
        foreach ($valores as $ind => $monedas) {
            $totalCordoba += isset($arreglo_montos_cordobas[$ind]) ? $arreglo_montos_cordobas[$ind] * $monedas : 0;
            $totalDolar += isset($arreglo_montos_dolares[$ind]) ? $arreglo_montos_dolares[$ind] * $monedas : 0;
        }

        $arqueo->total_dolar = $totalDolar;
        $arqueo->total_cordoba = $totalCordoba;

        if ($arqueo->save()) {
            return redirect()->back()->with('success', 'Arqueo Guardado correctamente');
        }
        return redirect()->back()->with('error', 'Ha ocurrido un error al intentar guardar el arqueo');
    }

    public function arqueoDetalleCuotas($fecha,$cobrador,$arq)
    {
        $cobradorObj = User::where('id', decode($cobrador))->first();
        $abonos = abonosModel::whereDate('fecha_abono', $fecha)
            ->where('created_user_id', decode($cobrador))
            ->where('estado', 1)
            ->get();

        $arqueo = arqueoModel::where('id',decode($arq))->first();

        return view('reportes.arqueo.arqueoCuotas', compact('fecha', 'cobradorObj', 'abonos','arqueo'));
    }

    public function asignacionClientes(Request $request)
    {
        $cliente = $request->cliente;
        $cobrador = $request->cobrador;

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $prestamos = prestamosModel::with('cliente','agente')->orderBy('fecha_prestamo')
            ->when($cliente, function ($query) use ($cliente) {
                $query->where('user_id', decode($cliente));
            })
            ->when($cobrador, function ($query) use ($cobrador) {
                $query->where('agente_id', decode($cobrador));
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('agente_id', $agentesAsignados);
            })
            ->where('desembolsado', 1)
            ->where('estado', 1);


        if ($request->pdf) {
            $prestamos = $prestamos->get();
            $pdf = Pdf::loadView('reportes.asignacionClientes.asignacionClientesPDF', compact('prestamos'));
            $pdf->setPaper('letter', 'landscape');
            return $pdf->stream('Asignacion_Clientes_'.date('Y-m-d').'.pdf');
        }
        if ($request->excel) {
            $prestamos = $prestamos->get();
            return Excel::download(new asignacionClientesExport($prestamos), 'Asignación de Clientes.xlsx');
        }

        // Si tiene parámetros de filtro, mostrar vista HTML con datos
        if($cliente || $cobrador) {
            $prestamos = $prestamos->get();
            return view('reportes.asignacionClientes.asignacionClientesHTML', compact('prestamos', 'cliente', 'cobrador', 'listaClientes', 'listaCobradores'));
        }

        $prestamos = $prestamos->paginate(50);

        return view('reportes.asignacionClientes.asignacionClientes', compact('listaCobradores', 'listaClientes', 'prestamos'));
    }

    public function transferirCarteraCompleta(Request $request)
    {
        $clienteIdDecoded = $request->cliente_id ? decode($request->cliente_id) : null;
        
        $request->validate([
            'cobrador_origen' => $clienteIdDecoded ? 'nullable' : 'required',
            'cobrador_destino' => 'required'
        ]);

        $cobradorDestinoId = decode($request->cobrador_destino);
        $cobradorOrigenId = $request->cobrador_origen ? decode($request->cobrador_origen) : null;

        if ($cobradorOrigenId && $cobradorOrigenId == $cobradorDestinoId) {
            return redirect()->route('reportes.index')->with('error', 'El cobrador destino debe ser diferente al cobrador origen.');
        }

        // Obtener préstamos activos (filtrar por cliente si existe, sino por cobrador)
        $query = prestamosModel::where('estado', 1); // Solo préstamos activos

        if ($clienteIdDecoded) {
            $query->where('user_id', $clienteIdDecoded);
        } else {
            $query->where('agente_id', $cobradorOrigenId);
        }

        $prestamosActivos = $query->get();

        if ($prestamosActivos->isEmpty()) {
            $msg = $clienteIdDecoded ? 'El cliente seleccionado no tiene préstamos activos.' : 'El cobrador origen no tiene préstamos activos.';
            return redirect()->route('reportes.index')->with('error', $msg);
        }

        $cantidadTransferida = 0;
        foreach ($prestamosActivos as $prestamo) {
            $prestamo->agente_id = $cobradorDestinoId;
            $prestamo->save();
            $cantidadTransferida++;
        }

        $cobradorDestino = User::find($cobradorDestinoId);
        $mensaje = "✅ Transferencia exitosa: Se movieron {$cantidadTransferida} préstamo(s) a {$cobradorDestino->full_name}.";
        
        if ($clienteIdDecoded) {
            $cliente = User::find($clienteIdDecoded);
            $mensaje = "✅ Transferencia exitosa: El cliente {$cliente->full_name} fue asignado a {$cobradorDestino->full_name}.";
        }

        return redirect()->route('reportes.index')->with('success', $mensaje);
    }

    public function cobrosDia(Request $request)
    {
        $cobrador = $request->cobrador;
        $fecha = $request->fecha;
        $fecha2 = $request->fecha2;
        if (!$fecha) {
            $fecha = date('Y-m-d');

            $fecha2 = date('Y-m-d');
        }

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $prestamoCuotas = \DB::table('prestamo_coutas as pc')
            ->join('prestamos as p', 'p.id', '=', 'pc.prestamo_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('departamento_municipio as dm', 'u.dep_mun', '=', 'dm.id')
            ->leftJoin('departamentos as d', 'dm.departamento_id', '=', 'd.id')
            ->leftJoin('users as a_u', 'p.agente_id', '=', 'a_u.id')
            ->select('pc.*', 'p.consecutivo', 'p.monto_prestamo', 'p.monto_cuota as m_cuota',
                \DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_full_name"),
                'u.direccion', 'u.telefono1', 'u.telefono2',
                'd.nombre as departamento_nombre', 'dm.nombre as municipio_nombre',
                'a_u.username as agente_username'
            )
            ->whereBetween('pc.fecha_cuota', [$fecha, $fecha2])
            ->where('p.estado', 1) // Solo préstamos activos
            ->where('pc.estado', 1) // Solo cuotas pendientes
            ->whereNull('p.deleted_at')
            ->when($cobrador, function ($query) use ($cobrador) {
                $query->where('p.agente_id', decode($cobrador));
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('p.agente_id', $agentesAsignados);
            })
            ->orderBy('a_u.id')
            ->orderBy('pc.fecha_cuota')
            ->get();

        if ($request->pdf) {
            $pdf = Pdf::loadView('reportes.cobrosDias.cobrosDiaPDF', compact('prestamoCuotas'));
            $pdf->setPaper('letter', 'landscape');
            return $pdf->stream('Cobros_del_Dia_'.date('Y-m-d').'.pdf');
        }
        if ($request->excel) {
            return Excel::download(new cobrosDiaExport($prestamoCuotas), 'Cobros del Día.xlsx');
        }

        // Si tiene parámetros de filtro, mostrar vista HTML con datos
        if($fecha || $cobrador) {
            return view('reportes.cobrosDias.cobrosDiaHTML', compact('prestamoCuotas', 'fecha', 'fecha2', 'cobrador', 'listaCobradores'));
        }

        $prestamoCuotas = $prestamoCuotas->paginate(50);
        return view('reportes.cobrosDias.cobrosDia', compact('prestamoCuotas', 'listaCobradores'));
    }

    public function desembolsos(Request $request)
    {
        $listaCobradores = User::agente()->activo()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $desembolsos = prestamosModel::paginate(50);

        return view('reportes.desembolsos.rptDesembolsos', compact('listaCobradores', compact('desembolsos')));
    }

    public function colocacionV2(Request $request)
    {
        $meses = [
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

        $fechaInicio = Carbon::parse('2023-01-01');
        $fechaFin = Carbon::parse(Carbon::now());

        $aniosEnRango = $fechaInicio->diffInYears($fechaFin);

        $anios = [];
        for ($i = 0; $i <= $aniosEnRango; $i++) {
            $anios[$fechaInicio->copy()->addYears($i)->year] = $fechaInicio->copy()->addYears($i)->year;
        }

        $mesSeleccionado = $request->mes;
        $anyoSeleccionado = $request->anio;

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        if ($mesSeleccionado) {
            $prestamos = prestamosModel::whereMonth('fecha_desembolso', $mesSeleccionado)
                ->whereYear('fecha_desembolso', $anyoSeleccionado)
                ->where('desembolsado', 1)
                ->get();

            return Excel::download(new detalleColocacionV2Export($prestamos, $mesSeleccionado, $anyoSeleccionado), 'Detalle Colocacion V2.xlsx');
        }

        return view('reportes.colocacionV2.rptColocacionV2', compact('listaCobradores', 'meses', 'anios'));

    }

    public function cobranza(Request $request)
    {
        $inicio = $request->get('fecha_inicio');
        $fin = $request->get('fecha_fin');
        $cobrador = $request->get('cobrador');
        $cuotas = [];

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        if ($inicio && $fin) {

            $cuotas = prestamoCuotasModel::with('prestamo','prestamo.cliente','prestamo.agente')->join('prestamos as P', 'P.id', 'prestamo_coutas.prestamo_id')
                ->where('P.desembolsado', 1)
                ->whereDate('prestamo_coutas.fecha_cuota', '>=', $inicio)
                ->whereDate('prestamo_coutas.fecha_cuota', '<=', $fin)
                ->where('P.estado', '!=', 4)//anulado
                ->whereIn('prestamo_coutas.estado', [1, 2])//activas y vencidas
                ->when($cobrador, function ($query) use ($cobrador) {
                    $query->where('P.agente_id', decode($cobrador));
                })
                ->select('prestamo_coutas.*')
                ->orderBy('prestamo_coutas.fecha_cuota', 'desc');

            if ($request->get('pdf')) {
                $cuotas = $cuotas->get();
                $pdf = Pdf::loadView('reportes.cobranza.cobranzaPDF', compact('cuotas'));
                $pdf->setPaper('letter', 'landscape');
                return $pdf->stream('Cobranza_'.date('Y-m-d').'.pdf');
            }
            if ($request->get('excel')) {
                $cuotas = $cuotas->get();
                return Excel::download(new cobranzaExport($cuotas), 'Control y Gestión de Cobranza.xlsx');
            }
            
            // Si tiene parámetros de filtro, mostrar vista HTML con datos
            $cuotas = $cuotas->get();
            return view('reportes.cobranza.rptCobranzaHTML', compact('cuotas', 'inicio', 'fin', 'cobrador', 'listaCobradores'));
        }

        return view('reportes.cobranza.rptCobranza', compact('cuotas', 'listaCobradores'));
    }


//    public function saldoCartera(Request $request)
//    {
//        $cobrador = $request->get('cobrador');
//        $listaCobradores = User::agente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
//
//        $fechaInicio = $request->get('inicio');
//        $fechaFin = $request->get('fin');
//
//        $prestamos = array();
//        if ($fechaInicio && $fechaFin) {
//
//            $prestamos = prestamosModel::when($cobrador, function ($query) use ($cobrador) {
//                $query->where('agente_id', decode($cobrador));
//            })->whereDate('fecha_prestamo', '>=', $fechaInicio)
//                ->whereDate('fecha_prestamo', '<=', $fechaFin)
//                ->get();
//
//            return Excel::download(new saldoCarteraExport($prestamos,$fechaInicio,$fechaFin), 'Control y Gestión de Cobranza.xlsx');
//        }
//
//        return view('reportes.saldoCartera.saldoCartera', compact('listaCobradores', 'prestamos'));
//    }

    public function saldoCartera(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $clienteSel = $request->cliente;
        $cobradorSel = $request->cobrador;
        $frecuenciaSel = $request->frecuencia;
        $tipo_vista = $request->tipo_vista ?? 'detallado'; // detallado | resumido

        $inicio = $request->inicio;
        $fin = $request->fin;
        $estado = $request->estado;

        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $meses = [
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

        $prestamos = [];
        $resumen = [];

        if($fin)
        {
            $query = \DB::table('prestamos as p')
                ->select('p.*', 'p.plazo_pago as plazo',
                    \DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_nombre"), 
                    \DB::raw("CONCAT(a_u.nombres, ' ', a_u.apellidos) as agente_nombre"),
                    \DB::raw("(CASE WHEN p.moneda_prestamo = 1 THEN 'C$' ELSE 'U$' END) as moneda"),
                    \DB::raw("(CASE 
                        WHEN p.estado = 1 THEN 'Activo'
                        WHEN p.estado = 2 THEN 'Cancelado'
                        WHEN p.estado = 3 THEN 'Vencido'
                        WHEN p.estado = 4 THEN 'Anulado'
                        ELSE 'N/D'
                    END) as estado_prestamo"),
                    \DB::raw("(CASE 
                        WHEN p.tipo_desembolso = 1 THEN 'Nuevo'
                        WHEN p.tipo_desembolso = 2 THEN 'Represtamo'
                        WHEN p.tipo_desembolso = 3 THEN 'Reactivación'
                        WHEN p.tipo_desembolso = 4 THEN 'Reestructuración'
                        ELSE 'N/E'
                    END) as tipo_prestamo"),
                    \DB::raw("(CASE 
                        WHEN p.forma_pago_tipo = 1 THEN 'Diario'
                        WHEN p.forma_pago_tipo = 2 THEN 'Semanal'
                        WHEN p.forma_pago_tipo = 3 THEN 'Quincenal'
                        WHEN p.forma_pago_tipo = 4 THEN 'Mensual'
                        WHEN p.forma_pago_tipo = 5 THEN 'Trimestral'
                        WHEN p.forma_pago_tipo = 6 THEN 'Bimestral'
                        WHEN p.forma_pago_tipo = 7 THEN 'Catorcenal'
                        ELSE 'Desconocido'
                    END) as forma_pago"),
                    \DB::raw('COALESCE(cuotas_agg.suma_cuotas, 0) as suma_cuotas'),
                    \DB::raw('COALESCE(abonos_agg.suma_abonos, 0) as suma_abonos'),
                    \DB::raw('COALESCE(abonos_agg.suma_abonos_capital, 0) as suma_abonos_capital'),
                    \DB::raw('COALESCE(abonos_agg.suma_abonos_interes, 0) as suma_abonos_interes')
                )
                ->join('users as u', 'u.id', '=', 'p.user_id')
                ->leftJoin('users as a_u', 'a_u.id', '=', 'p.agente_id')
                ->leftJoin(\DB::raw('(
                    SELECT prestamo_id,
                           SUM(monto_cuota) as suma_cuotas
                    FROM prestamo_coutas
                    WHERE estado != 3
                      AND fecha_cuota <= "' . $fin . '"
                    GROUP BY prestamo_id
                ) as cuotas_agg'), 'cuotas_agg.prestamo_id', '=', 'p.id')
                ->leftJoin(\DB::raw('(
                    SELECT a.prestamo_id,
                           SUM(pca.monto_abono)   as suma_abonos,
                           SUM(pca.total_capital) as suma_abonos_capital,
                           SUM(pca.total_interes) as suma_abonos_interes
                    FROM prestamo_cuota_abono pca
                    JOIN abonos a ON a.id = pca.abono_id
                    WHERE pca.estado = 1
                      AND a.estado = 1
                      AND a.fecha_abono <= "' . $fin . '"
                    GROUP BY a.prestamo_id
                ) as abonos_agg'), 'abonos_agg.prestamo_id', '=', 'p.id')
                ->when($clienteSel, function ($query) use ($clienteSel) {
                    $query->where('p.user_id', decode($clienteSel));
                })
                ->when($cobradorSel, function ($query) use ($cobradorSel) {
                    $ids = array_map('decode', (array)$cobradorSel);
                    $query->whereIn('p.agente_id', $ids);
                })
                ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                    $query->whereIn('p.agente_id', $agentesAsignados);
                })
                ->when($frecuenciaSel, function ($query) use ($frecuenciaSel) {
                    $query->where('p.forma_pago_tipo', $frecuenciaSel);
                })
                ->when($estado == 5, function ($query) {
                    $query->whereNotNull('p.motivo_clasificacion');
                })
                ->when($estado && $estado != 5, function ($query) use ($estado) {
                    $query->where('p.estado', $estado);
                })
                ->when(!$estado, function ($query) {
                    $query->whereNotIn('p.estado', [4]);
                })
                ->where('p.desembolsado', 1)
                ->whereNull('p.deleted_at')
                ->orderBy('a_u.nombres') // Orden por nombres del cobrador
                ->orderBy('a_u.apellidos') 
                ->orderBy('u.nombres');  // Orden por nombres del cliente

            // MODO RESUMIDO: Agrupamos en SQL para que sea instantáneo
            if ($tipo_vista == 'resumido' && !$request->pdf && !$request->excel) {
                $resumen = \DB::select("
                    SELECT 
                        agente_id,
                        COUNT(*) as clientes,
                        SUM(monto_prestamo - suma_abonos_capital) as capital,
                        SUM((monto_financiado - monto_prestamo) - suma_abonos_interes) as interes,
                        SUM((monto_prestamo - suma_abonos_capital) + ((monto_financiado - monto_prestamo) - suma_abonos_interes)) as total
                    FROM ({$query->toSql()}) as sub
                    GROUP BY agente_id
                ", $query->getBindings());
                
                // Mapear nombres de agentes
                foreach($resumen as $r) {
                    $r->nombre = $listaCobradores[encode($r->agente_id)] ?? 'Sin asignar';
                }
            } else {
                $prestamos = $query->get();
            }

            if ($request->pdf) {
                $pdf = Pdf::loadView('reportes.saldoCartera.saldoCarteraPDF', compact('prestamos'));
                $pdf->setPaper('letter', 'landscape');
                return $pdf->stream('Saldo_Cartera_'.date('Y-m-d').'.pdf');
            }
            if ($request->excel) {
                return Excel::download(new saldoCarteraExport($prestamos, date('m', strtotime($inicio)), date('Y', strtotime($inicio)),$fin), 'Saldo de Cartera.xlsx');
            }

            return view('reportes.saldoCartera.saldoCarteraHTML',
                compact('prestamos', 'resumen', 'clienteSel', 'cobradorSel', 'frecuenciaSel',
                        'estado', 'fin', 'tipo_vista', 'listaClientes', 'listaCobradores'));
        }

        // Formulario con filtros (dentro del layout del sistema)
        return view('reportes.saldoCartera.saldoCartera', compact('listaCobradores', 'meses', 'listaClientes', 'prestamos'));
    }

    public function estadoClientes(Request $request)
    {
        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $listaCobradores = User::agente()->activo()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $estado = $request->get('estado');
        $cliente = $request->get('cliente');
        $cobrador = $request->get('cobrador');
        
        $clientes = User::cliente()
            ->withCount(['prestamos as prestamos_activos_count' => function ($query) {
                $query->where('estado', 1)->where('desembolsado', 1);
            }])
            ->with(['prestamos' => function ($query) {
                $query->where('desembolsado', 1)
                    ->select('id', 'user_id', 'monto_prestamo', 'monto_financiado', 'moneda_prestamo', 'agente_id', 'plazo_pago', 'forma_pago_tipo', 'created_at', 'updated_at', 'estado')
                    ->orderBy('created_at', 'desc');
            }])
            ->when($estado == 1, function ($query) {
                $query->has('prestamos', '>=', 1, 'and', function ($query) {
                    $query->where('estado', 1)->where('desembolsado', 1);
                });
            })
            ->when($estado == 2, function ($query) {
                $query->whereDoesntHave('prestamos', function ($query) {
                    $query->where('estado', 1)->where('desembolsado', 1);
                });
            })
            ->when($cliente, function ($query) use ($cliente) {
                $query->where('id', decode($cliente));
            })
            ->when($cobrador, function ($query) use ($cobrador) {
                $ids = array_map('decode', (array)$cobrador);
                $query->whereHas('prestamos', function ($query) use ($ids) {
                    $query->whereIn('agente_id', $ids)->where('desembolsado', 1);
                });
            })
            ->orderBy('nombres')
            ->orderBy('apellidos');

        // Si tiene parámetros de filtro, mostrar vista HTML con datos
        if($estado || $cliente || $cobrador) {
            $clientes = $clientes->get();

            if ($estado == 2) {
                foreach ($clientes as $clienteItem) {
                    $ultimoPrestamo = $clienteItem->prestamos->first();
                    $promedioDiasAtraso = 0;
                    $agenteNombre = 'Sin Agente';

                    if ($ultimoPrestamo) {
                        $ultimoPrestamo->load('agente', 'cuotas');
                        if ($ultimoPrestamo->agente) {
                            $agenteNombre = $ultimoPrestamo->agente->full_name;
                        }

                        $ultimoPrestamo->fecha_vencimiento = $ultimoPrestamo->cuotas->max('fecha_cuota');

                        $ultimoPrestamo->fecha_cancelacion = \DB::table('abonos')
                            ->where('prestamo_id', $ultimoPrestamo->id)
                            ->where('estado', 1)
                            ->max('fecha_abono');

                        $totalDiasAtraso = 0;
                        $totalCuotas = $ultimoPrestamo->cuotas->count();
                        if ($totalCuotas > 0) {
                            foreach ($ultimoPrestamo->cuotas as $cuota) {
                                $fechaPlanCuota = \Carbon\Carbon::parse($cuota->fecha_cuota);
                                
                                if ($cuota->estado == 3) {
                                    $ultimoAbono = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuota->id)
                                        ->where('estado', 1)
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                                        
                                    if ($ultimoAbono) {
                                        $fechaPagoReal = \Carbon\Carbon::parse($ultimoAbono->created_at);
                                        $diasAtraso = $fechaPlanCuota->diffInDays($fechaPagoReal, false);
                                        if ($diasAtraso > 0) {
                                            $totalDiasAtraso += $diasAtraso;
                                        }
                                    }
                                } elseif (in_array($cuota->estado, [1, 2]) && $fechaPlanCuota->isPast()) {
                                    $diasAtraso = $fechaPlanCuota->diffInDays(\Carbon\Carbon::now(), false);
                                    if ($diasAtraso > 0) {
                                        $totalDiasAtraso += $diasAtraso;
                                    }
                                }
                            }
                            $promedioDiasAtraso = round($totalDiasAtraso / $totalCuotas, 2);
                        }
                    }
                    $clienteItem->promedio_dias_atraso = $promedioDiasAtraso;
                    $clienteItem->agente_nombre = $agenteNombre;
                }
                
                $clientesAgrupados = $clientes->groupBy('agente_nombre');
                return view('reportes.estadoClientes.estadoClientesHTML', compact('clientes', 'clientesAgrupados', 'estado', 'cliente', 'cobrador', 'listaClientes', 'listaCobradores'));
            }

            return view('reportes.estadoClientes.estadoClientesHTML', compact('clientes', 'estado', 'cliente', 'cobrador', 'listaClientes', 'listaCobradores'));
        }

        $clientes = $clientes->paginate(50);
        return view('reportes.estadoClientes.estadoClientesIndex',compact('clientes','listaClientes'));
    }

    public function estadoCuentaClientes(Request $request)
    {
        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $cliente = $request->get('cliente');
        $prestamo = $request->get('prestamos');
        $prestamos = [];
        $prestamoSel = null;
        if ($cliente) {
            $prestamos = prestamosModel::where('user_id', decode($cliente))
                ->where('desembolsado', 1)
                ->where('estado', 1)
                ->get()
                ->mapWithKeys(function($p) {
                    return [encode($p->id) => '#'.$p->consecutivo.' | C$ '.number_format($p->monto_financiado, 2)];
                })
                ->toArray();

            if ($prestamo) {
                $prestamoSel = prestamosModel::where('id', decode($prestamo))->first();
                
                // Calcular promedio de días de atraso
                $totalDiasAtraso = 0;
                $totalCuotas = $prestamoSel->cuotas->count();
                
                foreach ($prestamoSel->cuotas as $cuota) {
                    $fechaPlanCuota = \Carbon\Carbon::parse($cuota->fecha_cuota);
                    
                    // Si la cuota está pagada (estado 3), calcular días de atraso
                    if ($cuota->estado == 3) {
                        // Obtener la fecha del último abono de esta cuota
                        $ultimoAbono = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuota->id)
                            ->where('estado', 1)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        if ($ultimoAbono) {
                            $fechaPagoReal = \Carbon\Carbon::parse($ultimoAbono->created_at);
                            $diasAtraso = $fechaPlanCuota->diffInDays($fechaPagoReal, false);
                            
                            // Solo contar si hay atraso (días positivos)
                            if ($diasAtraso > 0) {
                                $totalDiasAtraso += $diasAtraso;
                            }
                        }
                    }
                    // Si la cuota está pendiente o parcial y ya venció, contar días desde hoy
                    elseif (in_array($cuota->estado, [1, 2]) && $fechaPlanCuota->isPast()) {
                        $diasAtraso = $fechaPlanCuota->diffInDays(\Carbon\Carbon::now(), false);
                        if ($diasAtraso > 0) {
                            $totalDiasAtraso += $diasAtraso;
                        }
                    }
                }
                
                $promedioDiasAtraso = $totalCuotas > 0 ? round($totalDiasAtraso / $totalCuotas, 2) : 0;
                
                if($request->get('exportar')) {
                    $pdf = Pdf::loadView('reportes.estadoCuentaCliente.estadoCuentaPDF', compact('prestamoSel', 'promedioDiasAtraso'));
                    $pdf->setPaper('letter', 'portrait');
                    return $pdf->stream('Estado_Cuenta_'.$prestamoSel->consecutivo.'.pdf');
                }
            }
        }

        return view('reportes.estadoCuentaCliente.estadoCuentaIndex',compact('listaClientes','prestamos','prestamoSel'));
    }

    public function planPago(Request $request)
    {
        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $cliente = $request->get('cliente');
        $prestamo = $request->get('prestamos');
        $prestamos = [];
        $prestamoSel = null;
        if ($cliente) {
            $prestamos = prestamosModel::where('user_id', decode($cliente))
                ->where('desembolsado', 1)
                ->where('estado', 1)
                ->get()
                ->mapWithKeys(function($p) {
                    return [encode($p->id) => '#'.$p->consecutivo.' | C$ '.number_format($p->monto_financiado, 2)];
                })
                ->toArray();

            if ($prestamo) {
                $prestamoSel = prestamosModel::where('id', decode($prestamo))->first();
                if($request->get('exportar')) {
                    $pdf = Pdf::loadView('reportes.planPago.planPagoPDF', compact('prestamoSel'));
                    $pdf->setPaper('letter', 'portrait');
                    return $pdf->stream('Plan_Pago_'.$prestamoSel->consecutivo.'.pdf');
                }
            }
        }

        return view('reportes.planPago.planPagoIndex',compact('listaClientes','prestamos','prestamoSel'));
    }

    public function getPrestamosCliente($clienteId)
    {
        $prestamos = prestamosModel::where('user_id', decode($clienteId))
            ->where('desembolsado', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->mapWithKeys(function($p) {
                $estado = $p->estado == 1 ? 'Activo' : 'Cancelado';
                return [encode($p->id) => '#'.$p->consecutivo.' | C$ '.number_format($p->monto_financiado, 2).' | '.$estado];
            })
            ->toArray();

        return response()->json($prestamos);
    }

    public function antiguedad_saldos(Request $request)
    {
        $listaClientes = User::cliente()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $cliente = $request->get('cliente');
        $cobrador = $request->get('cobrador');
        $frecuencia = $request->get('frecuencia');

        // OPTIMIZACIÓN: Eager loading para evitar N+1
        $prestamos = prestamosModel::with(['cliente', 'agente', 'cuotas' => function($query) {
                $query->orderBy('id', 'desc')->limit(1);
            }])
            ->where('desembolsado', 1)
            ->when($cliente,function ($query) use ($cliente){
                $query->where('user_id',decode($cliente));
            })
            ->when($cobrador,function ($query) use ($cobrador){
                $query->where('agente_id',decode($cobrador));
            })
            ->when($frecuencia,function ($query) use ($frecuencia){
                $query->where('forma_pago_tipo',$frecuencia);
            })
            ->whereNotIn('estado', [4, 2])
            ->orderBy('id','desc');
        
        if($request->get('pdf')) {
            $prestamos = $prestamos->get();
            $pdf = Pdf::loadView('reportes.antiguedad_saldos.antiguedadSaldosPDF', compact('prestamos'));
            $pdf->setPaper('letter', 'landscape');
            return $pdf->stream('Antiguedad_Saldos_'.date('Y-m-d').'.pdf');
        }
        if($request->get('excel')) {
            $prestamos = $prestamos->get();
            return Excel::download(new antiguedadSaldosExport($prestamos), 'Antiguedad de Saldos.xlsx');
        }

        // Si tiene parámetros de filtro, mostrar vista HTML con datos
        if($request->has('cliente') || $request->has('cobrador') || $request->has('frecuencia')) {
            $prestamos = $prestamos->get();
            return view('reportes.antiguedad_saldos.antiguedadSaldosHTML', compact('prestamos', 'cliente', 'cobrador', 'frecuencia', 'listaClientes', 'listaCobradores'));
        }

        $prestamos = $prestamos->get();
        return view('reportes.antiguedad_saldos.antiguedadSaldosIndex',compact('listaClientes','listaCobradores','prestamos'));
    }

    public function colocacionVsRecuperacion(Request $request)
    {
        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();

        $desde    = $request->get('desde');
        $hasta    = $request->get('hasta');
        $cobrador = $request->get('cobrador');

        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->whereIn('id', $agentesAsignados);
            })
            ->whereHas('clientesAsignados', function ($query) {
                $query->where('desembolsado', 1)
                      ->where('estado', 1);  // Solo préstamos activos = agentes que aún tienen clientes
            })
            ->orderBy('nombres')->orderBy('apellidos')
            ->get();

        $listaCobradorSelect = $listaCobradores->pluck('full_name', 'id_enc')->toArray();

        if ($request->route()->getName() === 'reportes.colocacionVsRecuperacion.html' && $desde && $hasta) {

            $agentes = $listaCobradores;
            if ($cobrador) {
                $agentes = $agentes->where('id', decode($cobrador));
            }

            $resultado = [];

            foreach ($agentes as $agente) {

                // ─── COLOCACIÓN ────────────────────────────────────────────────────────────
                // Misma lógica que reporte de Desembolsos:
                // filtra prestamos desembolsados por fecha_desembolso,
                // suma monto_financiado (Capital + Interés total = lo que el cliente debe pagar)
                $colocacion = \DB::table('prestamos as p')
                    ->where('p.agente_id', $agente->id)
                    ->where('p.desembolsado', 1)
                    ->whereNull('p.deleted_at')
                    ->whereDate('p.fecha_desembolso', '>=', $desde)
                    ->whereDate('p.fecha_desembolso', '<=', $hasta)
                    ->selectRaw('COUNT(p.id) as num_desembolsos, COALESCE(SUM(p.monto_financiado), 0) as total_colocado')
                    ->first();

                // ─── RECUPERACIÓN ───────────────────────────────────────────────────────────
                // Misma lógica que reporte de Recuperación:
                // filtra abonos por fecha_abono, suma el monto total abonado
                // sobre los préstamos ASIGNADOS a este agente (sin importar quién cobró)
                $recuperacion = \DB::table('abonos as a')
                    ->join('prestamos as p', 'p.id', '=', 'a.prestamo_id')
                    ->leftJoin(\DB::raw('(SELECT abono_id, SUM(monto_abono) as total FROM prestamo_cuota_abono WHERE estado = 1 GROUP BY abono_id) as t_pca'), 'a.id', '=', 't_pca.abono_id')
                    ->where('p.agente_id', $agente->id)
                    ->where('a.estado', 1)
                    ->whereNull('p.deleted_at')
                    ->whereDate('a.fecha_abono', '>=', $desde)
                    ->whereDate('a.fecha_abono', '<=', $hasta)
                    ->selectRaw('COALESCE(SUM(t_pca.total), 0) as total_recuperado')
                    ->first();

                $totalColocado   = (float) ($colocacion->total_colocado ?? 0);
                $totalRecuperado = (float) ($recuperacion->total_recuperado ?? 0);
                $numDesembolsos  = (int)   ($colocacion->num_desembolsos ?? 0);

                $resultado[] = [
                    'agente_nombre'   => $agente->full_name,
                    'colocacion'      => $totalColocado,
                    'recuperacion'    => $totalRecuperado,
                    'diferencia'      => $totalColocado - $totalRecuperado,
                    'num_desembolsos' => $numDesembolsos,
                ];
            }

            $totales = [
                'colocacion'      => array_sum(array_column($resultado, 'colocacion')),
                'recuperacion'    => array_sum(array_column($resultado, 'recuperacion')),
                'diferencia'      => array_sum(array_column($resultado, 'diferencia')),
                'num_desembolsos' => array_sum(array_column($resultado, 'num_desembolsos')),
            ];

            return view('reportes.colocacionRecuperacion.colocacionRecuperacionHTML', compact(
                'resultado', 'totales', 'desde', 'hasta', 'cobrador', 'listaCobradorSelect'
            ));
        }

        return view('reportes.colocacionRecuperacion.colocacionRecuperacionIndex', compact('listaCobradorSelect'));
    }

    public function carteraDiaria(Request $request)
    {
        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();
        $listaCobradores = User::agente()->activo()
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->wherein('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $cobrador = $request->get('cobrador');
        $fecha = $request->get('fecha') ?: date('Y-m-d');

        if ($cobrador && $fecha) {
            $agenteId = decode($cobrador);
            
            // Obtener todos los préstamos activos y desembolsados del agente
            $prestamos = prestamosModel::with(['agente', 'cliente', 'cuotas' => function($q) {
                    $q->where('estado', '!=', 4);
                }])
                ->where('agente_id', $agenteId)
                ->where('desembolsado', 1)
                ->whereIn('estado', [1, 3]) // Activos o Vencidos
                ->whereNull('fecha_clasificacion')
                ->get();

            $cuotasDia = collect();
            $cuotasMora = collect();
            $cuotasVencidas = collect();

            $prestamoIds = $prestamos->pluck('id')->toArray();
            $ultimosAbonos = \DB::table('abonos')
                ->whereIn('prestamo_id', $prestamoIds)
                ->where('estado', 1)
                ->select('prestamo_id', \DB::raw('MAX(fecha_abono) as ultimo_abono'))
                ->groupBy('prestamo_id')
                ->pluck('ultimo_abono', 'prestamo_id');

            foreach ($prestamos as $p) {
                // REGLA DE ORO: Si ya no debe nada, no sale en el reporte
                if ($p->pendiente_abono <= 0) continue;

                $hasCuotaHoy = $p->cuotas->where('fecha_cuota', $fecha)->first();
                $lastCuotaDate = $p->cuotas->max('fecha_cuota');
                
                // 1. ¿Tiene cuota hoy? -> Va a la sección de "Cuotas del Día"
                if ($hasCuotaHoy) {
                    // Calculamos el atraso anterior (cuotas antes de hoy que no están pagadas)
                    $atraso = 0;
                    $cuotasAtrasadas = $p->cuotas->where('fecha_cuota', '<', $fecha)->whereIn('estado', [1, 2]);
                    foreach($cuotasAtrasadas as $ca) {
                        $atraso += ($ca->monto_cuota - $ca->abonos->where('estado', 1)->sum('monto_abono'));
                    }
                    
                    // Cantidad de cuotas vencidas
                    $cantVenc = $cuotasAtrasadas->count();
                    
                    // Monto de la cuota de hoy (solo lo pendiente)
                    $montoCuotaHoy = $hasCuotaHoy->estado == 3 ? 0 : ($hasCuotaHoy->monto_cuota - $hasCuotaHoy->abonos->where('estado', 1)->sum('monto_abono'));

                    $cuotasDia->push([
                        'prestamo' => $p,
                        'cuota_hoy' => $montoCuotaHoy,
                        'atraso' => $atraso,
                        'cant_venc' => $cantVenc,
                        'dias_atraso' => $p->dias_atraso['dias_atraso'], // Usamos el helper del modelo
                        'saldo_total' => $p->pendiente_abono,
                        'fecha_venc' => $lastCuotaDate
                    ]);
                } 
                // 2. ¿El plazo ya venció? -> Va a la sección de "Vencidos"
                elseif ($lastCuotaDate < $fecha) {
                    $cantVenc = $p->cuotas->whereIn('estado', [1, 2])->count();
                    $cuotasVencidas->push([
                        'prestamo'     => $p,
                        'cant_venc'    => $cantVenc,
                        'dias_atraso'  => $p->dias_atraso['dias_atraso'],
                        'saldo_total'  => $p->pendiente_abono,
                        'fecha_venc'   => $lastCuotaDate,
                        'ultimo_abono' => $ultimosAbonos->get($p->id),
                    ]);
                }
                else {
                    $atrasoTotal = 0;
                    $cuotasAtrasadas = $p->cuotas->where('fecha_cuota', '<', $fecha)->whereIn('estado', [1, 2]);
                    foreach($cuotasAtrasadas as $ca) {
                        $atrasoTotal += ($ca->monto_cuota - $ca->abonos->where('estado', 1)->sum('monto_abono'));
                    }

                    if ($atrasoTotal > 0) {
                        $cuotasMora->push([
                            'prestamo'     => $p,
                            'atraso'       => $atrasoTotal,
                            'cant_venc'    => $cuotasAtrasadas->count(),
                            'dias_atraso'  => $p->dias_atraso['dias_atraso'],
                            'saldo_total'  => $p->pendiente_abono,
                            'fecha_venc'   => $lastCuotaDate,
                            'ultimo_abono' => $ultimosAbonos->get($p->id),
                        ]);
                    }
                }
            }

            // ORDENAR POR DÍAS DE ATRASO (MENOR A MAYOR)
            $cuotasDia = $cuotasDia->sortBy('dias_atraso')->values();
            $cuotasMora = $cuotasMora->sortBy('dias_atraso')->values();
            $cuotasVencidas = $cuotasVencidas->sortBy('dias_atraso')->values();

            if ($request->get('pdf')) {
                $pdf = Pdf::loadView('reportes.carteraDiaria.cobranzaDiariaPDF', compact('cuotasDia', 'cuotasMora', 'cuotasVencidas', 'fecha'));
                $pdf->setPaper('letter', 'landscape');
                return $pdf->stream('Cobranza_Diaria_'.date('Y-m-d').'.pdf');
            }
            
            if ($request->get('excel')) {
                return Excel::download(new cobranzaDiariaExport($cuotasDia, $cuotasMora, $cuotasVencidas, $fecha), 'Cobranza Diaria.xlsx');
            }
            
            return view('reportes.carteraDiaria.cobranzaDiariaHTML', compact('cuotasDia', 'cuotasMora', 'cuotasVencidas', 'cobrador', 'fecha', 'listaCobradores'));
        }

        return view('reportes.carteraDiaria.cobranzaDiariaIndex', compact('listaCobradores'));
    }
}
