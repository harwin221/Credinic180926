<?php

namespace App\Http\Controllers;

use App\Models\abonosModel;
use App\Models\consecutivoModel;
use App\Models\feriados;
use App\Models\prestamoCuotaAbonoModel;
use App\Models\prestamoCuotasModel;
use App\Models\prestamosModel;
use App\Models\solicitudPrestamoModel;
use App\Models\User;
use App\Models\userAsignadoModel;
use App\Models\userNegociosModel;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class prestamosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $clasificacion = $request->get('clasificacion');
        $agentesAsignados = userAsignadoModel::where('user_id', \Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();

        $prestamos = prestamosModel::with('userDesembolso', 'userCreado','cuotas','cliente')->when($buscar, function ($query) use ($buscar) {
            $query->whereHas('cliente', function ($query2) use ($buscar) {
                $query2->where(function ($query3) use ($buscar) {
                    $query3->where('nombres', 'like', '%' . $buscar . '%')
                        ->orWhere('apellidos', 'like', '%' . $buscar . '%')
                        ->orWhere('prestamos.consecutivo', 'like', '%' . $buscar . '%');
                });
            });
        })
            ->where(function ($query) {
                $query->where('estado_aprobacion', 2)
                    ->orwhereNull('estado_aprobacion');
            })
            ->when($clasificacion, function ($query) use ($clasificacion) {
                $query->whereNotNull('motivo_clasificacion');
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('agente_id', $agentesAsignados);
            })
            ->orderBy('id', 'desc')
            ->paginate(30);
        return view('prestamos.prestamosIndex', compact('prestamos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('prestamos.prestamosNuevo');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();


            $prestamo = new prestamosModel();

            $prestamo->consecutivo = consecutivoModel::obtenerConsecutivo();

            $prestamo->user_id = decode($request->cliente);
            $prestamo->agente_id = decode($request->agente);//cobrador
            $prestamo->vendedor_id = decode($request->vendedor);
            $prestamo->fiador_id = ($request->fiador) ? decode($request->fiador) : 3;
            if (!$request->negocio)
                $prestamo->negocio_id = 1;//SIN ASIGNAR
            else {
                $prestamo->negocio_id = decode($request->negocio);
            }

            $prestamo->fecha_prestamo = $request->fechaPrestamo;
            $prestamo->user_desembolso = decode($request->desembolso);//cambiar
            $prestamo->fecha_desembolso = $request->fechaDesembolso;//cambiar
            $prestamo->moneda_prestamo = $request->moneda;
            $prestamo->monto_prestamo = $request->montoFinanciar;

            if ($request->chkSolicitud) {
                $prestamo->desembolsado = 0;
                $prestamo->estado_aprobacion = 1;
            }

            $prestamo->monto_financiado = $request->montoTotalFinanciar;

            $prestamo->forma_pago_tipo = $request->formaPago;
            $prestamo->plazo_pago = $request->plazoPago;
            $prestamo->dia_pago = 1;
            $prestamo->fecha_primer_pago = $request->fechaPago;
            $prestamo->monto_cuota = $request->montoCuota;
            $prestamo->tasa_prestamo = $request->tasaInteres;

            $prestamo->interes_pagar = $request->interesPagar;
            $prestamo->interes_mes = $request->interesMes;
            $prestamo->interes_total_pagar = $request->totalIntereses;

            $prestamo->estado = 1;
            $prestamo->observaciones = $request->comentarios;

            $prestamo->dias_aplicar_mora = $request->dias_mora;
            $prestamo->tipo_mora = $request->moraTipo;
            $prestamo->monto_mora = $request->monto_mora;
            $prestamo->tipo_desembolso = $request->tipo_prestamo;
            $prestamo->tipo_destino = $request->tipo_destino;

            $prestamo->created_user_id = userLogeado()->id;
            $prestamo->dias_pago = $request->get('diasPago');
            $prestamo->dia_pago_preferido = $request->get('dia_pago_preferido');
            $prestamo->dia_semana_preferido = $request->get('diaSemanaPreferido');

            $prestamo->save();

            // GENERACIÓN DE CUOTAS (Lógica centralizada)
            $this->generarPlanPagos($prestamo, $request);

            DB::commit();
            return response()->json(['message' => 'Préstamo creado con éxito', 'type' => 'success']);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el préstamo ' . $ex->getMessage(), 'type' => 'error'], 503);
        }
    }

    function calcularFechaFinal($fechaInicial, $banderaAumentar, $feriados)
    {
        $fechaInicial = Carbon::parse($fechaInicial);
        $fechaCuota = $fechaInicial->copy()->addDays($banderaAumentar);

        $diasLaborables = 0;
        $fechaActual = $fechaInicial->copy();
        while ($fechaActual <= $fechaCuota) {
            if ($fechaActual->isSunday() || $fechaActual->isSaturday()) { // Verifica si es día laborable
                $diasLaborables++;
            }

            $fechaVerificar = $fechaCuota->toDateString();
            if (in_array($fechaVerificar, $feriados)) {
                $fechaCuota->addDay();
            } else
                $fechaActual->addDay();
        }
        // Ajustar la fecha final sumando 15 días laborables
        $fechaCuota = $fechaInicial->copy()->addDays($banderaAumentar);
        return $fechaCuota;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $prestamo = prestamosModel::where('id', decode($id))->first();
        $vendedores = User::admin()->activo()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $cobradores = User::agente()->activo()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();
        $admins = User::whereIn('tipo_usuario', [1, 2, 4, 5])->where('estado', 1)->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $clasificaciones = [
            '1' => 'Incobrable'
        ];

        return view('prestamos.prestamosVer2', compact('prestamo', 'vendedores', 'cobradores', 'clasificaciones', 'admins'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('Editar Desembolsos');
        try {
            DB::beginTransaction();

            $desembolsado = $request->desembolsado_por;

            $prestamo = prestamosModel::where('id', decode($id))->first();

            $prestamo->fecha_prestamo = $request->fechaPrestamo;
            $prestamo->user_desembolso = ($desembolsado) ? decode($desembolsado) : userLogeado()->id;
            $prestamo->fecha_desembolso = $request->fechaDesembolso;//cambiar
            $prestamo->moneda_prestamo = $request->moneda;
            $prestamo->monto_prestamo = $request->montoFinanciar;

            $prestamo->monto_financiado = $request->montoTotalFinanciar;
            $prestamo->tipo_desembolso = $request->tipo_prestamo;
            $prestamo->tipo_destino = $request->tipo_destino;

            $prestamo->forma_pago_tipo = $request->formaPago;
            $prestamo->plazo_pago = $request->plazoPago;
            $prestamo->dia_pago = 1;
            $prestamo->fecha_primer_pago = $request->fechaPago;
            $prestamo->monto_cuota = $request->montoCuota;
            $prestamo->tasa_prestamo = $request->tasaInteres;

            $prestamo->interes_pagar = $request->interesPagar;
            $prestamo->interes_mes = $request->interesMes;
            $prestamo->interes_total_pagar = $request->totalIntereses;

            $prestamo->estado = 1;
            $prestamo->observaciones = $request->comentarios;

            $prestamo->dias_aplicar_mora = $request->dias_mora;
            $prestamo->tipo_mora = $request->moraTipo;
            $prestamo->monto_mora = $request->monto_mora;
            $prestamo->tipo_desembolso = $request->tipo_prestamo;
            $prestamo->tipo_destino = $request->tipo_destino;
            $prestamo->dias_pago = $request->get('diasPago');
            $prestamo->dia_pago_preferido = $request->get('dia_pago_preferido');
            $prestamo->dia_semana_preferido = $request->get('diaSemanaPreferido');

            $prestamo->update();

            // Verificar si hay pagos aplicados
            $tienePagos = $prestamo->cuotas()->has('abonos')->count() > 0;

            // Guardar mapeo de cuotas viejas ANTES de eliminarlas
            $mapeoViejoCuotaIdANumero = [];
            if ($tienePagos) {
                foreach ($prestamo->cuotas as $cuotaVieja) {
                    $mapeoViejoCuotaIdANumero[$cuotaVieja->id] = $cuotaVieja->numero_cuota;
                }
            }

            if ($prestamo->cuotas()->has('abonos')->count() == 0 || $tienePagos) {
                $prestamo->cuotas()->forceDelete();

                // GENERACIÓN DE CUOTAS (Lógica centralizada)
                $this->generarPlanPagos($prestamo, $request);

                // Si tenía pagos, intentar re-vincularlos
                if ($tienePagos) {

                // ===== SI HABÍA PAGOS, REDISTRIBUIR SECUENCIALMENTE =====
                if ($tienePagos && !empty($mapeoViejoCuotaIdANumero)) {
                    // Obtener todos los ABONOS (recibos de pago) del préstamo en orden
                    $abonos = abonosModel::where('prestamo_id', $prestamo->id)
                        ->where('estado', 1) // Solo activos, no anulados
                        ->orderBy('id', 'asc')
                        ->get();

                    // Eliminar TODAS las relaciones viejas (quedan huérfanas temporalmente)
                    prestamoCuotaAbonoModel::whereIn('prestamo_cuota_id', array_keys($mapeoViejoCuotaIdANumero))
                        ->forceDelete();

                    // Limpieza adicional: eliminar cualquier registro huérfano cuya cuota ya no exista
                    // (cuotas que no tenían abonos no entran en el mapeo pero sus IDs pueden quedar basura)
                    prestamoCuotaAbonoModel::whereHas('prestamo_cuota', function($q) use ($prestamo) {
                        // noop — el whereHas filtra solo los que SÍ tienen cuota
                    })->get(); // no hace nada, el cleanup real es el siguiente:
                    DB::table('prestamo_cuota_abono')
                        ->join('abonos as a', 'a.id', '=', 'prestamo_cuota_abono.abono_id')
                        ->leftJoin('prestamo_coutas as pc', 'pc.id', '=', 'prestamo_cuota_abono.prestamo_cuota_id')
                        ->where('a.prestamo_id', $prestamo->id)
                        ->whereNull('pc.id')
                        ->delete();

                    // Obtener las nuevas cuotas ordenadas
                    $cuotasNuevas = prestamoCuotasModel::where('prestamo_id', $prestamo->id)
                        ->orderBy('numero_cuota', 'asc')
                        ->get();

                    // REDISTRIBUIR cada ABONO secuencialmente (igual que abonoController::store)
                    foreach ($abonos as $abono) {
                        $valorCuota = $abono->total_efectivo + $abono->total_tarjeta + $abono->total_cheque + $abono->total_transferencia;
                        $ultimaCuotaPagadaCompletamente = null;

                        foreach ($cuotasNuevas as $cuota) {
                            if ($valorCuota > 0 && $cuota->monto_pendiente_cuota > 0) {
                                // Refrescar cuota para obtener valores actualizados
                                $cuota->refresh();
                                $cuota->load('abonos');

                                $totalAbonoIntereses = $cuota->total_pendiente_interes_cuota;
                                $totalAbonoCapital = $cuota->total_pendiente_capital_cuota;
                                $totalAbonoMora = $cuota->total_pendiente_mora_cuota;

                                $montoAbonarInteres = 0;
                                $montoAbonarCapital = 0;
                                $montoAbonarMora = 0;
                                $totalAbonado = 0;

                                // Aplicar primero a INTERESES
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

                                // Luego aplicar a CAPITAL
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

                                // Finalmente aplicar a MORA
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

                                // Crear la nueva relación (puede haber múltiples por abono)
                                if ($totalAbonado > 0) {
                                    $abonoCuota = new prestamoCuotaAbonoModel();
                                    $abonoCuota->abono_id = $abono->id;
                                    $abonoCuota->prestamo_cuota_id = $cuota->id;
                                    $abonoCuota->estado = 1;
                                    $abonoCuota->monto_abono = $totalAbonado;
                                    $abonoCuota->total_interes = $montoAbonarInteres;
                                    $abonoCuota->total_capital = $montoAbonarCapital;
                                    $abonoCuota->total_mora = $montoAbonarMora;
                                    $abonoCuota->tipo_abono = 1;
                                    $abonoCuota->fecha_abono = $abono->created_at;
                                    $abonoCuota->created_user_id = $abono->created_user_id;
                                    $abonoCuota->created_at = $abono->created_at;
                                    $abonoCuota->updated_at = $abono->updated_at;
                                    $abonoCuota->save();
                                }

                                // Actualizar estado de la cuota
                                $cuota->refresh();
                                if ($cuota->monto_pendiente_cuota == 0) {
                                    $cuota->estado = 3; // PAGADA
                                    $cuota->fecha_pagado = $abono->created_at;
                                    $ultimaCuotaPagadaCompletamente = $cuota;
                                    $cuota->save();
                                }
                            }
                        }
                    }

                    // Verificar si el préstamo está completamente pagado
                    $prestamo->refresh();
                    if ($prestamo->pendiente_abono <= 0) {
                        $prestamo->estado = 2; // CANCELADO
                        $prestamo->save();
                    } else {
                        $prestamo->estado = 1; // ACTIVO
                        $prestamo->save();
                    }
                }
            }
        }

        DB::commit();
        return redirect()->back()->with('success', 'Préstamo actualizado con éxito');
        } catch (\Exception $ex) {
            logger()->error('Error al actualizar el prestamo ' . $ex->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar el prestamo: ' . $ex->getMessage());
        }
    }

    /**
     * Actualizar datos básicos de una solicitud pendiente (estado_aprobacion = 1, no desembolsado).
     * Requiere permiso 'Editar Solicitudes' — separado del permiso de Editar Desembolsos (Admin).
     * No regenera cuotas ni redistribuye abonos.
     */
    public function updateSolicitud(Request $request, string $id)
    {
        $this->authorize('Editar Solicitudes');
        try {
            DB::beginTransaction();

            $prestamo = prestamosModel::where('id', decode($id))->first();

            if (!$prestamo)
                return redirect()->back()->with('error', 'Solicitud no encontrada');

            // Solo permitir editar si la solicitud está pendiente y no ha sido desembolsada
            if ($prestamo->desembolsado == 1 || $prestamo->estado_aprobacion != 1)
                return redirect()->back()->with('error', 'Solo se pueden editar solicitudes pendientes no desembolsadas');

            $prestamo->fecha_prestamo       = $request->fechaPrestamo;
            $prestamo->fecha_desembolso     = $request->fechaDesembolso;
            $prestamo->moneda_prestamo      = $request->moneda;
            $prestamo->monto_prestamo       = $request->montoFinanciar;
            $prestamo->monto_financiado     = $request->montoTotalFinanciar;
            $prestamo->forma_pago_tipo      = $request->formaPago;
            $prestamo->plazo_pago           = $request->plazoPago;
            $prestamo->fecha_primer_pago    = $request->fechaPago;
            $prestamo->monto_cuota          = $request->montoCuota;
            $prestamo->tasa_prestamo        = $request->tasaInteres;
            $prestamo->interes_pagar        = $request->interesPagar;
            $prestamo->interes_mes          = $request->interesMes;
            $prestamo->interes_total_pagar  = $request->totalIntereses;
            $prestamo->tipo_desembolso      = $request->tipo_prestamo;
            $prestamo->tipo_destino         = $request->tipo_destino;
            $prestamo->dias_pago            = $request->get('diasPago');
            $prestamo->dia_pago_preferido   = $request->get('dia_pago_preferido');
            $prestamo->dia_semana_preferido = $request->get('diaSemanaPreferido');
            $prestamo->user_desembolso      = $request->desembolsado_por ? decode($request->desembolsado_por) : userLogeado()->id;

            $prestamo->update();

            // Regenerar el plan de pagos (no hay abonos en una solicitud pendiente)
            $prestamo->cuotas()->forceDelete();
            $this->generarPlanPagos($prestamo, $request);

            DB::commit();
            return redirect()->back()->with('success', 'Datos de la solicitud actualizados con éxito');
        } catch (\Exception $ex) {
            DB::rollBack();
            logger()->error('Error al actualizar la solicitud ' . $ex->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar la solicitud: ' . $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)//anular
    {
        try {
            DB::beginTransaction();

            $prestamo = prestamosModel::where('id', decode($id))->first();

            if ($prestamo->cuotas()->has('abonos')->count())
                return redirect()->back()->with('error', 'No se puede anular el préstamo, posee registro de abonos');

            $prestamo->estado = 4;//anulado
            $prestamo->anulado_user_id = userLogeado()->id;//anulado
            $prestamo->fecha_anulado = Carbon::now();//anulado

            $prestamo->save();
            DB::commit();
            return redirect()->back()->with('success', 'Se ha anulado correctamente el préstamo');
        } catch (\Exception $ex) {
            DB::rollBack();
            logger()->error('No se ha podido eliminar el préstamo ' . $ex->getMessage());
            return redirect()->back()->with('error', 'Ha ocurrido un error al intentar eliminar el préstamo');
        }
    }

    public function eliminar(string $id)//eliminar fisico
    {
        $this->authorize('Eliminar Desembolsos');
        try {
            DB::beginTransaction();

            $prestamo = prestamosModel::where('id', decode($id))->first();

            if (!$prestamo)
                return redirect()->back()->with('error', 'Préstamo no encontrado');

            if ($prestamo->cuotas()->has('abonos')->count())
                return redirect()->back()->with('error', 'No se puede eliminar el préstamo, posee registro de abonos');

            $prestamo->cuotas()->forceDelete();
            $prestamo->forceDelete();

            DB::commit();
            return redirect()->back()->with('success', 'Se ha eliminado correctamente el préstamo');
        } catch (\Exception $ex) {
            DB::rollBack();
            logger()->error('No se ha podido eliminar el préstamo ' . $ex->getMessage());
            return redirect()->back()->with('error', 'Ha ocurrido un error al intentar eliminar el préstamo');
        }
    }

    public function registrarAbono(Request $request, $cuotaId)
    {

    }

    public function storeReprestamo(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            $prestamo = prestamosModel::where('id', decode($id))->first();
            $copiaPrestamo = $prestamo->replicate();

            $copiaPrestamo->consecutivo = consecutivoModel::obtenerConsecutivo();

            $copiaPrestamo->represtamo = 1;
            $copiaPrestamo->user_id = $prestamo->user_id;
            $copiaPrestamo->agente_id = decode($request->agente);//cobrador
            $copiaPrestamo->vendedor_id = decode($request->vendedor);
//            $copiaPrestamo->fiador_id = $prestamo->fiador_id;

            $copiaPrestamo->fecha_prestamo = $request->fechaPrestamo;
            $copiaPrestamo->user_desembolso = decode($request->desembolso);//cambiar
            $copiaPrestamo->fecha_desembolso = $request->fechaDesembolso;//cambiar
            $copiaPrestamo->moneda_prestamo = $request->moneda;
            $copiaPrestamo->monto_prestamo = $request->montoFinanciar;

            $copiaPrestamo->monto_financiado = $request->montoTotalFinanciar;

            $copiaPrestamo->forma_pago_tipo = $request->formaPago;
            $copiaPrestamo->plazo_pago = $request->plazoPago;
            $copiaPrestamo->dia_pago = 1;
            $copiaPrestamo->fecha_primer_pago = $request->fechaPago;
            $copiaPrestamo->monto_cuota = $request->montoCuota;
            $copiaPrestamo->tasa_prestamo = $request->tasaInteres;

            $copiaPrestamo->interes_pagar = $request->interesPagar;
            $copiaPrestamo->interes_mes = $request->interesMes;
            $copiaPrestamo->interes_total_pagar = $request->totalIntereses;

            $copiaPrestamo->estado = 1;
            $copiaPrestamo->observaciones = $request->comentarios;

            $copiaPrestamo->dias_aplicar_mora = $request->dias_mora;
            $copiaPrestamo->tipo_mora = $request->moraTipo;
            $copiaPrestamo->monto_mora = $request->monto_mora;

            $copiaPrestamo->created_user_id = userLogeado()->id;
            $copiaPrestamo->save();

            $banderaAumentar = 0;
            switch ($request->formaPago) {
                case "1":
                    $formapagovalor = 20;//diario
                    $banderaAumentar = 1;//aumentar 1 dia
                    break;
                case "2":
                    $formapagovalor = 4;//semanal
                    $banderaAumentar = 7; //aumentar 7 dias
                    break;
                case "3":
                    $formapagovalor = 2;//quincenal
                    $banderaAumentar = 14; //aumentar 15 dias
                    break;
                case "4":
                    $formapagovalor = 1;//mensual
                    $banderaAumentar = 30; //aumentar 30 dias
                    break;
            }

            $plazo = $request->plazoPago;
            $numeroCuotas = $plazo * $formapagovalor;
            $fechaCuota = Carbon::createFromFormat('Y-m-d', $request->fechaPago);

            // Cargar feriados considerando recurrentes
            $anioBase = $fechaCuota->year;
            $feriadosReprestamo = [];
            for ($anio = $anioBase; $anio <= $anioBase + 2; $anio++) {
                $feriadosReprestamo = array_merge($feriadosReprestamo, $this->obtenerFeriadosAplicables($anio));
            }
            $feriadosReprestamo = array_unique($feriadosReprestamo);

            $montoActual = (float)$copiaPrestamo->monto_prestamo;
            $montoCuotaSugerido = (float)$request->montoCuota;
            $interesCuotaSugerido = (float)$request->interesPagar;
            $totalAcumulado = 0;

            for ($i = 1; $i <= $numeroCuotas; $i++) {
                $prestamoCuota = new prestamoCuotasModel();
                $prestamoCuota->prestamo_id = $copiaPrestamo->id;
                $prestamoCuota->numero_cuota = $i;

                // CORRECCIÓN: La última cuota debe cuadrar el total exacto
                if ($i == $numeroCuotas) {
                    $prestamoCuota->monto_cuota = round($copiaPrestamo->monto_financiado - $totalAcumulado, 2);
                    $abonoCapital = round($prestamoCuota->monto_cuota - $interesCuotaSugerido, 2);
                } else {
                    $prestamoCuota->monto_cuota = $montoCuotaSugerido;
                    $abonoCapital = round($montoCuotaSugerido - $interesCuotaSugerido, 2);
                    $totalAcumulado += $montoCuotaSugerido;
                }

                $prestamoCuota->monto_interes = $interesCuotaSugerido;
                $montoActual = round($montoActual - $abonoCapital, 2);

                $prestamoCuota->forma_pago = 1;

                if ($i != 1) {
                    $fechaCuota->addDays($banderaAumentar);
                }
                // Saltar fines de semana y feriados (igual que generarPlanPagos)
                while ($fechaCuota->dayOfWeek === 0 || $fechaCuota->dayOfWeek === 6 || in_array($fechaCuota->toDateString(), $feriadosReprestamo)) {
                    $fechaCuota->addDay();
                }
                $prestamoCuota->fecha_cuota = $fechaCuota->toDateString();

                $prestamoCuota->estado = 1;

                $prestamoCuota->created_user_id = userLogeado()->id;
                $prestamoCuota->save();
            }

            DB::commit();

            return redirect()->route('prestamos.index');

        } catch (\Exception $exception) {
            DB::rollBack();
            logger()->error('Ha ocurrido un error al intentar realizar el represtamo ' . $exception->getMessage());
            return redirect()->back()->with('error', 'Ha ocurrido un error al intentar realizar el represtamo');
        }
    }

    public function getListAbonos($cuotaId)
    {
        $abonos = prestamoCuotaAbonoModel::where('prestamo_cuota_id', decode($cuotaId))->where('estado', 1)->orderBy('fecha_abono', 'asc')->get();
        if ($abonos)
            return response()->json($abonos);
        else
            return response()->json((object)[]);
    }

    public function actualizarVendedorCobradorPrestamo(Request $request, $id)
    {
        $prestamo = prestamosModel::where('id', decode($id))->first();
        if ($request->tipo == 'cobrador')
            $prestamo->agente_id = decode($request->usuario);
        elseif ($request->tipo == 'vendedor')
            $prestamo->vendedor_id = decode($request->usuario);
        $prestamo->save();

        return redirect()->back()->with('success', 'Se ha modificado el préstamo');
    }

    //SOLICITUDES
    public function indexSolicitudes(Request $request)
    {
        $buscar = $request->buscar;
        $estado = $request->estado;

        $agentesAsignados = userAsignadoModel::where('user_id', \Auth::user()->id)
            ->get()->pluck('admin_asignado_id')->toArray();

        if (!$estado)
            $estado = 1;

        $prestamos = prestamosModel::when($estado != 4, function ($query) use ($estado) {
                $query->where('estado_aprobacion', $estado);
            })
            ->when($buscar, function ($query) use ($buscar) {
                $query->whereHas('cliente', function ($query) use ($buscar) {
                    $query->where('nombres', 'like', '%' . $buscar . '%')
                        ->orWhere('apellidos', 'like', '%' . $buscar . '%');
                });
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('agente_id', $agentesAsignados);
            })
            ->where('estado_aprobacion', '!=', null)
            ->orderBy('id', 'desc')
            ->paginate(30);

        $solicitudesPendientes = prestamosModel::where('estado_aprobacion', 1)->where('desembolsado', 0)
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('agente_id', $agentesAsignados);
            })
            ->orderBy('id', 'desc')->get();

        $solicitudesAprobadasHoy = prestamosModel::where('estado_aprobacion', 2)
            ->whereDate('created_at', Carbon::now()->toDateString())
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('agente_id', $agentesAsignados);
            })
            ->orderBy('id', 'desc')->get();

        $solicitudesAprobadas = $solicitudesAprobadasHoy->count();

        $solicitudesRechazadasHoy = prestamosModel::where('estado_aprobacion', 3)
            ->whereDate('created_at', Carbon::now()->toDateString())
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereIn('agente_id', $agentesAsignados);
            })
            ->orderBy('id', 'desc')->get();

        $solicitudesRechazadas = $solicitudesRechazadasHoy->count();

        return view('prestamos.solicitudes.indexSolicitud', compact('prestamos', 'solicitudesAprobadas', 'solicitudesRechazadas', 'solicitudesPendientes', 'solicitudesAprobadasHoy', 'solicitudesRechazadasHoy'));
    }

    public function cambiarEstadoSolicitud($solicitud, $estado, $comentario = '')
    {
        if (!in_array($estado, [2, 3]))  //evitar que se inyecte un estado no valido
            return redirect()->back()->with('error', 'Estado no registrado');

        prestamosModel::where('id', decode($solicitud))->update(['estado_aprobacion' => $estado, 'comentarios_rechazado' => $comentario, 'updated_user_id' => userLogeado()->id]);
        $prestamo = prestamosModel::where('id', decode($solicitud))->first();
        if ($prestamo->cliente->tipo_usuario == 6 && $estado == 2) {
            $prestamo->cliente->tipo_usuario = 3;
            $prestamo->cliente->save();
        }
        return redirect()->back();
    }

    public function prestamoDesembolsado($id)//marcar prestamo como desembolsado
    {
        try {
            DB::beginTransaction();
            $prestamo = prestamosModel::where('id', decode($id))->first();
            if (!$prestamo) {
                return redirect()->back()->with('error', 'No se encontró el préstamo');
            }
            if ($prestamo->estado_aprobacion == 1) { //solicitud pendiente
                return redirect()->back()->with('error', 'Para desembolsar este préstamo primero tiene que aprobar la solicitud');
            }

            $prestamo->desembolsado = 1;
            $prestamo->user_desembolso = \Auth::id();
            $prestamo->fecha_desembolso = date('Y-m-d');
            $prestamo->save();

            // Auto-cancelar crédito(s) anterior(es) activo(s) del cliente si tiene saldo pendiente
            $gestorCobradorId = $prestamo->agente_id ?? \Auth::id();
            $prestamosAnteriores = prestamosModel::where('user_id', $prestamo->user_id)
                ->where('estado', 1)
                ->where('desembolsado', 1)
                ->where('id', '!=', $prestamo->id)
                ->get();

            foreach ($prestamosAnteriores as $pAnt) {
                $cuotasPendientes = prestamoCuotasModel::where('prestamo_id', $pAnt->id)
                    ->whereIn('estado', [1, 2])
                    ->orderBy('numero_cuota', 'asc')
                    ->get();

                $totalSaldoPendiente = 0;
                foreach ($cuotasPendientes as $cp) {
                    $totalSaldoPendiente += (float)($cp->monto_pendiente_cuota > 0 ? $cp->monto_pendiente_cuota : $cp->monto_cuota);
                }

                if ($totalSaldoPendiente > 0 && count($cuotasPendientes) > 0) {
                    $abono = new abonosModel();
                    $abono->prestamo_id = $pAnt->id;
                    $abono->fecha_abono = Carbon::now();
                    $abono->tipo_abono = 3; // 3: Cancelación / Représtamo
                    $abono->estado = 1;
                    $abono->anulado_user_id = null;
                    $abono->created_user_id = $gestorCobradorId;
                    $abono->total_efectivo = $totalSaldoPendiente;
                    $abono->total_tarjeta = 0;
                    $abono->total_cheque = 0;
                    $abono->total_transferencia = 0;
                    $abono->referencia_tarjeta = '';
                    $abono->referencia_cheque = '';
                    $abono->referencia_transferencia = 'Cancelación Crédito #' . ($prestamo->consecutivo ?? $prestamo->id);
                    $abono->save();

                    foreach ($cuotasPendientes as $cuota) {
                        $capitalPend = (float)($cuota->total_pendiente_capital_cuota ?? max(0, $cuota->monto_cuota - $cuota->monto_interes));
                        $interesPend = (float)($cuota->total_pendiente_interes_cuota ?? $cuota->monto_interes);
                        $moraPend    = (float)($cuota->total_pendiente_mora_cuota ?? $cuota->monto_mora ?? 0);
                        $montoAbonoCuota = (float)($cuota->monto_pendiente_cuota > 0 ? $cuota->monto_pendiente_cuota : ($capitalPend + $interesPend + $moraPend));

                        $abonoCuota = new prestamoCuotaAbonoModel();
                        $abonoCuota->abono_id = $abono->id;
                        $abonoCuota->prestamo_cuota_id = $cuota->id;
                        $abonoCuota->monto_abono = $montoAbonoCuota;
                        $abonoCuota->fecha_abono = Carbon::now();
                        $abonoCuota->tipo_abono = 1;
                        $abonoCuota->estado = 1;
                        $abonoCuota->total_capital = $capitalPend;
                        $abonoCuota->total_interes = $interesPend;
                        $abonoCuota->total_mora = $moraPend;
                        $abonoCuota->created_user_id = $gestorCobradorId;
                        $abonoCuota->save();

                        $cuota->estado = 3; // Pagada
                        $cuota->fecha_pagado = Carbon::now();
                        $cuota->save();
                    }

                    $pAnt->estado = 2; // Pagado / Cancelado
                    $pAnt->save();
                } else {
                    $pAnt->estado = 2;
                    $pAnt->save();
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Se ha marcado como desembolsado y se canceló el crédito anterior.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al desembolsar préstamo: ' . $e->getMessage());
        }
    }

    public function simulador()
    {
        return view('prestamos.simulador.simulador');
    }

    public function createSolicitud()
    {
        return view('solicitudes.createSolicitud');
    }

    function clasificar_prestamo(Request $request, $id)
    {
        $prestamo = prestamosModel::where('id', decode($id))->first();

        if ($request->get('desclasificar')) {
            $prestamo->clasificacion = null;
            $prestamo->motivo_clasificacion = null;
            $prestamo->fecha_clasificacion = null;
            $prestamo->clasificado_por = null;
        } else {
            if (!$request->get('clasificacion') || !$request->get('motivo_clasificacion') || !$request->get('fecha_clasificacion'))
                return redirect()->back()->with('error', 'Deben llenarse todos los campos');
            $prestamo->clasificacion = mb_strtoupper($request->get('clasificacion'));
            $prestamo->motivo_clasificacion = mb_strtoupper($request->get('motivo_clasificacion'));
            $prestamo->fecha_clasificacion = $request->get('fecha_clasificacion');
            $prestamo->clasificado_por = \Auth::user()->id;
        }
        $prestamo->update();

        return redirect()->back();
    }


    public function editarFechaCuota(Request $request, $id)
    {
        $cuota = prestamoCuotasModel::where('id', decode($id))->first();
        $cuota->user_edit_fecha .= "fecha editada por: ".(\Auth::user()->full_name) . ", fecha anterior: ". $cuota->fecha_cuota.", aplicado:" . Carbon::now()->toDateTimeString()."\n\n";
        $cuota->fecha_cuota = $request->fecha_cuota;
        $cuota->update();
        return redirect()->back();
    }

    /**
     * Obtener feriados aplicables para el año del préstamo y el siguiente
     */
    /**
     * Genera el plan de pagos (cuotas) para un préstamo
     * Implementa la lógica de "Retorno al Ciclo" tras feriados/domingos
     */
    public function generarPlanPagos($prestamo, $request)
    {
        $formaPago = $request->formaPago;
        $plazo = (float)$request->plazoPago;
        $montoCuotaSugerido = (float)$request->montoCuota;
        $interesCuota = (float)$request->interesPagar;
        $montoActual = (float)$prestamo->monto_prestamo;

        // 1. Determinar número de cuotas y salto de días
        $banderaAumentar = 0;
        $numeroCuotas = 0;

        switch ($formaPago) {
            case "1": // Diario
                $numeroCuotas = $plazo * 20;
                $banderaAumentar = 1;
                break;
            case "2": // Semanal
                $numeroCuotas = $plazo * 4;
                $banderaAumentar = 7;
                break;
            case "3": // Quincenal
                $numeroCuotas = $plazo * 2;
                $banderaAumentar = 15;
                break;
            case "7": // Catorcenal
                $numeroCuotas = $plazo * 2;
                $banderaAumentar = 14;
                break;
            default:
                $numeroCuotas = $plazo;
                $banderaAumentar = 30;
                break;
        }

        // 2. Cargar feriados para todos los años que cubre el plan (recurrentes y no recurrentes)
        $anioBase = Carbon::parse($request->fechaPago)->year;
        $anioFin  = $anioBase + 2;
        $feriados = [];
        for ($anio = $anioBase; $anio <= $anioFin; $anio++) {
            $feriados = array_merge($feriados, $this->obtenerFeriadosAplicables($anio));
        }
        $feriados = array_unique($feriados);

        // 3. Inicializar fechas
        $fechaReferencia = Carbon::parse($request->fechaPago);

        // --- INFERENCIA INTELIGENTE ---
        $diaSemanaPreferido = (isset($request->diaSemanaPreferido) ? $request->diaSemanaPreferido : null) ?? $prestamo->dia_semana_preferido;

        // Si es semanal/catorcenal y no hay día preferido, lo inferimos de la fecha inicial
        if (in_array($formaPago, ["2", "7"]) && empty($diaSemanaPreferido)) {
            $diaSemanaPreferido = $fechaReferencia->dayOfWeek; // 0=Dom, 1=Lun...
            // Guardamos el hallazgo para que ya no sea null
            $prestamo->dia_semana_preferido = $diaSemanaPreferido;
            $prestamo->save();
        }

        // Ajustar la fechaReferencia al día preferido si es Semanal/Catorcenal
        if (in_array($formaPago, ["2", "7"]) && !empty($diaSemanaPreferido)) {
            $prefInt = (int)$diaSemanaPreferido;
            while ($fechaReferencia->dayOfWeek !== $prefInt) {
                $fechaReferencia->addDay();
            }
        }

        $diasPreferidosQuincenal = (isset($request->dia_pago_preferido) ? $request->dia_pago_preferido : null) ?? $prestamo->dia_pago_preferido ?? (isset($request->diasPago) ? $request->diasPago : null) ?? $prestamo->dias_pago;
        $diaInicialOriginal = $fechaReferencia->day;

        // 4. Calcular el total acumulado para ajustar la última cuota
        $totalAcumulado = 0;

        // 5. Generar cuotas
        for ($i = 1; $i <= $numeroCuotas; $i++) {
            $cuota = new prestamoCuotasModel();
            $cuota->prestamo_id = $prestamo->id;
            $cuota->numero_cuota = $i;

            // CORRECCIÓN: La última cuota debe cuadrar el total exacto
            if ($i == $numeroCuotas) {
                // Última cuota = Total financiado - Suma de cuotas anteriores
                $cuota->monto_cuota = round($prestamo->monto_financiado - $totalAcumulado, 2);
                $abonoCapital = round($cuota->monto_cuota - $interesCuota, 2);
            } else {
                $cuota->monto_cuota = $montoCuotaSugerido;
                $abonoCapital = round($montoCuotaSugerido - $interesCuota, 2);
                $totalAcumulado += $montoCuotaSugerido;
            }

            $cuota->monto_interes = $interesCuota;
            $montoActual = round($montoActual - $abonoCapital, 2);

            $cuota->forma_pago = 1;
            $cuota->estado = 1;
            $cuota->created_user_id = userLogeado()->id;

            if ($formaPago === "1") {
                // DIARIO: Solo Lun-Vie (días hábiles consecutivos sin repetir fechas)
                while ($fechaReferencia->dayOfWeek === 0 || $fechaReferencia->dayOfWeek === 6 || in_array($fechaReferencia->toDateString(), $feriados)) {
                    $fechaReferencia->addDay();
                }
                $cuota->fecha_cuota = $fechaReferencia->toDateString();
                $cuota->save();

                // Para la siguiente cuota, avanzar al siguiente día calendario (el while saltará fines de semana/feriados)
                $fechaReferencia->addDay();
            } else {
                // OTRAS FRECUENCIAS: Salta Dom y Feriados
                $fechaReal = $fechaReferencia->copy();
                while ($fechaReal->dayOfWeek === 0 || in_array($fechaReal->toDateString(), $feriados)) {
                    $fechaReal->addDay();
                }

                $cuota->fecha_cuota = $fechaReal->toDateString();
                $cuota->save();

                // 6. Mover REFERENCIA
                if ($formaPago === "3" && $diasPreferidosQuincenal > 0) {
                    // Quincenal
                    if ($fechaReferencia->day == $diaInicialOriginal) {
                        $fechaReferencia->day = $diasPreferidosQuincenal;
                        if ($diasPreferidosQuincenal < $diaInicialOriginal) {
                            $fechaReferencia->addMonth();
                        }
                    } else {
                        $fechaReferencia->day = $diaInicialOriginal;
                        if ($diaInicialOriginal < $diasPreferidosQuincenal) {
                            $fechaReferencia->addMonth();
                        }
                    }
                } else {
                    $fechaReferencia->addDays($banderaAumentar);
                }
            }
    }

}
