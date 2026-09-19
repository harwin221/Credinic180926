<?php

namespace App\Http\Controllers\agenteControllers;

use App\Exports\listaCuotasExport;
use App\Http\Controllers\Controller;
use App\Models\abonosModel;
use App\Models\arqueoModel;
use App\Models\prestamoCuotaAbonoModel;
use App\Models\prestamoCuotasModel;
use App\Models\prestamosModel;
use App\Models\solicitudPrestamoModel;
use App\Models\User;
use App\utils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use function Psy\bin;

class HomeControllerAgenteController extends Controller
{
    public function index()
    {
        // PESTAÑA 1: CUOTAS DEL DÍA
        // Clientes que tienen cuota programada para HOY (sin importar si están en mora)
        $cobrosDia = prestamoCuotasModel::join('prestamos as P', 'P.id', 'prestamo_coutas.prestamo_id')
            ->join('users as U', 'U.id', 'P.user_id')
            ->where('P.estado', 1)
            ->whereNull('P.fecha_clasificacion')
            ->where('P.desembolsado', 1)
            ->whereDate('prestamo_coutas.fecha_cuota', Carbon::now())
            ->whereIn('prestamo_coutas.estado', [1, 2])
            ->where('agente_id', userLogeado()->id)
            ->select('prestamo_coutas.*')
            ->orderBy('U.nombres', 'asc')
            ->orderBy('U.apellidos', 'asc')
            ->get();

        // PESTAÑA 2: CLIENTES EN MORA
        // Clientes con cuotas vencidas pero que AÚN tienen cuotas futuras (préstamo activo)
        $clientesEnMora = prestamosModel::join('users as U', 'U.id', 'prestamos.user_id')
            ->leftJoin('prestamo_coutas as PC', function($join) {
                $join->on('PC.prestamo_id', '=', 'prestamos.id')
                     ->whereRaw('PC.id = (SELECT id FROM prestamo_coutas WHERE prestamo_id = prestamos.id AND fecha_cuota < NOW() AND estado IN (1,2) ORDER BY fecha_cuota ASC LIMIT 1)');
            })
            ->where('prestamos.estado', 1)
            ->where('desembolsado', 1)
            ->whereNull('prestamos.fecha_clasificacion')
            ->where('agente_id', userLogeado()->id)
            ->whereHas('cuotas', function($query) {
                // Tiene cuotas vencidas
                $query->whereDate('fecha_cuota', '<', Carbon::now())
                      ->whereIn('estado', [1, 2]);
            })
            ->whereHas('cuotas', function($query) {
                // Tiene cuotas futuras (préstamo no vencido)
                $query->whereDate('fecha_cuota', '>=', Carbon::now());
            })
            ->whereDoesntHave('cuotas', function($query) {
                // NO tiene cuota para hoy
                $query->whereDate('fecha_cuota', Carbon::now());
            })
            ->select('prestamos.*', DB::raw('DATEDIFF(NOW(), PC.fecha_cuota) as dias_mora'))
            ->orderBy('dias_mora', 'asc')
            ->get();

        // PESTAÑA 3: PRÉSTAMOS VENCIDOS
        // Préstamos donde la última cuota ya venció pero tienen saldo pendiente
        $prestamosVencidos = prestamosModel::join('users as U', 'U.id', 'prestamos.user_id')
            ->leftJoin('prestamo_coutas as PC', function($join) {
                $join->on('PC.prestamo_id', '=', 'prestamos.id')
                     ->whereRaw('PC.id = (SELECT id FROM prestamo_coutas WHERE prestamo_id = prestamos.id ORDER BY fecha_cuota DESC LIMIT 1)');
            })
            ->where('prestamos.estado', 1)
            ->where('desembolsado', 1)
            ->whereNull('prestamos.fecha_clasificacion')
            ->where('agente_id', userLogeado()->id)
            ->whereHas('cuotas', function($query) {
                // La última cuota ya venció
                $query->whereDate('fecha_cuota', '<', Carbon::now())
                      ->whereIn('estado', [1, 2]);
            })
            ->whereDoesntHave('cuotas', function($query) {
                // NO tiene cuotas futuras
                $query->whereDate('fecha_cuota', '>=', Carbon::now());
            })
            ->whereRaw('(SELECT SUM(monto_cuota) FROM prestamo_coutas WHERE prestamo_id = prestamos.id) > (SELECT COALESCE(SUM(monto_abono), 0) FROM prestamo_cuota_abono WHERE prestamo_cuota_id IN (SELECT id FROM prestamo_coutas WHERE prestamo_id = prestamos.id) AND estado = 1)')
            ->select('prestamos.*', DB::raw('DATEDIFF(NOW(), PC.fecha_cuota) as dias_vencido'))
            ->orderBy('dias_vencido', 'asc')
            ->get();

        return view('agentesViews.homeAgentes', compact('cobrosDia', 'clientesEnMora', 'prestamosVencidos'));
    }

    public function recaudo()
    {
        $agente   = userLogeado();
        $hoy      = Carbon::today()->toDateString();

        // ── Abonos del agente logueado registrados HOY ───────────────────────────
        $abonosHoy = DB::table('abonos as a')
            ->join('prestamos as p',  'p.id',  '=', 'a.prestamo_id')
            ->join('users as u',      'u.id',  '=', 'p.user_id')
            // Totales del abono (capital + interes + mora + total)
            ->leftJoin(DB::raw('(SELECT abono_id,
                                    SUM(total_capital)  as capital,
                                    SUM(total_interes)  as interes,
                                    SUM(total_mora)     as mora,
                                    SUM(monto_abono)    as total
                                FROM prestamo_cuota_abono
                                WHERE estado = 1
                                GROUP BY abono_id) as t_pca'), 'a.id', '=', 't_pca.abono_id')
            // Fecha de la cuota más antigua aplicada → clasifica el tipo de cobro
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
            ->where('a.created_user_id', $agente->id)
            ->where('a.estado', 1)
            ->whereDate('a.fecha_abono', $hoy)
            ->select(
                'a.id', 'a.prestamo_id', 'a.fecha_abono', 'a.tipo_abono', 'a.total_transferencia',
                'p.estado as prestamo_estado',
                'p.user_id as cliente_id',
                DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_nombre"),
                't_pca.capital  as total_abonado_capital',
                't_pca.interes  as total_abonado_interes',
                't_pca.mora     as total_abonado_mora',
                't_pca.total    as total_abonado',
                't_fc.fecha_cuota_min',
                DB::raw('COALESCE(t_fut.cuotas_futuras_pend, 0) as cuotas_futuras_pend'),
                DB::raw('COALESCE(t_saldo.total_cuotas, 0) - COALESCE(t_saldo.total_abonado_prest, 0) as saldo_pendiente_prest')
            )
            ->get();

        // ── Clasificación ─────────────────────────────────────────────────────────
        $resumen = [
            'total_recuperado'    => 0,
            'total_transferencia' => 0,
            'dia_recaudado'       => 0,
            'mora_recaudada'      => 0,
            'proximo_recaudado'   => 0,
            'vencido_recaudado'   => 0,
            'total_clientes'      => 0,
        ];
        $clientesUnicos = [];

        $abonoIds = $abonosHoy->pluck('id')->toArray();
        $detallesPorAbono = DB::table('prestamo_cuota_abono as pca')
            ->join('prestamo_coutas as pc', 'pc.id', '=', 'pca.prestamo_cuota_id')
            ->whereIn('pca.abono_id', $abonoIds)
            ->where('pca.estado', 1)
            ->select('pca.abono_id', 'pca.monto_abono', 'pc.fecha_cuota')
            ->get()
            ->groupBy('abono_id');

        foreach ($abonosHoy as $abono) {
            $monto = (float) ($abono->total_abonado ?? 0);
            $clientesUnicos[$abono->cliente_id] = true;
            $resumen['total_recuperado']    += $monto;
            $resumen['total_transferencia'] += (float) ($abono->total_transferencia ?? 0);

            if ($abono->prestamo_estado == 3 ||
                ($abono->cuotas_futuras_pend == 0 && $abono->saldo_pendiente_prest > 0)) {
                $resumen['vencido_recaudado'] += $monto;
            } else {
                $fechaAbono = substr($abono->fecha_abono, 0, 10);
                $detalles   = $detallesPorAbono->get($abono->id, collect());

                foreach ($detalles as $detalle) {
                    $montoCuota  = (float) $detalle->monto_abono;
                    $fechaCuota  = substr($detalle->fecha_cuota, 0, 10);

                    if ($fechaCuota < $fechaAbono) {
                        $resumen['mora_recaudada']    += $montoCuota;
                    } elseif ($fechaCuota > $fechaAbono) {
                        $resumen['proximo_recaudado'] += $montoCuota;
                    } else {
                        $resumen['dia_recaudado']     += $montoCuota;
                    }
                }
            }
        }
        $resumen['total_clientes'] = count($clientesUnicos);

        return view('agentesViews.recaudo', compact('agente', 'resumen', 'hoy'));
    }

    public function misClientes(Request $request)
    {
        $buscar =  $request->buscar;
        $clientes = User::join('prestamos as P', 'users.id', 'P.user_id')
            ->when($buscar,function ($query) use ($buscar){
                $query->where(function ($query) use ($buscar){
                   $query->where('nombres','like','%'.$buscar.'%')
                   ->orWhere('apellidos','like','%'.$buscar.'%')
                   ->orWhere('cedula','like','%'.$buscar.'%');
                });
            })
            ->where('desembolsado',1)
            ->whereNotIn('P.estado', [2,4])//pagados y anulados
            ->where('P.agente_id', userLogeado()->id)
            ->whereNull('P.fecha_clasificacion')
            ->select('users.*')
            ->distinct()
            ->orderBy('users.nombres', 'asc')
            ->orderBy('users.apellidos', 'asc')
            ->paginate(50);
        return view('agentesViews.clientes.agentesClientesIndex',compact('clientes'));
    }

    public function prestamosClientes($id)
    {
        $cliente = User::where('id',decode($id))->first();
        $prestamosCliente = prestamosModel::where('user_id',decode($id))
            ->where('agente_id',userLogeado()->id)
            ->where('estado',1)
            ->whereNull('fecha_clasificacion')
            ->where('desembolsado',1)
            ->paginate(1);

        return view('agentesViews.clientes.agentesPrestamosClientes',compact('cliente','prestamosCliente'));
    }

    public function arqueoIndex(Request $request)
    {
        return redirect()->back()->with('warning','OPERACIÓN NO PERMITIDA');
        $arqueo = arqueoModel::where('fecha_arqueo',Carbon::now()->toDateString())
            ->where('created_user_id',userLogeado()->id)
            ->first();

        $abonos = [];
        $total_recuperado = 0;

        $abonos = abonosModel::whereDate('fecha_abono', Carbon::now()->toDateString())
            ->where('created_user_id',userLogeado()->id)
            ->where('estado', 1)
            ->get();

            if ($abonos) {
                $total_recuperado = 0;
                foreach ($abonos as $ab) {
                    $total_recuperado += $ab->total_abonado;
                }
            }


        return view('agentesViews.arqueo.arqueoAgente',compact('arqueo','abonos','total_recuperado'));
    }

    public function saveOrUpdateAqueroAgente(Request $request)
    {
        $fecha = $request->fecha;

        if(!$fecha)
            return redirect()->back()->with('warning','El campo fecha es requerido');

        if ($fecha < date('Y-m-d') || $fecha > date('Y-m-d'))
            return redirect()->back()->with('warning', 'No se puede crear/modificar arqueo de una fecha inferior o posterior a la actual');

        $tipo_guardado = 1;
        if ($arqueo = arqueoModel::where('cobrador_id', userLogeado()->id)->where('fecha_arqueo', $fecha)->first()) {
            $arqueo->fill($request->all());
            $arqueo->updated_user_id = userLogeado()->id;
        } else {
            $arqueo = new arqueoModel($request->all());
        }
        $arqueo->efectivo = $request->get('efectivo');
        $arqueo->tipocambio = $request->get('tipocambio');
        $arqueo->cobrador_id = userLogeado()->id;
        $arqueo->estado = $tipo_guardado; //1:solo guardar, 2:guadar y finalizar
        $arqueo->fecha_arqueo = $fecha; //1:solo guardar, 2:guadar y finalizar
        $arqueo->created_user_id = userLogeado()->id; //1:solo guardar, 2:guadar y finalizar

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


    public function reportesIndex()
    {
        $listaClientes = User::whereHas('prestamos', function ($query) {
            $query->where('agente_id', \Auth::user()->id);
        })
            ->get()
            ->pluck('full_name', 'id_enc')
            ->toArray();

        return view('agentesViews.reportes.rptIndexAgentes', compact('listaClientes'));
    }

    public function cuotas(Request $request)
    {
        $desdeSel = $request->desde;
        $hastaSel = $request->hasta;

        $abonos = prestamoCuotaAbonoModel::with('prestamo_cuota','prestamo_cuota.prestamo')
            ->orderBy('fecha_abono','desc')
            ->orderBy('prestamo_cuota_id','desc')
            ->when($desdeSel, function ($query) use ($desdeSel) {
                $query->whereDate('fecha_abono', '>=', $desdeSel);
            })
            ->when($hastaSel, function ($query) use ($hastaSel) {
                $query->whereDate('fecha_abono', '<=', $hastaSel);
            })
            ->where('created_user_id',userLogeado()->id);
//            ->whereRelation('prestamo_cuota.prestamo', 'agente_id',userLogeado()->id);

        if ($request->pdf) {
            $abonos = $abonos->get();
            return view('reportes.cuotas.listaCuotasPDF', compact('abonos'));
        }
        if ($request->excel) {
            $abonos = $abonos->get();
            return Excel::download(new listaCuotasExport($abonos),'Lista de Cuotas.xlsx');
        }

        $abonos = $abonos->paginate(20);
        return view('agentesViews.reportes.cuotas.cuotasAgentesIndex',compact('abonos'));
    }


    public function colocacion(Request $request)
    {
        $desde = $request->desde;
        $hasta = $request->hasta;

        $listaCobradores = User::agente()->get()->pluck('full_name', 'id_enc')->toArray();

        $sumaAbonoDia = array();
        if($desde && $hasta){
            $primerDiaMesSeleccionado = \Carbon\Carbon::createFromFormat('Y-m-d',$desde);
            $ultimoDiaMesSeleccionado = \Carbon\Carbon::createFromFormat('Y-m-d',$hasta);

            $diaContador = $primerDiaMesSeleccionado;
            $sumaAbonoDia = array();
            while ($diaContador <= $ultimoDiaMesSeleccionado) {
                $abonosDia = abonosModel::whereDate('fecha_abono', $diaContador->toDateString())
                    ->where('created_user_id', userLogeado()->id)
                    ->get();
                $totalAbonos = 0;
                foreach ($abonosDia as $dia) {
                    $totalAbonos += $dia->total_abonado;
                }
                $sumaAbonoDia[$diaContador->toDateString()] = $totalAbonos;
                $diaContador = $diaContador->addDay();
            }
        }
        return view('agentesViews.reportes.colocacion.detalleColocacionAgentes',compact('sumaAbonoDia','listaCobradores'));
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

        $prestamoCuotas = prestamoCuotasModel::join('prestamos as P', 'P.id', 'prestamo_coutas.prestamo_id')
            ->where('P.estado', 1)
            ->where('P.desembolsado', 1)
            ->whereNull('P.fecha_clasificacion')
            ->when($fecha, function ($query) use ($fecha, $fecha2) {
                $query->whereDate('prestamo_coutas.fecha_cuota', '>=', $fecha)
                    ->whereDate('prestamo_coutas.fecha_cuota', '<=', $fecha2);
            })
            ->whereIn('prestamo_coutas.estado', [1, 2])
            ->where('agente_id', userLogeado()->id)
            ->select('prestamo_coutas.*')
            ->orderBy('P.agente_id')
            ->paginate(50);

        return view('agentesViews.reportes.cobrosDia.agenteCobrosDia',compact('prestamoCuotas'));
    }

    public function getListClientes(Request $request)
    {
        $buscar = $request->buscar;
        $clientes = User::cliente()
            ->join('prestamos as P', 'P.user_id', 'users.id')
            ->where('P.agente_id', userLogeado()->id)
            ->whereNull('P.fecha_clasificacion')
            ->where('P.estado', 1)
            ->select('users.*')
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($query2) use ($buscar) {
                    $query2->where('nombres', 'like', '%' . $buscar . '%')
                        ->orwhere('apellidos', 'like', '%' . $buscar . '%')
                        ->orwhere('cedula', 'like', '%' . $buscar . '%');
                });
            })
            ->distinct()
            ->paginate(15);
        return view('abonos.listaClientes', compact('clientes'))->render();
    }
    public function getDatosCliente($clienteId)
    {
        $user = User::where('id', decode($clienteId))->first();
        $user->dep = $user->departamento_municipio->departamento->nombre;
        $user->mun = $user->departamento_municipio->nombre;
        $user->sexo = $user->sexo == 1 ? 'Femenino':'Masculino';
        $user->estado_civil = $user->estado_civil_user;
        $user->prestamoActivo = 0;
        if($user->prestamos()->where('estado',1))
            $user->prestamoActivo = 1;
        return response()->json($user);
    }
    public function getInfoCliente(Request $request)
    {
        $user_id = decode($request->clienteId);
        $user = User::where('id', $user_id)->first();
        
        // Manejar el caso cuando no tiene departamento/municipio
        if ($user->departamento_municipio && $user->departamento_municipio->departamento) {
            $user->dep = $user->departamento_municipio->departamento->nombre;
            $user->mun = $user->departamento_municipio->nombre;
        } else {
            $user->dep = '-';
            $user->mun = '-';
        }
        
        $datos = [
            'userData' => $user,
            'prestamoActivos' => prestamosModel::where('user_id', $user_id)
                ->whereNull('fecha_clasificacion')
                ->where('desembolsado',1)->where('estado', 1)->get()
        ];
        return response()->json($datos);
    }
    public function getCuotasPendientesPrestamos(Request $request)
    {
        $prestamoId = $request->prestamoId;

        $prestamoCuotas = prestamoCuotasModel::where('prestamo_id', $prestamoId)->get();
        $prestamo = prestamosModel::where('id', $prestamoId)->first();

        return view('abonos.listaCuotas', compact('prestamoCuotas', 'prestamo'))->render();
    }

    public function getDetalleCuota(Request $request)
    {

        $cuotas = $request->cuotas;
        $prestamo = $request->prestamoId;
        $objPendiente = null;
        $totalPendiente = 0;
        $moneda = 0;
        if($prestamo){
            $prestamoObj = prestamosModel::where('id',$prestamo)->first();
            if($prestamoObj){
                $totalPendiente = $prestamoObj->pendiente_abono;
                $moneda = $prestamoObj->moneda;
            }
        } else {
            $decodedIds = array_map(function ($cuotas) {
                return decode($cuotas);
            }, $cuotas);

            $cuotas = prestamoCuotasModel::whereIn('id', $decodedIds)->get();
            $moneda = $cuotas[0]->prestamo->moneda;

            foreach ($cuotas as $cuota) {
                $totalPendiente += $cuota->monto_pendiente_cuota;
            }
        }

        $objPendiente = (object)[
            'total_pendiente'=>$totalPendiente,
            'moneda'=>$moneda
        ];
        return response()->json($objPendiente);
    }


    public function createAbono(Request $request)
    {
        $prestamoId = $request->get('prestamo') ? decode($request->get('prestamo')) : null;
        $prestamo = null;
        
        if ($prestamoId) {
            // Cargar el préstamo con todas sus relaciones necesarias
            $prestamo = prestamosModel::with([
                'cliente',
                'cliente.departamento_municipio',
                'cliente.departamento_municipio.departamento'
            ])->find($prestamoId);
        }
        
        return view('agentesViews.abonos.abonosAgentesIndex', compact('prestamo'));
    }

    public function abonos(Request $request)
    {
        $buscar = $request->buscar;
        $abonos = abonosModel::with('prestamo','prestamo.cliente','prestamo.agente','user_create','abono_detalle')->when($buscar, function ($query) use ($buscar) {
            $query->whereHas('prestamo', function ($query) use ($buscar) {
                $query->whereHas('cliente', function ($query) use ($buscar) {
                    $query->where('nombres', 'like', '%' . $buscar . '%')
                        ->orWhere('apellidos', 'like', '%' . $buscar . '%');
                });
            });
        })->where('created_user_id', userLogeado()->id)
            ->orderBy('id', 'desc')->paginate(30);
        return view('agentesViews.abonos.lstAbonosAgente',compact('abonos'));
    }

    public function verAbono($id)
    {
        $abono = abonosModel::with('abono_detalle','abono_detalle.prestamo_cuota','abono_detalle.prestamo_cuota.prestamo')->where('id', decode($id))->first();
        return view('agentesViews.abonos.verAbonoAgente', compact('abono'));
    }

     public function anularAbono(Request $request, $id)
    {
        return redirect()->back()->with('error', 'Operación no permitida');
        try {
            DB::beginTransaction();

            $abono = abonosModel::where('id', decode($id))->first();

            foreach ($abono->abono_detalle as $detalle) {
                $detalle->estado = 2;
                $detalle->save();
                 if ($detalle->prestamo_cuota->estado == 3)// si se marco como pagada, ponerla como pendiente
                {
                    $detalle->prestamo_cuota->estado = 1;
                    $detalle->prestamo_cuota->fecha_pagado = null;
                    $detalle->prestamo_cuota->update();
                }
            }
            $abono->estado = 2;
            $abono->anulado_user_id = userLogeado()->id;
            $abono->fecha_anulado = Carbon::now();
            $abono->detalle_anulado = $request->detalle_anulado;
            $abono->save();
            DB::commit();
            return redirect()->back()->with('success', 'Abono anulado correctamente');

        } catch (\Throwable $ex) {
            DB::rollBack();
            logger()->error('No se ha logrado anular el abono ' . $ex->getMessage());
            return redirect()->back()->with('error', 'No se ha logrado anular el abono');
        }
    }

    public function storeAbono(Request $request)
    {

        $str = [];
        parse_str($request->values, $str);
        $cuotas = json_decode($str['cuotas'][0],true);
        $prestamoId = null;
        if (gettype($cuotas) != "array")//si no es un array quiere decir q es abono al desembolso
            $prestamoId = $cuotas;
        else {
            $decodedIds = array_map(function ($cuotas) {
                return decode($cuotas);
            }, $cuotas);
        }

        $montoEfectivo = (isset($str['efectivoMonto']))?$str['efectivoMonto']:0;
        $montoTarjeta = (isset($str['tarjetaMonto']))?$str['tarjetaMonto']:0;
        $montoCheque = (isset($str['chequeMonto']))?$str['chequeMonto']:0;
        $montoTransferencia = (isset($str['transferenciaMonto']))?$str['transferenciaMonto']:0;

        $valorCuota = $montoEfectivo + $montoTarjeta + $montoCheque + $montoTransferencia;

        if ($valorCuota == 0)
            return response()->json('Revise los montos ingresados', 404);

        try {
            DB::beginTransaction();

            $abono = new abonosModel();
            if ($prestamoId == null) {
                $cuotas = prestamoCuotasModel::whereIn('id', $decodedIds)->orderBy('numero_cuota', 'asc')->get();
                $abono->prestamo_id = $cuotas[0]->prestamo_id;
            } else {
                $cuotas = prestamoCuotasModel::where('prestamo_id', $prestamoId)->orderBy('numero_cuota', 'asc')->get();
                $abono->prestamo_id = $prestamoId;
            }

            $abono->estado = 1;
            $abono->anulado_user_id = NULL;
            $abono->fecha_abono = \Carbon\Carbon::now();
            $abono->tipo_abono = 0;
            $abono->created_user_id = userLogeado()->id;

            $abono->total_efectivo = $montoEfectivo;
            $abono->total_tarjeta = $montoTarjeta;
            $abono->total_cheque = $montoCheque;
            $abono->total_transferencia = $montoTransferencia;

            $abono->referencia_tarjeta = '';
            $abono->referencia_cheque = '';
            $abono->referencia_transferencia = '';
            $abono->save();

            foreach ($cuotas as $cuota){
                if($valorCuota>0 && $cuota->monto_pendiente_cuota>0){
                   $totalAbonoIntereses = $cuota->total_pendiente_interes_cuota;
                    $totalAbonoCapital = $cuota->total_pendiente_capital_cuota;
                    $totalAbonoMora = $cuota->total_pendiente_mora_cuota;

                    $montoAbonarInteres = 0;
                    $montoAbonarCapital = 0;
                    $montoAbonarMora = 0;
                    $totalAbonado = 0;

                    if ($cuota->total_pendiente_interes_cuota > 0) {
                        $totalAbonoIntereses -= $valorCuota;

                        if ($totalAbonoIntereses <= 0) {
                            $montoAbonarInteres = $cuota->total_pendiente_interes_cuota;
                            $valorCuota = abs($totalAbonoIntereses);
                        } else {
                            $montoAbonarInteres = $cuota->total_pendiente_interes_cuota - $totalAbonoIntereses;
                            $valorCuota -= $montoAbonarInteres;
                        }
                        $totalAbonado += $montoAbonarInteres;
                    }


                    if ($cuota->total_pendiente_capital_cuota > 0) {
                        $totalAbonoCapital -= $valorCuota;

                        if ($totalAbonoCapital <= 0) {
                            $montoAbonarCapital = $cuota->total_pendiente_capital_cuota;
                            $valorCuota = abs($totalAbonoCapital);
                        } else {
                            $montoAbonarCapital = $cuota->total_pendiente_capital_cuota - $totalAbonoCapital;
                            $valorCuota -= $montoAbonarCapital;
                        }
                        $totalAbonado += $montoAbonarCapital;
                    }

                    if ($cuota->total_pendiente_mora_cuota > 0) {
                        $totalAbonoMora -= $valorCuota;

                        if ($totalAbonoMora <= 0) {
                            $montoAbonarMora = $cuota->total_pendiente_mora_cuota;
                            $valorCuota = abs($totalAbonoMora);
                        } else {
                            $montoAbonarMora = $cuota->total_pendiente_mora_cuota - $totalAbonoMora;
                            $valorCuota -= $montoAbonarMora;
                        }
                        $totalAbonado += $montoAbonarMora;
                    }

                    $abonoCuota = new prestamoCuotaAbonoModel();
                    $abonoCuota->abono_id = $abono->id;
                    $abonoCuota->prestamo_cuota_id = $cuota->id;
                    $abonoCuota->estado = 1;
                    $abonoCuota->monto_abono = $totalAbonado;
                    $abonoCuota->total_interes = $montoAbonarInteres;
                    $abonoCuota->total_capital = $montoAbonarCapital;
                    $abonoCuota->total_mora = $montoAbonarMora;

                    $abonoCuota->tipo_abono = 1;

                    $abonoCuota->fecha_abono = Carbon::now();
                    $abonoCuota->created_user_id = userLogeado()->id;
                    $abonoCuota->save();

                    if ($cuota->monto_pendiente_cuota == 0) {
                        $cuota->estado = 3;
                        $cuota->fecha_pagado = Carbon::now();
                        $cuota->save();
                    }
                    if ($cuota->prestamo->pendiente_abono == 0) {
                        $cuota->prestamo->estado = 2;
                        $cuota->prestamo->save();
                    }
                }
            }

            DB::commit();
            return response()->json(route('agentes.abonos.printRecibo', encode($abono->id)));
        } catch (\Exception $ex) {
            DB::rollBack();
            logger()->error('Error al crear el abono ' . $ex->getMessage());
            return response()->json('Ha ocurrido un error al intentar crear la cuota ' . $ex->getMessage(), 404);
        }
    }

    public function printRecibo($abonoId)
    {
        $abono = abonosModel::where('id', decode($abonoId))->first();
        return view('abonos.recibo', compact('abono'));
    }

    public function registroClientes(Request $request)
    {
        $buscar = $request->buscar;
        $clientes = User::cnuevo()
            ->buscar($buscar)
            ->orderBy('id','desc')
            ->paginate(30);
        return view('agentesViews.clientes.agentesClientesNuevos',compact('clientes'));
    }

    public function indexNuevoCliente()
    {
        return view('agentesViews.clientes.agenteNuevoCliente');
    }

    public function storeNuevoCliente(Request $request)
    {
        $user = new User($request->all());
        $user->tipo_usuario = 6;
        $user->password = \Hash::make(\Str::random(10));
        $user->email = \Str::random('10') . "@gmail.com";
        $user->created_user_id = userLogeado()->id;
        $user->dep_mun = decode($request->dep_mun);
        $user->estado = 1;
        if ($user->save()) {
            if ($request->file('foto')) {
                $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory);
                $user->foto = $imageName;
                $user->update();
            }
        }
        return redirect()->route('agentes.registroClientes');
    }

    public function editNuevoCliente($id)
    {
        $user = User::where('id', decode($id))->first();
        return view('agentesViews.clientes.agenteEditarCliente', compact('user'));
    }

    public function updateNuevoCliente(Request $request, $id)
    {
        $user = User::findorfail(decode($id));
        $fotoActual = $user->foto;
        $user->fill($request->all());
        $user->dep_mun = decode($request->dep_mun);
        $user->updated_user_id = userLogeado()->id;

        if ($request->file('foto')) {
            $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory, $fotoActual);
            $user->foto = $imageName;
        }

        $user->update();
        return redirect()->route('agentes.registroClientes');
    }

    public function destroyCliente($id)
    {

    }

    public function nuevaSolicitud(Request $request,$id)
    {
        $solicitud = new solicitudPrestamoModel($request->all());
        $solicitud->user_id = decode($id);
        $solicitud->estado = 1;
        $solicitud->created_user_id = userLogeado()->id;
        $solicitud->save();

        return redirect()->back();
    }

    public function destroySolicitud($id)
    {
        $prestamo = prestamosModel::where('id', decode($id))->first();
        if ($prestamo && $prestamo->estado_aprobacion != 1)
            return redirect()->back()->with('warning', 'La solicitud no puede ser eliminada, esta ya no se encuentra en estado pendiente');
        else
            $prestamo->delete();
        return redirect()->back();
    }

    public function estadoCuentaClientes(Request $request)
    {
        $listaClientes = User::whereHas('prestamos', function ($query) {
            $query->where('agente_id', \Auth::user()->id);
        })
            ->get()
            ->pluck('full_name', 'id_enc')
            ->toArray();
        $clienteSel = $request->get('cliente');
        $prestamo = $request->get('prestamo');
        
        if ($clienteSel && $prestamo) {
            $prestamoSel = prestamosModel::where('id', decode($prestamo))->where('agente_id', \Auth::user()->id)->first();
            
            if ($prestamoSel) {
                // Calcular promedio de días de atraso
                $totalDiasAtraso = 0;
                $totalCuotas = $prestamoSel->cuotas->count();
                
                foreach ($prestamoSel->cuotas as $cuota) {
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
                    }
                    elseif (in_array($cuota->estado, [1, 2]) && $fechaPlanCuota->isPast()) {
                        $diasAtraso = $fechaPlanCuota->diffInDays(\Carbon\Carbon::now(), false);
                        if ($diasAtraso > 0) {
                            $totalDiasAtraso += $diasAtraso;
                        }
                    }
                }
                
                $promedioDiasAtraso = $totalCuotas > 0 ? round($totalDiasAtraso / $totalCuotas, 2) : 0;
                
                if($request->get('exportar')) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.estadoCuentaCliente.estadoCuentaPDF', compact('prestamoSel', 'promedioDiasAtraso'));
                    $pdf->setPaper('letter', 'portrait');
                    return $pdf->stream('Estado_Cuenta_'.$prestamoSel->consecutivo.'.pdf');
                }
            }
        }

        return redirect()->back()->with('error', 'No se pudo generar el reporte.');
    }

    public function planPago(Request $request)
    {
        $listaClientes = User::whereHas('prestamos', function ($query) {
            $query->where('agente_id', \Auth::user()->id);
        })
            ->get()
            ->pluck('full_name', 'id_enc')
            ->toArray();

        $clienteSel = $request->get('cliente');
        $prestamo = $request->get('prestamos');
        $prestamos = [];
        $prestamoSel = null;

        if ($clienteSel) {
            $prestamos = prestamosModel::where('user_id', decode($clienteSel))
                ->where('agente_id', \Auth::user()->id)
                ->where('desembolsado', 1)
                ->where('estado', 1)
                ->get()
                ->mapWithKeys(function($p) {
                    return [encode($p->id) => '#'.$p->consecutivo.' | C$ '.number_format($p->monto_financiado, 2)];
                })
                ->toArray();

            if ($prestamo) {
                $prestamoSel = prestamosModel::where('id', decode($prestamo))->where('agente_id', \Auth::user()->id)->first();
                if($prestamoSel && $request->get('exportar')) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.planPago.planPagoPDF', compact('prestamoSel'));
                    $pdf->setPaper('letter', 'portrait');
                    return $pdf->stream('Plan_Pago_'.$prestamoSel->consecutivo.'.pdf');
                }
            }
        }

        return view('agentesViews.reportes.planPago.planPagoIndex',compact('listaClientes','prestamos','prestamoSel'));
    }

    public function getPrestamosCliente($clienteId)
    {
        $prestamos = prestamosModel::where('user_id', decode($clienteId))
            ->where('agente_id', \Auth::user()->id)
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

}
