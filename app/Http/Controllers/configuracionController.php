<?php

namespace App\Http\Controllers;

use App\Imports\tipoCambioImport;
use App\Models\configModel;
use App\Models\feriados;
use App\Models\negocioTiposModel;
use App\Models\tipoCambioModel;
use App\Models\tipoDocumentosModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class configuracionController extends Controller
{

    public function onlineUsers()
    {
        if (\Auth::user()->id == 1) {
            $users = User::where('tipo_usuario', '!=', 3)->get();
            return view('online.usuarios_online', compact('users'));
        }
        else
            return redirect()->route('home');
    }
    public function getDatos($id)
    {
        $documento = tipoDocumentosModel::where('id', decode($id))->first();
        return response()->json($documento);
    }

    public function indexConfiguracion()
    {
        return view('configuracion.configuracionIndex');
    }

    public function indexDocumentos()
    {
        $documentos = tipoDocumentosModel::get();
        return view('configuracion.documentos.documentosIndex', compact('documentos'));
    }

    public function storeTipoDocumento(Request $request)
    {
        $documento = new tipoDocumentosModel();
        $documento->nombre = MAYUS($request->nombre);
        $documento->tipo = $request->tipo;
        $documento->created_user_id = userLogeado()->id;
        $documento->save();
        return redirect()->back();
    }

    public function updateTipoDocumento(Request $request, $id)
    {
        $documento = tipoDocumentosModel::find(decode($id));
        $documento->nombre = MAYUS($request->nombre);
        $documento->tipo = $request->tipo;
        $documento->updated_user_id = userLogeado()->id;
        $documento->update();
        return redirect()->back();
    }

    public function destroyTipoDocumento($id)
    {
        $documento = tipoDocumentosModel::find(decode($id));
        $documento->delete();
        return redirect()->back();
    }

    //TIPOS NEGOCIOS
    public function indexTiposNegocios()
    {
        $tiposNegocios = negocioTiposModel::orderBy('nombre')->paginate(25);
        return view('configuracion.tiposNegocios.tiposNegociosIndex', compact('tiposNegocios'));
    }

    public function storeTiposNegocios(Request $request)
    {
        $tipoNegocio = new negocioTiposModel();
        $tipoNegocio->nombre = MAYUS($request->nombre);
        $tipoNegocio->created_user_id = userLogeado()->id;
        $tipoNegocio->save();
        return redirect()->back();
    }

    public function updateTiposNegocios(Request $request, $id)
    {
        $tipoNegocio = negocioTiposModel::find(decode($id));
        $tipoNegocio->nombre = MAYUS($request->nombre);
        $tipoNegocio->updated_user_id = userLogeado()->id;
        $tipoNegocio->update();
        return redirect()->back();
    }

    public function destroyTiposNegocios($id)
    {
        $tipoNegocio = negocioTiposModel::find(decode($id));
        $tipoNegocio->delete();
        return redirect()->back();
    }

    public function getDatosTipoNegocio($id)
    {
        $tipoNegocio = negocioTiposModel::where('id', decode($id))->first();
        return response()->json($tipoNegocio);
    }

    public function getListTiposNegocios()
    {
        $listTiposNegocios = negocioTiposModel::get()->pluck('nombre', 'id_enc')->toArray();
        return response()->json($listTiposNegocios);
    }


    public function indexTipoCambio()
    {
        $tipoCambioDia = tipoCambioModel::where('fecha', '>=', date('Y-m-01'))
            ->where('fecha', '<=', date('Y-m-t'))
            ->orderBy('fecha','desc')
            ->get();
        return view('tipoCambio.indexTipoCambio',compact('tipoCambioDia'));
    }

    public function importTipoCambio(Request $request)
    {
        $datos = Excel::toArray(new tipoCambioImport, $request->file('tipoCambio'));
        foreach ($datos[0] as $ind => $cambio) {
            if ($ind != 0) {
                if (!tipoCambioModel::where('fecha', $cambio[0])->exists()) {
                    $tipoCambio = new tipoCambioModel();
                    $tipoCambio->fecha = $cambio[0];
                    $tipoCambio->valor = $cambio[1];
                    $tipoCambio->created_user_id = userLogeado()->id;
                    $tipoCambio->save();
                }
            }
        }

        return redirect()->back()->with('success','Tipo de Cambio Importado');
    }

    public function indexFeriados(Request $request)
    {
        $buscar = $request->get('buscar');
        $fecha = $request->get('fecha');

        $feriados = feriados::when($buscar, function ($query) use ($buscar, $fecha) {
            $query->where('nombre', 'like', '%' . $buscar . '%');
            if ($fecha) {
                $query->whereDate('fecha', $fecha);
            }
        })
            ->orderBy('fecha', 'asc')
            ->paginate(50);

        return view('feriados.feriadosIndex',compact('feriados'));
    }

    public function storeFeriados(Request $request)
    {
        if(feriados::where('nombre',$request->get('nombre'))->exists())
            return redirect()->back()->with('warning','El feriado ingresado ya existe');
        $feriado = new feriados();
        $feriado->fecha = $request->get('fecha');
        $feriado->nombre = $request->get('nombre');
        $feriado->tipo = $request->get('tipo', 2); // 1: Recurrente, 2: No recurrente
        if($feriado->save())
            return redirect()->back()->with('success','Se ha creado correctamente el registro');
        else
            return redirect()->back()->with('error','Ha ocurrido un error al intentar crear el registro');
    }

    public function destroyFeriados($id)
    {
        feriados::where('id', decode($id))->delete();
        return redirect()->back();
    }

    public function updateFeriados(Request $request,$id)
    {
        if(feriados::where('nombre',$request->get('nombre'))->where('id','!=',decode($id))->exists())
            return redirect()->back()->with('warning','El feriado ingresado ya existe');
        $feriado = feriados::where('id',decode($id))->first();
        $feriado->fecha = $request->get('fecha');
        $feriado->nombre = $request->get('nombre');
        $feriado->tipo = $request->get('tipo', 2); // 1: Recurrente, 2: No recurrente
        if($feriado->save())
            return redirect()->back()->with('success','Se ha actualizado correctamente el registro');
        else
            return redirect()->back()->with('error','Ha ocurrido un error al intentar actualizado el registro');
    }

    public function getFeriados()
    {
        $anioActual = date('Y');
        $feriadosArray = [];
        
        // Obtener todos los feriados
        $feriados = feriados::all();
        
        foreach ($feriados as $feriado) {
            $fechaOriginal = Carbon::parse($feriado->fecha);
            
            if ($feriado->tipo == 1) {
                // RECURRENTE: Ajustar al año actual
                $fechaAjustada = Carbon::create($anioActual, $fechaOriginal->month, $fechaOriginal->day);
                $feriadosArray[] = $fechaAjustada->format('Y-m-d');
            } else {
                // NO RECURRENTE: Solo incluir si es del año actual o futuro
                if ($fechaOriginal->year >= $anioActual) {
                    $feriadosArray[] = $fechaOriginal->format('Y-m-d');
                }
            }
        }
        
        // Eliminar duplicados y ordenar
        $feriadosArray = array_unique($feriadosArray);
        sort($feriadosArray);
        
        return response()->json($feriadosArray);
    }

    public function activacion_sistema()
    {
        $configuraciones = configModel::get();
        return view('configuracion.activacion.activacion_sistema',compact('configuraciones'));
    }

    public function updactivacion_sistema(Request $request,$id)
    {
        if(!$request->get('hora_inicio_activacion') || !$request->get('hora_fin_activacion'))
            return redirect()->back()->with('error','Ambas horas son requeridas');

        if($request->get('hora_inicio_activacion') >= $request->get('hora_fin_activacion'))
            return redirect()->back()->with('error','Revise las horas establecidas');

        $config = configModel::where('id',$id)->first();
        $config->hora_inicio_activacion = $request->get('hora_inicio_activacion');
        $config->hora_fin_activacion = $request->get('hora_fin_activacion');
        $config->updated_user_id = Auth::id();
        $config->update();

        return redirect()->back()->with('success','Se ha actualizado correctamente el registro');
    }

    public function restringidoAdminPage()
    {
        $config = configModel::where('sistema','admin')->first();
        return view('layouts.restringido',compact('config'));
    }

    public function restringidoCobradorPage()
    {
        $config = configModel::where('sistema', 'cobradores')->first();
        return view('layouts.restringido', compact('config'));
    }    public function recalcularPlanesPorFeriados()
    {
        try {
            ini_set('memory_limit', '2048M');
            set_time_limit(1800); // 30 minutos máximo
            
            $prestamoId = request('prestamo_id');
            
            // OPTIMIZACIÓN 1: Cachear feriados una sola vez
            $anioActual = date('Y');
            $feriados = feriados::whereBetween('fecha', [($anioActual - 1) . '-01-01', ($anioActual + 2) . '-12-31'])
                ->pluck('fecha')
                ->map(fn($f) => Carbon::parse($f)->toDateString())
                ->toArray();

            $query = \App\Models\prestamosModel::whereIn('estado', [1, 3])
                ->where('desembolsado', 1);
                
            if ($prestamoId) {
                $query->where('id', $prestamoId);
            }
            
            // OPTIMIZACIÓN 2: Solo IDs, no cargar relaciones pesadas
            $prestamoIds = $query->pluck('id')->toArray();
            $totalPrestamos = count($prestamoIds);

            $stats = [
                'prestamos_recalculados' => 0,
                'cuotas_regeneradas' => 0,
                'abonos_redistribuidos' => 0,
                'cancelados' => 0,
                'reactivados' => 0,
                'fallidos' => 0,
                'errores' => []
            ];

            // OPTIMIZACIÓN 3: Procesar en chunks de 50 para mejor rendimiento
            $chunks = array_chunk($prestamoIds, 50);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                $prestamos = \App\Models\prestamosModel::whereIn('id', $chunk)->get();
                
                foreach ($prestamos as $prestamo) {
                    try {
                        \DB::beginTransaction();
                        
                        // 1. Reparar día preferido si es NULL
                        if ($prestamo->dia_semana_preferido === null && in_array($prestamo->forma_pago_tipo, [2, 7])) {
                            $fechaPrimerPago = Carbon::parse($prestamo->fecha_primer_pago);
                            $prestamo->dia_semana_preferido = $fechaPrimerPago->dayOfWeek;
                            $prestamo->save();
                        }

                        // 2. Obtener abonos ANTES de borrar
                        $abonosRealizados = \App\Models\abonosModel::where('prestamo_id', $prestamo->id)
                            ->where('estado', 1)
                            ->orderBy('id', 'asc')
                            ->get();
                        
                        $tienePagos = $abonosRealizados->count() > 0;

                        // 3. BORRADO PERMANENTE (sin soft delete)
                        $cuotasIds = \DB::table('prestamo_coutas')
                            ->where('prestamo_id', $prestamo->id)
                            ->pluck('id')
                            ->toArray();
                        
                        if (!empty($cuotasIds)) {
                            \DB::table('prestamo_cuota_abonos')->whereIn('prestamo_cuota_id', $cuotasIds)->delete();
                            \DB::table('prestamo_coutas')->whereIn('id', $cuotasIds)->delete();
                        }

                        // 4. RECALCULAR monto_cuota correcto
                        $formaPago = $prestamo->forma_pago_tipo;
                        $plazo = $prestamo->plazo_pago;
                        
                        switch ($formaPago) {
                            case "1": $numeroCuotas = $plazo * 20; break;
                            case "2": $numeroCuotas = $plazo * 4; break;
                            case "3": case "7": $numeroCuotas = $plazo * 2; break;
                            default: $numeroCuotas = $plazo; break;
                        }
                        
                        $montoCuotaCorregido = round($prestamo->monto_financiado / $numeroCuotas, 2);
                        $interesCuota = round($prestamo->interes_total_pagar / $numeroCuotas, 2);

                        // 5. GENERAR nuevas cuotas
                        $requestSimulado = (object)[
                            'fechaPago' => $prestamo->fecha_primer_pago,
                            'formaPago' => $prestamo->forma_pago_tipo,
                            'plazoPago' => $prestamo->plazo_pago,
                            'montoCuota' => $montoCuotaCorregido,
                            'interesPagar' => $interesCuota,
                            'diaSemanaPreferido' => $prestamo->dia_semana_preferido,
                            'diasPago' => $prestamo->dias_pago
                        ];

                        $pController = new \App\Http\Controllers\prestamosController();
                        $pController->generarPlanPagos($prestamo, $requestSimulado);

                        // 6. REDISTRIBUIR abonos (CORREGIDO)
                        if ($tienePagos) {
                            $cuotasNuevas = \DB::table('prestamo_coutas')
                                ->where('prestamo_id', $prestamo->id)
                                ->orderBy('numero_cuota', 'asc')
                                ->get();

                            $relacionesParaInsertar = [];
                            $cuotasParaActualizar = [];
                            
                            // Mapa global de abonos acumulados por cuota (para todos los abonos)
                            $abonosAcumuladosPorCuota = [];
                            foreach ($cuotasNuevas as $cuota) {
                                $abonosAcumuladosPorCuota[$cuota->id] = ['interes' => 0, 'capital' => 0];
                            }

                            foreach ($abonosRealizados as $abono) {
                                $montoDisponible = $abono->total_efectivo + $abono->total_tarjeta + $abono->total_cheque + $abono->total_transferencia;

                                foreach ($cuotasNuevas as $cuota) {
                                    if ($montoDisponible <= 0.01) break;
                                    
                                    // Calcular pendiente usando el acumulado global
                                    $pendienteInteres = $cuota->monto_interes - $abonosAcumuladosPorCuota[$cuota->id]['interes'];
                                    $pendienteCapital = ($cuota->monto_cuota - $cuota->monto_interes) - $abonosAcumuladosPorCuota[$cuota->id]['capital'];
                                    
                                    if ($pendienteInteres <= 0 && $pendienteCapital <= 0) continue;
                                    
                                    $pagarInteres = min($montoDisponible, max(0, $pendienteInteres));
                                    $montoDisponible -= $pagarInteres;
                                    $abonosAcumuladosPorCuota[$cuota->id]['interes'] += $pagarInteres;

                                    $pagarCapital = min($montoDisponible, max(0, $pendienteCapital));
                                    $montoDisponible -= $pagarCapital;
                                    $abonosAcumuladosPorCuota[$cuota->id]['capital'] += $pagarCapital;

                                    if ($pagarInteres > 0 || $pagarCapital > 0) {
                                        $relacionesParaInsertar[] = [
                                            'abono_id' => $abono->id,
                                            'prestamo_cuota_id' => $cuota->id,
                                            'monto_abono' => round($pagarInteres + $pagarCapital, 2),
                                            'total_interes' => round($pagarInteres, 2),
                                            'total_capital' => round($pagarCapital, 2),
                                            'total_mora' => 0,
                                            'created_user_id' => $abono->created_user_id,
                                            'fecha_abono' => $abono->created_at,
                                            'estado' => 1,
                                            'tipo_abono' => 1,
                                            'created_at' => $abono->created_at,
                                            'updated_at' => $abono->updated_at
                                        ];

                                        // Verificar si la cuota está completamente pagada usando el acumulado global
                                        $totalAbonadoCuota = $abonosAcumuladosPorCuota[$cuota->id]['interes'] + $abonosAcumuladosPorCuota[$cuota->id]['capital'];
                                        if ($cuota->monto_cuota - $totalAbonadoCuota <= 0.05) {
                                            $cuotasParaActualizar[$cuota->id] = [
                                                'id' => $cuota->id,
                                                'estado' => 3,
                                                'fecha_pagado' => $abono->created_at
                                            ];
                                        }
                                    }
                                }
                            }

                            // INSERCIÓN MASIVA
                            if (!empty($relacionesParaInsertar)) {
                                foreach (array_chunk($relacionesParaInsertar, 500) as $chunk) {
                                    \DB::table('prestamo_cuota_abonos')->insert($chunk);
                                }
                            }
                            
                            // ACTUALIZACIÓN MASIVA de cuotas pagadas
                            foreach ($cuotasParaActualizar as $cuotaUpdate) {
                                \DB::table('prestamo_coutas')
                                    ->where('id', $cuotaUpdate['id'])
                                    ->update([
                                        'estado' => $cuotaUpdate['estado'],
                                        'fecha_pagado' => $cuotaUpdate['fecha_pagado']
                                    ]);
                            }
                            
                            $stats['abonos_redistribuidos']++;
                        }

                        // 7. Actualizar estado del préstamo
                        $totalAbonado = \App\Models\abonosModel::where('prestamo_id', $prestamo->id)
                            ->where('estado', 1)
                            ->sum(\DB::raw('total_efectivo + total_tarjeta + total_cheque + total_transferencia'));
                        
                        $pendiente = $prestamo->monto_financiado - $totalAbonado;
                        
                        if ($pendiente <= 0.05) {
                            if ($prestamo->estado != 2) $stats['cancelados']++;
                            $prestamo->estado = 2;
                        } else {
                            if ($prestamo->estado != 1) $stats['reactivados']++;
                            $prestamo->estado = 1;
                        }
                        $prestamo->save();

                        \DB::commit();
                        $stats['prestamos_recalculados']++;
                        $stats['cuotas_regeneradas'] += count($cuotasNuevas);

                    } catch (\Exception $e) {
                        \DB::rollBack();
                        $stats['fallidos']++;
                        if (count($stats['errores']) < 5) {
                            $stats['errores'][] = "Préstamo #{$prestamo->id}: " . $e->getMessage();
                        }
                        \Log::error("Error recalculando préstamo ID {$prestamo->id}: " . $e->getMessage());
                    }
                }
                
                // Liberar memoria después de cada chunk
                unset($prestamos);
                gc_collect_cycles();
                
                // PROGRESS BAR: Enviar progreso al frontend
                $progreso = round((($chunkIndex + 1) / count($chunks)) * 100, 1);
                $stats['progreso'] = $progreso;
                $stats['chunk_actual'] = $chunkIndex + 1;
                $stats['total_chunks'] = count($chunks);
            }

            return response()->json(array_merge([
                'status' => 'success',
                'message' => '¡Recálculo Completado!'
            ], $stats));

        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }

    public function getPrestamosParaRecalcular()
    {
        $ids = \App\Models\prestamosModel::whereIn('estado', [1, 3])
            ->where('desembolsado', 1)
            ->pluck('id');
            
        return response()->json(['ids' => $ids]);
    }

}
