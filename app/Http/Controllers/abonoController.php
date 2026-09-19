<?php

namespace App\Http\Controllers;

use App\Models\abonosModel;
use App\Models\prestamoCuotaAbonoModel;
use App\Models\prestamoCuotasModel;
use App\Models\prestamosModel;
use App\Models\User;
use App\Models\userAsignadoModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\once;
use function Termwind\terminal;

class abonoController extends Controller
{

    public function getListClientes(Request $request)
    {
        $buscar = $request->buscar;
        $clientes = User::cliente()
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($query2) use ($buscar) {
                    $query2->where('nombres', 'like', '%' . $buscar . '%')
                        ->orwhere('apellidos', 'like', '%' . $buscar . '%')
                        ->orwhere('cedula', 'like', '%' . $buscar . '%');
                });
            })->paginate(15);
        return view('abonos.listaClientes', compact('clientes'))->render();
    }

    public function getInfoCliente(Request $request)
    {
        $user_id = decode($request->clienteId);
        $user = User::where('id', $user_id)->first();
        $user->dep = $user->departamento_municipio->departamento->nombre;
        $user->mun = $user->departamento_municipio->nombre;
        $datos = [
            'userData' => $user,
            'prestamoActivos' => prestamosModel::where('user_id', $user_id)->where('desembolsado',1)->where('estado', 1)->get()
        ];
        return response()->json($datos);
    }

    public function getCuotasPendientesPrestamos(Request $request)
    {
        $prestamoId = $request->prestamoId;

        $prestamoCuotas = prestamoCuotasModel::with('prestamo')->where('prestamo_id', $prestamoId)->get();
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
        if ($prestamo) {
            $prestamoObj = prestamosModel::where('id', $prestamo)->first();
            if ($prestamoObj) {
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
            'total_pendiente' => $totalPendiente,
            'moneda' => $moneda
        ];
        return response()->json($objPendiente);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $agentesAsignados = userAsignadoModel::where('user_id', Auth::user()->id)->get()->pluck('admin_asignado_id')->toArray();

        $listaCobradores = User::whereIn('tipo_usuario', [2, 4])
            ->when($agentesAsignados, function ($query) use ($agentesAsignados) {
                $query->whereIn('id', $agentesAsignados);
            })
            ->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray();

        $buscar = $request->buscar;
        $cobrador = $request->get('cobrador');
        $abonos = abonosModel::with('prestamo','prestamo.cliente','user_create','prestamo.agente')->when($buscar, function ($query) use ($buscar) {
            $query->whereHas('prestamo', function ($query) use ($buscar) {
                $query->whereHas('cliente', function ($query) use ($buscar) {
                    $query->where('nombres', 'like', '%' . $buscar . '%')
                        ->orWhere('apellidos', 'like', '%' . $buscar . '%');
                });
            });
        })
            ->when($cobrador,function ($query) use ($cobrador){
                $query->where('created_user_id',decode($cobrador));
            })
            ->when(count($agentesAsignados), function ($query) use ($agentesAsignados) {
                $query->whereHas('prestamo', function ($query) use ($agentesAsignados) {
                    $query->whereIn('agente_id', $agentesAsignados);
                });
            })
            ->orderBy('id', 'desc')
            ->withSum('abono_detalle as total_abonado2', 'monto_abono')
            ->paginate(30);

        return view('abonos.indexAbonos', compact('abonos','listaCobradores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('abonos.nuevoAbono');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $str = [];
        parse_str($request->values, $str);
        $cuotas = json_decode($str['cuotas'][0], true);
        $prestamoId = null;
        if (gettype($cuotas) != "array")//si no es un array quiere decir q es abono al desembolso
            $prestamoId = $cuotas;
        else {
            $decodedIds = array_map(function ($cuotas) {
                return decode($cuotas);
            }, $cuotas);
        }

        $montoEfectivo = (isset($str['efectivoMonto'])) ? $str['efectivoMonto'] : 0;
        $montoTarjeta = (isset($str['tarjetaMonto'])) ? $str['tarjetaMonto'] : 0;
        $montoCheque = (isset($str['chequeMonto'])) ? $str['chequeMonto'] : 0;
        $montoTransferencia = (isset($str['transferenciaMonto'])) ? $str['transferenciaMonto'] : 0;

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
            $abono->fecha_abono = Carbon::now();
            $abono->tipo_abono = $str['tipo_abono'];
            $abono->created_user_id = userLogeado()->id;

            $abono->total_efectivo = $montoEfectivo;
            $abono->total_tarjeta = $montoTarjeta;
            $abono->total_cheque = $montoCheque;
            $abono->total_transferencia = $montoTransferencia;

            $abono->referencia_tarjeta = '';
            $abono->referencia_cheque = '';
            $abono->referencia_transferencia = '';
            $abono->referencia_transferencia = '';

            $abono->save();

            $totalAbonado2 = 0;
            foreach ($cuotas as $cuota) {
                if ($valorCuota > 0 && $cuota->monto_pendiente_cuota > 0) {
                    $totalAbonoIntereses = $cuota->total_pendiente_interes_cuota;
                    $totalAbonoCapital = $cuota->total_pendiente_capital_cuota;
                    $totalAbonoMora = $cuota->total_pendiente_mora_cuota;

                    $montoAbonarInteres = 0;
                    $montoAbonarCapital = 0;
                    $montoAbonarMora = 0;
                    $totalAbonado = 0;

                    if($str['tipo_abono']==2){//es una dispensa de saldo, solo afectar el valor de los intereses
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
                    } else {
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
                    }

                    $abonoCuota = new prestamoCuotaAbonoModel();
                    $abonoCuota->abono_id = $abono->id;
                    $abonoCuota->prestamo_cuota_id = $cuota->id;
                    $abonoCuota->estado = 1;
                    $abonoCuota->monto_abono = $totalAbonado;
                    $abonoCuota->total_interes = $montoAbonarInteres;
                    $abonoCuota->total_capital = $montoAbonarCapital;
                    $abonoCuota->total_mora = $montoAbonarMora;

                    $abonoCuota->tipo_abono = 1; //no requerido

                    $abonoCuota->fecha_abono = Carbon::now();

                    $abonoCuota->created_user_id = userLogeado()->id;
                    $abonoCuota->save();

                    if ($cuota->monto_pendiente_cuota == 0) {
                        $cuota->estado = 3;
                        $cuota->fecha_pagado = Carbon::now();
                        $cuota->save();
                    }
                    if ($cuota->prestamo->pendiente_abono == 0) {// si el prestamo ya se cancelo por completo
                        $cuota->prestamo->estado = 2;
                        $cuota->prestamo->save();
                    }
                    $totalAbonado2 += $totalAbonado;//para verificar si se aplicó algun abono
                }
            }
            if ($totalAbonado2 === 0) {
                if ($str['tipo_abono'] == 2)
                    return response()->json('<br><b style="color: red">No se logró aplicar la dispensa de saldo, la totalidad de los intereses pendientes ya se encuentran cancelados</b>', 404);
                else
                    return response()->json('<br><b style="color: red">No se encontrarón montos pendientes para aplicar el abono</b>', 404);
            }

            DB::commit();
            return response()->json(route('abonos.printRecibo', encode($abono->id)));
        } catch (\Exception $ex) {
            DB::rollBack();
            logger()->error('Error al crear el abono ' . $ex->getMessage());
            return response()->json('Ha ocurrido un error al intentar crear la cuota ' . $ex->getMessage(), 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $abono = abonosModel::where('id', decode($id))->first();
        return view('abonos.verAbono', compact('abono'));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('Eliminar Abonos');
        try {
            DB::beginTransaction();

            $abono = abonosModel::where('id', decode($id))->first();

            if (!$abono)
                return redirect()->back()->with('error', 'Abono no encontrado');

            // Paso 1: igual que anular — marcar detalles y revertir estado de cuotas
            foreach ($abono->abono_detalle as $detalle) {
                $detalle->estado = 2;
                $detalle->save();

                if ($detalle->prestamo_cuota->estado == 3) {
                    $detalle->prestamo_cuota->estado = 1;
                    $detalle->prestamo_cuota->fecha_pagado = null;
                    $detalle->prestamo_cuota->update();
                }
            }

            // Paso 2: igual que anular — revertir estado del préstamo si estaba cancelado
            if ($abono->prestamo->estado == 2) {
                $abono->prestamo->estado = 1;
                $abono->prestamo->save();
            }

            $abono->abono_detalle()->forceDelete();
            $abono->forceDelete();

            DB::commit();
            return redirect()->back()->with('success', 'Abono eliminado correctamente');
        } catch (\Exception $ex) {
            DB::rollBack();
            logger()->error('No se ha logrado eliminar el abono ' . $ex->getMessage());
            return redirect()->back()->with('error', 'No se ha logrado eliminar el abono');
        }
    }

    public function printRecibo($abonoId)
    {
        $abono = abonosModel::where('id', decode($abonoId))->first();
        return view('abonos.recibo', compact('abono'));
    }

    public function anularAbono(Request $request, $id)
    {
        $this->authorize('Anular Abonos');
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

            if ($abono->prestamo->estado == 2)//si el prestamo se marco como pagado al hacer el/los abonos, al anular regresarlo a pendiente
            {
                $abono->prestamo->estado = 1;
                $abono->prestamo->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Abono anulado correctamente');

        } catch (\Throwable $ex) {
            DB::rollBack();
            logger()->error('No se ha logrado anular el abono ' . $ex->getMessage());
            return redirect()->back()->with('error', 'No se ha logrado anular el abono');
        }
    }
}
