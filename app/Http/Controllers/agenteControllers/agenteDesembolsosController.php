<?php

namespace App\Http\Controllers\agenteControllers;

use App\Http\Controllers\Controller;
use App\Models\consecutivoModel;
use App\Models\feriados;
use App\Models\negocioTiposModel;
use App\Models\prestamoCuotaAbonoModel;
use App\Models\prestamoCuotasModel;
use App\Models\prestamosModel;
use App\Models\User;
use App\Models\userNegociosModel;
use App\Models\usersFiadoresModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class agenteDesembolsosController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $prestamos = prestamosModel::when($buscar, function ($query) use ($buscar) {
            $query->whereHas('cliente', function ($query2) use ($buscar) {
                $query2->where(function ($query3) use ($buscar) {
                    $query3->where('nombres', 'like', '%' . $buscar . '%')
                        ->orWhere('apellidos', 'like', '%' . $buscar . '%')
                        ->orWhere('prestamos.consecutivo', 'like', '%' . $buscar . '%');
                });
            });
        })
            ->where('desembolsado', 1)
            ->where('agente_id', userLogeado()->id)
            ->orderBy('id', 'desc')
            ->paginate(30);
        return view('agentesViews.desembolsos.agenteDesembolsosIndex', compact('prestamos'));
    }

    public function show($id)
    {
        return redirect()->back();
//       $prestamo = prestamosModel::where('id', decode($id))->first();
//       $vendedores = User::where('tipo_usuario', 2)->get()->pluck('full_name', 'id_enc')->toArray();
//       $cobradores = User::where('tipo_usuario', 4)->get()->pluck('full_name', 'id_enc')->toArray();
//       return view('agentesViews.desembolsos.agentePrestamosVer2',compact('prestamo','vendedores','cobradores'));
    }

    public function create(Request $request)
    {
        $agente = 1;
        $cliente = $request->cliente;
        return view('agentesViews.desembolsos.agentePrestamosNuevo', compact('agente', 'cliente'));
    }

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
                $prestamo->negocio_id = 901;//SIN ASIGNAR
            else {
                $prestamo->negocio_id = decode($request->negocio);
            }

            $prestamo->fecha_prestamo = $request->fechaPrestamo;
            $prestamo->user_desembolso = decode($request->desembolso);
            $prestamo->fecha_desembolso = $request->fechaDesembolso;
            $prestamo->desembolsado = 0;
            $prestamo->estado_aprobacion = 1;
            $prestamo->moneda_prestamo = $request->moneda;
            $prestamo->monto_prestamo = $request->montoFinanciar;

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
            $prestamo->dia_semana_preferido = $request->get('diaSemanaPreferido');

            $prestamo->save();

            // NO CREAR CUOTAS
//
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
                    $banderaAumentar = 15; //aumentar 15 dias
                    break;
                case "4":
                    $formapagovalor = 1;//mensual
                    $banderaAumentar = 30; //aumentar 30 dias
                    break;
                case "5":
                    $formapagovalor = 3;//trimestral
                    $banderaAumentar = 90; //aumentar 30 dias
                    break;
                case "6":
                    $formapagovalor = 2;//bimestral
                    $banderaAumentar = 60; //aumentar 30 dias
                    break;
                case "7":
                    $formapagovalor = 2;//bimestral
                    $banderaAumentar = 14; //aumentar 30 dias
                    break;
            }

            $plazo = $request->plazoPago;
            if (in_array($request->formaPago, ["5", "6"]))
                $numeroCuotas = $plazo / $formapagovalor;
            else
                $numeroCuotas = $plazo * $formapagovalor;

            $fechaCuota = Carbon::createFromFormat('Y-m-d', $request->fechaPago);
            
            // Obtener feriados aplicables para el año del préstamo
            $anioInicio = Carbon::parse($request->fechaPago)->year;
            $fer = $this->obtenerFeriadosAplicables($anioInicio);

            $diasPreferidos = $request->get('diasPago');
            $diaSemanaPreferido = $request->get('diaSemanaPreferido'); // Para SEMANAL y CATORCENAL
            $dia = Carbon::createFromFormat('Y-m-d', $request->fechaPago)->day;
            $diaEvaluar = $dia;
            $diaPreferidoEvaluar = $diasPreferidos;

            $arrFechasMes = [];
            $montoActual = (float)$prestamo->monto_prestamo;
            $montoCuotaSugerido = (float)$request->montoCuota;
            $interesCuotaSugerido = (float)$request->interesPagar;
            $totalAcumulado = 0;

            for ($i = 1; $i <= $numeroCuotas; $i++) {
                $prestamoCuota = new prestamoCuotasModel();
                $prestamoCuota->prestamo_id = $prestamo->id;
                $prestamoCuota->numero_cuota = $i;

                // CORRECCIÓN: La última cuota debe cuadrar el total exacto
                if ($i == $numeroCuotas) {
                    $prestamoCuota->monto_cuota = round($prestamo->monto_financiado - $totalAcumulado, 2);
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
                    if($diasPreferidos > 0 && $request->formaPago==="3")
                        $band=false;
                    else
                        $fechaCuota = $this->calcularFechaFinal($fechaCuota, $banderaAumentar, $fer);
                    
                    // Para SEMANAL y CATORCENAL: Buscar el siguiente día de la semana preferido
                    if (in_array($request->formaPago, ["2", "7"]) && $diaSemanaPreferido) {
                        // Avanzar al siguiente día
                        $fechaCuota->addDay();
                        
                        // Buscar el siguiente día de la semana preferido
                        $diaPreferidoInt = (int)$diaSemanaPreferido;
                        while ($fechaCuota->dayOfWeek !== $diaPreferidoInt) {
                            $fechaCuota->addDay();
                        }
                        
                        // Para CATORCENAL: saltar una semana más (de por medio)
                        if ($request->formaPago === "7") {
                            $fechaCuota->addDays(7);
                        }
                    }
                    
                    // Ajustar si cae en feriado o domingo (SOLO esta cuota)
                    if ($request->formaPago === "1") {
                        // DIARIO: Solo Lun-Vie
                        $fechaCuota = $this->ajustarFechaNoLaborableDiario($fechaCuota, $fer);
                    } else {
                        // OTROS: Lun-Sáb válidos, solo rechaza domingo y feriados
                        do {
                            if ($fechaCuota->dayOfWeek === 0) {
                                $fechaCuota->addDay();
                            }
                            $fechaVerificar = $fechaCuota->toDateString();
                            if (in_array($fechaVerificar, $fer)) {
                                $fechaCuota->addDay();
                            }
                        } while ($fechaCuota->dayOfWeek === 0 || in_array($fechaCuota->toDateString(), $fer));
                    }
                    
                    $arrFechasMes[] = $fechaCuota->month . "-" . $fechaCuota->year;

                    $cadenaBusqueda = $fechaCuota->month . "-" . $fechaCuota->year;
                    $ocurrencias = array_filter($arrFechasMes, function ($fecha) use ($cadenaBusqueda) {
                        return $fecha === $cadenaBusqueda;
                    });

                    if ($request->formaPago === "3" && count($ocurrencias) > 1) {
                        $fechaCuota->setDay(1);
                        $fechaCuota->addMonth();
                    }

                    if ($diasPreferidos > 0 && !in_array($diasPreferidos,["",null,"0",0]) && $request->formaPago === "3") {//intercambiar dias
                        if ($diaEvaluar === $dia)
                            $diaEvaluar = $diaPreferidoEvaluar;
                        else
                            $diaEvaluar = $dia;

                        if ($dia > $diaEvaluar && $i==1 || count(array_filter($arrFechasMes, function ($fecha) use ($fechaCuota) {
                                return $fecha === $fechaCuota->month."-".$fechaCuota->year;
                            }))>1)
                            $fechaCuota->addMonth();

                        $fechaCuota = Carbon::create($fechaCuota->year, $fechaCuota->month, $diaEvaluar);

                        do {
                            if ($fechaCuota->dayOfWeek === 0) {
                                $fechaCuota->addDay();
                            }
                            $fechaVerificar = $fechaCuota->toDateString();
                            if (in_array($fechaVerificar, $fer)) {
                                $fechaCuota->addDay();
                            }
                        } while ($fechaCuota->dayOfWeek === 0 || in_array($fechaCuota->toDateString(), $fer));
                    }

                    $prestamoCuota->fecha_cuota = $fechaCuota->toDateString();

                    if ($diasPreferidos > 0 && $request->formaPago==="3") {
                        $cadenaBusqueda = $fechaCuota->month . "-" . $fechaCuota->year;
                        $ocurrencias = array_filter($arrFechasMes, function ($fecha) use ($cadenaBusqueda) {
                            return $fecha === $cadenaBusqueda;
                        });

                        if (count($ocurrencias) > 1) {
                            $fechaCuota->setDay(1);
                            $fechaCuota->addMonth();
                        } else {
                            $fechaCuota->setDay(1);
                            $fechaCuota->addDays(15);
                        }
                    }
                } else {
                    // Verificar si la primera cuota cae en feriado o domingo
                    $fechaPrimeraCuota = Carbon::createFromFormat('Y-m-d', $request->fechaPago);
                    
                    // Ajustar según tipo de préstamo
                    if ($request->formaPago === "1") {
                        // DIARIO: Solo Lun-Vie, no sábado, no domingo, no feriados
                        $fechaPrimeraCuota = $this->ajustarFechaNoLaborableDiario($fechaPrimeraCuota, $fer);
                    } else {
                        // OTROS: Lun-Sáb válidos, solo rechaza domingo y feriados
                        do {
                            if ($fechaPrimeraCuota->dayOfWeek === 0) {
                                $fechaPrimeraCuota->addDay();
                            }
                            $fechaVerificar = $fechaPrimeraCuota->toDateString();
                            if (in_array($fechaVerificar, $fer)) {
                                $fechaPrimeraCuota->addDay();
                            }
                        } while ($fechaPrimeraCuota->dayOfWeek === 0 || in_array($fechaPrimeraCuota->toDateString(), $fer));
                    }
                    
                    $prestamoCuota->fecha_cuota = $fechaPrimeraCuota->toDateString();
                }

                $prestamoCuota->estado = 1;

                $prestamoCuota->created_user_id = userLogeado()->id;
                $prestamoCuota->save();
            }


            DB::commit();
            return response()->json(['message' => 'Préstamo creado con éxito', 'type' => 'success']);
        } catch (\Exception $ex) {
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

    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $desembolsado = $request->desembolsado_por;

            $prestamo = prestamosModel::where('id', decode($id))->first();

            if ($prestamo->estado_aprobacion == 2 || $prestamo->estado_aprobacion == 3)
                return redirect()->back()->with('error', 'Esta solicitud ya no se puede modificar');

            $prestamo->fecha_prestamo = $request->fechaPrestamo;
            $prestamo->user_desembolso = ($desembolsado) ? decode($desembolsado) : userLogeado()->id;
            $prestamo->fecha_desembolso = $request->fechaDesembolso;//cambiar
            $prestamo->moneda_prestamo = $request->moneda;
            $prestamo->monto_prestamo = $request->montoFinanciar;

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
            $prestamo->tipo_desembolso = $request->tipo_prestamo;

            $prestamo->estado = 1;
            $prestamo->observaciones = $request->comentarios;

            $prestamo->dias_aplicar_mora = $request->dias_mora;
            $prestamo->tipo_mora = $request->moraTipo;
            $prestamo->monto_mora = $request->monto_mora;
            $prestamo->tipo_desembolso = $request->tipo_prestamo;
//            $prestamo->tipo_destino = $request->tipo_destino;

            $prestamo->dias_pago = $request->get('diasPago');
            $prestamo->dia_semana_preferido = $request->get('diaSemanaPreferido');
            $prestamo->update();

            $prestamo->cuotas()->forceDelete();

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
                    $banderaAumentar = 15; //aumentar 15 dias
                    break;
                case "4":
                    $formapagovalor = 1;//mensual
                    $banderaAumentar = 30; //aumentar 30 dias
                    break;
                case "5":
                    $formapagovalor = 3;//trimestral
                    $banderaAumentar = 90; //aumentar 30 dias
                    break;
                case "6":
                    $formapagovalor = 2;//bimestral
                    $banderaAumentar = 60; //aumentar 30 dias
                    break;
                case "7":
                    $formapagovalor = 2;//bimestral
                    $banderaAumentar = 14; //aumentar 30 dias
                    break;
            }

            $plazo = $request->plazoPago;
            if (in_array($request->formaPago, ["5", "6"]))
                $numeroCuotas = $plazo / $formapagovalor;
            else
                $numeroCuotas = $plazo * $formapagovalor;

            $fechaCuota = Carbon::createFromFormat('Y-m-d', $request->fechaPago);
            
            // Obtener feriados aplicables para el año del préstamo
            $anioInicio = Carbon::parse($request->fechaPago)->year;
            $fer = $this->obtenerFeriadosAplicables($anioInicio);

            $diasPreferidos = $request->get('diasPago');
            $diaSemanaPreferido = $request->get('diaSemanaPreferido'); // Para SEMANAL y CATORCENAL
            $dia = Carbon::createFromFormat('Y-m-d', $request->fechaPago)->day;
            $diaEvaluar = $dia;
            $diaPreferidoEvaluar = $diasPreferidos;

            $arrFechasMes = [];
            $montoActual = (float)$prestamo->monto_prestamo;
            $montoCuotaSugerido = (float)$request->montoCuota;
            $interesCuotaSugerido = (float)$request->interesPagar;
            $totalAcumulado = 0;

            for ($i = 1; $i <= $numeroCuotas; $i++) {
                $prestamoCuota = new prestamoCuotasModel();
                $prestamoCuota->prestamo_id = $prestamo->id;
                $prestamoCuota->numero_cuota = $i;
                
                // CORRECCIÓN: La última cuota debe cuadrar el total exacto
                if ($i == $numeroCuotas) {
                    $prestamoCuota->monto_cuota = round($prestamo->monto_financiado - $totalAcumulado, 2);
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
                    if($diasPreferidos > 0 && $request->formaPago==="3")
                        $band=false;
                    else
                        $fechaCuota = $this->calcularFechaFinal($fechaCuota, $banderaAumentar, $fer);
                    
                    // Para SEMANAL y CATORCENAL: Buscar el siguiente día de la semana preferido
                    if (in_array($request->formaPago, ["2", "7"]) && $diaSemanaPreferido) {
                        // Avanzar al siguiente día
                        $fechaCuota->addDay();
                        
                        // Buscar el siguiente día de la semana preferido
                        $diaPreferidoInt = (int)$diaSemanaPreferido;
                        while ($fechaCuota->dayOfWeek !== $diaPreferidoInt) {
                            $fechaCuota->addDay();
                        }
                        
                        // Para CATORCENAL: saltar una semana más (de por medio)
                        if ($request->formaPago === "7") {
                            $fechaCuota->addDays(7);
                        }
                    }
                    
                    // Ajustar si cae en feriado o domingo (SOLO esta cuota)
                    if ($request->formaPago === "1") {
                        // DIARIO: Solo Lun-Vie
                        $fechaCuota = $this->ajustarFechaNoLaborableDiario($fechaCuota, $fer);
                    } else {
                        // OTROS: Lun-Sáb válidos, solo rechaza domingo y feriados
                        do {
                            if ($fechaCuota->dayOfWeek === 0) {
                                $fechaCuota->addDay();
                            }
                            $fechaVerificar = $fechaCuota->toDateString();
                            if (in_array($fechaVerificar, $fer)) {
                                $fechaCuota->addDay();
                            }
                        } while ($fechaCuota->dayOfWeek === 0 || in_array($fechaCuota->toDateString(), $fer));
                    }
                    
                    $arrFechasMes[] = $fechaCuota->month . "-" . $fechaCuota->year;

                    $cadenaBusqueda = $fechaCuota->month . "-" . $fechaCuota->year;
                    $ocurrencias = array_filter($arrFechasMes, function ($fecha) use ($cadenaBusqueda) {
                        return $fecha === $cadenaBusqueda;
                    });

                    if ($request->formaPago === "3" && count($ocurrencias) > 1) {
                        $fechaCuota->setDay(1);
                        $fechaCuota->addMonth();
                    }

                    if ($diasPreferidos > 0 && !in_array($diasPreferidos,["",null,"0",0]) && $request->formaPago === "3") {//intercambiar dias
                        if ($diaEvaluar === $dia)
                            $diaEvaluar = $diaPreferidoEvaluar;
                        else
                            $diaEvaluar = $dia;

                        if ($dia > $diaEvaluar && $i==1 || count(array_filter($arrFechasMes, function ($fecha) use ($fechaCuota) {
                                return $fecha === $fechaCuota->month."-".$fechaCuota->year;
                            }))>1)
                            $fechaCuota->addMonth();

                        $fechaCuota = Carbon::create($fechaCuota->year, $fechaCuota->month, $diaEvaluar);

                        do {
                            if ($fechaCuota->dayOfWeek === 0) {
                                $fechaCuota->addDay();
                            }
                            $fechaVerificar = $fechaCuota->toDateString();
                            if (in_array($fechaVerificar, $fer)) {
                                $fechaCuota->addDay();
                            }
                        } while ($fechaCuota->dayOfWeek === 0 || in_array($fechaCuota->toDateString(), $fer));
                    }

                    $prestamoCuota->fecha_cuota = $fechaCuota->toDateString();

                    if ($diasPreferidos > 0 && $request->formaPago==="3") {
                        $cadenaBusqueda = $fechaCuota->month . "-" . $fechaCuota->year;
                        $ocurrencias = array_filter($arrFechasMes, function ($fecha) use ($cadenaBusqueda) {
                            return $fecha === $cadenaBusqueda;
                        });

                        if (count($ocurrencias) > 1) {
                            $fechaCuota->setDay(1);
                            $fechaCuota->addMonth();
                        } else {
                            $fechaCuota->setDay(1);
                            $fechaCuota->addDays(15);
                        }
                    }
                } else {
                    // Verificar si la primera cuota cae en feriado o domingo
                    $fechaPrimeraCuota = Carbon::createFromFormat('Y-m-d', $request->fechaPago);
                    
                    // Ajustar según tipo de préstamo
                    if ($request->formaPago === "1") {
                        // DIARIO: Solo Lun-Vie, no sábado, no domingo, no feriados
                        $fechaPrimeraCuota = $this->ajustarFechaNoLaborableDiario($fechaPrimeraCuota, $fer);
                    } else {
                        // OTROS: Lun-Sáb válidos, solo rechaza domingo y feriados
                        do {
                            if ($fechaPrimeraCuota->dayOfWeek === 0) {
                                $fechaPrimeraCuota->addDay();
                            }
                            $fechaVerificar = $fechaPrimeraCuota->toDateString();
                            if (in_array($fechaVerificar, $fer)) {
                                $fechaPrimeraCuota->addDay();
                            }
                        } while ($fechaPrimeraCuota->dayOfWeek === 0 || in_array($fechaPrimeraCuota->toDateString(), $fer));
                    }
                    
                    $prestamoCuota->fecha_cuota = $fechaPrimeraCuota->toDateString();
                }

                $prestamoCuota->estado = 1;

                $prestamoCuota->created_user_id = $prestamo->agente_id;
                $prestamoCuota->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Préstamo creado con éxito');
        } catch (\Exception $ex) {
            logger()->error('Error al crear el prestamo ' . $ex->getMessage());
            return redirect()->back()->with('error', 'Error al crear el prestamo');
        }
    }

    public function destroy($id)
    {
        return redirect()->back();
//        try {
//            DB::beginTransaction();
//
//            $prestamo = prestamosModel::where('id', decode($id))->first();
//
//            if ($prestamo->cuotas()->has('abonos')->count())
//                return redirect()->back()->with('error', 'No se puede anular el préstamo, posee registro de abonos');
//
//            $prestamo->estado = 4;//anulado
//            $prestamo->anulado_user_id = userLogeado()->id;//anulado
//            $prestamo->fecha_anulado = Carbon::now();//anulado
//
//            $prestamo->save();
//            DB::commit();
//            return redirect()->back()->with('success', 'Se ha anulado correctamente el préstamo');
//        } catch (\Exception $ex) {
//            DB::rollBack();
//            logger()->error('No se ha podido eliminar el préstamo ' . $ex->getMessage());
//            return redirect()->back()->with('error', 'Ha ocurrido un error al intentar eliminar el préstamo');
//        }
    }

    public function getAgentes()
    {
        return response()->json(['' => '* SELECCIONE UN AGENTE *'] + User::where('tipo_usuario', 4)->get()->pluck('full_name', 'id_enc')->toArray());
    }

    public function getAdministrativos()
    {
        return response()->json(['' => '* SELECCIONE UN USUARIO *'] + User::where('tipo_usuario', 2)->get()->pluck('full_name', 'id_enc')->toArray());
    }

    public function getListAbonos($cuotaId)
    {
        $abonos = prestamoCuotaAbonoModel::where('prestamo_cuota_id', decode($cuotaId))->where('estado', 1)->orderBy('fecha_abono', 'asc')->get();
        if ($abonos)
            return response()->json($abonos);
        else
            return response()->json((object)[]);
    }

    public function getClientesArray()
    {
        return response()->json(['' => '* SELECCIONE UN CLIENTE *'] + User::whereIn('tipo_usuario', [3, 6])->where('estado', 1)->get()->pluck('full_name', 'id_enc')->toArray());
    }

    public function getListNegociosCliente($userId)
    {
        $clienteNegocios = userNegociosModel::where('user_id', decode($userId))->get()->pluck('nombre', 'id_enc')->toArray();
        return response()->json(['' => '*SELECCIONE NEGOCIO*'] + $clienteNegocios);
    }

    public function getDatosCliente($clienteId)
    {
        $user = User::where('id', decode($clienteId))->first();
        $user->dep = $user->departamento_municipio->departamento->nombre;
        $user->mun = $user->departamento_municipio->nombre;
        $user->sexo = $user->sexo == 1 ? 'Femenino' : 'Masculino';
        $user->estado_civil = $user->estado_civil_user;
        $user->prestamoActivo = 0;
        if ($user->prestamos()->where('estado', 1))
            $user->prestamoActivo = 1;
        return response()->json($user);
    }

    public function getInformacionNegocio($negocioID)
    {
        $userNegocio = userNegociosModel::where('id', decode($negocioID))->first();
        $userNegocio->dep = $userNegocio->departamento_municipio->departamento->nombre;
        $userNegocio->mun = $userNegocio->departamento_municipio->nombre;
        return response()->json($userNegocio);
    }

    function storeNegocioCliente(Request $request, $clienteId)
    {
        $objCliente = User::find(decode($clienteId));
        if ($objCliente) {
            $negocioUser = new userNegociosModel();
            $negocioUser->user_id = decode($clienteId);
            $negocioUser->nombre = $request->nombre;

            $negocioUser->municipio_id = decode($request->municipio_id);
            $negocioUser->punto_geografico = $request->punto_geografico;
            $negocioUser->telefono_negocio = $request->telefono_negocio;
            $negocioUser->direccion = $request->direccion;
            $negocioUser->comentarios = $request->comentarios;
            $negocioUser->created_user_id = \Auth::user()->id;

            if ($negocioUser->save()) {
                return response()->json(encode($negocioUser->id));
            }
        }
        return response()->json('Error al intentar guardar el negocio del cliente');
    }

    public function getListTiposNegocios()
    {
        $listTiposNegocios = negocioTiposModel::get()->pluck('nombre', 'id_enc')->toArray();
        return response()->json($listTiposNegocios);
    }

    public function getListFiadoresUser($userId)
    {
        $fiadores = usersFiadoresModel::where('user_id', decode($userId))->get()->pluck('full_name', 'id_enc_fiador')->toArray();
        return response()->json(['' => '**Seleccione un Fiador**'] + $fiadores);
    }

    public function getDatos($userId)
    {
        $userInfo = User::find(decode($userId));
        $userInfo->datos_fiador = $userInfo->fiador_cliente;
        $userInfo->dep_mun_fiador = $userInfo->fiador_cliente->fiador->departamento_municipio->departamento->nombre . " / " . $userInfo->fiador_cliente->fiador->departamento_municipio->nombre;
        return response()->json($userInfo);
    }

    public function simulador()
    {
        return view('agentesViews.simulador.simuladorAgente');
    }

    public function getFeriados()
    {
        $feriados = feriados::all()->pluck('fecha')->map(function($fecha) {
            return Carbon::parse($fecha)->format('Y-m-d');
        })->toArray();
        
        return response()->json($feriados);
    }

    /**
     * Obtener feriados aplicables para el año del préstamo y el siguiente
     */
    protected function obtenerFeriadosAplicables(?int $anio = null): array
    {
        $anio = $anio ?? date('Y');
        $anioFin = $anio + 1;
        return \App\Models\feriados::whereBetween('fecha', [$anio . '-01-01', $anioFin . '-12-31'])
            ->pluck('fecha')
            ->map(fn($f) => \Carbon\Carbon::parse($f)->toDateString())
            ->toArray();
    }

    /**
     * Ajustar fecha para préstamos DIARIOS
     * Solo Lun-Vie son válidos (NO sábado, NO domingo, NO feriados)
     */
    protected function ajustarFechaNoLaborableDiario(\Carbon\Carbon $fecha, array $feriados): \Carbon\Carbon
    {
        $fechaCuota = Carbon::parse($fecha);
        
        while (true) {
            $fechaVerificar = $fechaCuota->toDateString();
            
            // Si es sábado o domingo, avanzar
            if ($fechaCuota->dayOfWeek === 0 || $fechaCuota->dayOfWeek === 6) {
                $fechaCuota->addDay();
                continue;
            }
            
            // Si es feriado, avanzar
            if (in_array($fechaVerificar, $feriados)) {
                $fechaCuota->addDay();
                continue;
            }
            
            // Es un día válido (Lun-Vie, no feriado)
            break;
        }
        
        return $fechaCuota;
    }

}
