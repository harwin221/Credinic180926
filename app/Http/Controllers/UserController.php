<?php

namespace App\Http\Controllers;

use App\Http\Requests\adminRequest;
use App\Http\Requests\agenteRequest;
use App\Http\Requests\clienteRequest;
use App\Imports\clientesImport;
use App\Models\abonosModel;
use App\Models\consecutivoModel;
use App\Models\negocioTiposModel;
use App\Models\prestamoCuotaAbonoModel;
use App\Models\prestamoCuotasModel;
use App\Models\prestamosModel;
use App\Models\tipoDocumentosModel;
use App\Models\User;
use App\Models\userAsignadoModel;
use App\Models\userDocumentosModel;
use App\Models\userNegociosModel;
use App\utils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function importarClientes(Request $request)
    {
        try {
            DB::beginTransaction();

            $fechaDesembolsoIndex = 18;
            $nombresIndex = 1;
            $apellidosIndex = 2;
            $cedulaIndex = 3;
            $direccionIndex = 4;
            $telefonoIndex = 5;
            $negocioIndex = 6;
            $montoPrestamoIndex = 7;
            $vendedorIndex = 9;
            $agenteIndex = 10;
            $formaPagoIndex = 11;
            $plazoIndex = 12;
            $cuotaIndex = 13;
            $tasaIndex = 14;
            $montoAbonarIndex = 17;
            $vencimientoIndex = 19;
            $observacionIndex = 20;

            $datos = Excel::toArray(new clientesImport, $request->file('clientes'));
            foreach ($datos[0] as $ind => $usuarios) {
                if ($ind != 0) {
                    $user = User::where('cedula', $usuarios[$cedulaIndex])->first();
                    if (!$user) {
                        $user = new User();
                        $user->tipo_usuario = 3;
                        $user->nombres = $usuarios[$nombresIndex];
                        $user->apellidos = $usuarios[$apellidosIndex];
                        $user->cedula = $usuarios[$cedulaIndex];
                        $user->telefono1 = $usuarios[$telefonoIndex];
                        $user->email = Str::random(8) . "@gmail.com";
                        $user->direccion = $usuarios[$direccionIndex];
                        $user->created_user_id = \Auth::user()->id;
                        $user->password = \Hash::make('cliente');
                        $user->save();
                    }

                    $formaPago = 1;
                    if ($usuarios[$formaPagoIndex] == 'DIARIO')
                        $formaPago = 1;
                    if ($usuarios[$formaPagoIndex] == 'QUINCENAL')
                        $formaPago = 3;
                    if ($usuarios[$formaPagoIndex] == 'SEMANAL')
                        $formaPago = 2;

                    $plazo = 0;
                    if ($usuarios[$plazoIndex] != '') {
                        $plazo = explode(' ', $usuarios[$plazoIndex])[0];
                    }

                    $tasa = 0;
                    if ($usuarios[$tasaIndex] != '' && $usuarios[$tasaIndex] != ' ')
                        $tasa = $usuarios[$tasaIndex];

                    $negocioId = 1;

                    if ($usuarios[$negocioIndex] != '') {
                        $negocio = negocioTiposModel::where('nombre', $usuarios[$negocioIndex])->first();
                        if (!$negocio) {
                            $negocio = new negocioTiposModel();
                            $negocio->nombre = $usuarios[$negocioIndex];
                            $negocio->created_user_id = 1;
                            $negocio->save();
                        }
                        $negocioId = $negocio->id;

                    }

                    $userNegocio = userNegociosModel::where('user_id', $user->id)->first();

                    if (!$userNegocio) {
                        $tipoNegocio = negocioTiposModel::where('id', $negocioId)->first();
                        $userNegocio = new userNegociosModel();
                        $userNegocio->user_id = $user->id;
                        $userNegocio->nombre = $tipoNegocio->nombre;
                        $userNegocio->direccion = $usuarios[$direccionIndex];
                        $userNegocio->municipio_id = 155;
                        $userNegocio->punto_geografico = "-";
                        $userNegocio->telefono_negocio = "-";
                        $userNegocio->created_user_id = 1;
                        $userNegocio->save();
                    }

                    $agenteId = User::where('nombres', 'like', '%' . $usuarios[$agenteIndex] . '%')->where('tipo_usuario', 4)->first();
                    $vendedorId = User::where('nombres', 'like', '%' . $usuarios[$vendedorIndex] . '%')->where('tipo_usuario', 2)->first();

                    $prestamo = new prestamosModel();
                    $prestamo->user_id = $user->id;
                    $prestamo->agente_id = ($agenteId) ? $agenteId->id : 4;
                    $prestamo->negocio_id = $userNegocio->id;
                    $prestamo->fiador_id = 3;
                    $prestamo->user_desembolso = 3;
                    $prestamo->vendedor_id = ($vendedorId) ? $vendedorId->id : 4;
                    $prestamo->consecutivo = consecutivoModel::obtenerConsecutivo();
                    $prestamo->fecha_prestamo = Carbon::now();
                    $prestamo->moneda_prestamo = 1;
                    $prestamo->fecha_desembolso = isset($usuarios[$fechaDesembolsoIndex]) ? $usuarios[$fechaDesembolsoIndex] : Carbon::now();
                    $prestamo->monto_prestamo = $usuarios[$montoPrestamoIndex];
                    $prestamo->monto_financiado = 0;
                    $prestamo->forma_pago_tipo = $formaPago;
                    $prestamo->plazo_pago = $plazo;
                    $prestamo->dia_pago = 1;
                    $prestamo->fecha_primer_pago = Carbon::now();
                    $prestamo->monto_cuota = ($usuarios[$cuotaIndex] != '') ? $usuarios[$cuotaIndex] : 0;
                    $prestamo->tasa_prestamo = $tasa;
                    $prestamo->interes_pagar = 0;
                    $prestamo->interes_mes = 0;
                    $prestamo->interes_total_pagar = 0;
                    $prestamo->dias_aplicar_mora = 0;
                    $prestamo->tipo_mora = 1;
                    $prestamo->monto_mora = 0;

                    $prestamo->observaciones = $usuarios[$observacionIndex];
                    $prestamo->estado = 1;
//                    if ($usuarios[$observacionIndex] === 0)
//                        $prestamo->estado = 2;
                    $prestamo->created_user_id = 1;


                    $banderaAumentar = 0;
                    switch ($formaPago) {
                        case 1:
                            $formapagovalor = 20;//diario
                            $banderaAumentar = 1;
                            break;
                        case 2:
                            $formapagovalor = 4;//semanal
                            $banderaAumentar = 7;
                            break;
                        case 3:
                            $formapagovalor = 2;//quincenal
                            $banderaAumentar = 14;
                            break;
                    }

                    if ($plazo == 0)
                        $plazo = 1;
                    $numeroCuotas = $plazo * $formapagovalor;


                    $interesesMes = $usuarios[$montoPrestamoIndex] * ($tasa / 100);
                    $interesesPagar = $interesesMes * $plazo;//plazo en meses
                    $interesCuota = $interesesPagar / ($plazo * $formapagovalor);//plazo en semanas

                    $subTotalFinanciamiento = $usuarios[$montoPrestamoIndex] + $interesesPagar;
                    $montoCuotaMes = $subTotalFinanciamiento / ($plazo * $formapagovalor);

                    $prestamo->interes_pagar = $interesCuota;
                    $prestamo->interes_mes = $interesesMes;
                    $prestamo->interes_total_pagar = $interesesPagar;
                    $prestamo->monto_financiado = $subTotalFinanciamiento;


                    $prestamo->save();
                    if (isset($usuarios[$fechaDesembolsoIndex]))
                        $fechaCuota = Carbon::createFromFormat('Y-m-d', $usuarios[$fechaDesembolsoIndex]);
                    else
                        $fechaCuota = Carbon::now();

//                    if ($usuarios[$observacionIndex] != 0) {
                        for ($i = 1; $i <= $numeroCuotas; $i++) {
                            $prestamoCuota = new prestamoCuotasModel();
                            $prestamoCuota->prestamo_id = $prestamo->id;
                            $prestamoCuota->numero_cuota = $i;
                            $prestamoCuota->forma_pago = 1;
                            $prestamoCuota->monto_cuota = ($usuarios[$cuotaIndex] != '') ? $usuarios[$cuotaIndex] : 0;
                            $prestamoCuota->monto_interes = $interesCuota;

                            $prestamoCuota->fecha_cuota = date('Y-m-d');
                            $prestamoCuota->fecha_pagado = NULL;

                            if ($i == 1) {
                                $prestamoCuota->fecha_cuota = Carbon::now()->toDateString();
                            } else
                                $fechaCuota->addDays($banderaAumentar);

                            $prestamoCuota->fecha_cuota = $fechaCuota->toDateString();

                            $prestamoCuota->estado = 1;

                            $prestamoCuota->created_user_id = userLogeado()->id;
                            $prestamoCuota->save();
                        }
//                    }


                    if ($usuarios[$montoAbonarIndex] !== "" && $usuarios[$montoAbonarIndex] != 0) {
                        $valorCuota = $usuarios[$montoAbonarIndex];

                        if ($valorCuota > 0) {
                            $abono = new abonosModel();

                            $cuotas = prestamoCuotasModel::where('prestamo_id', $prestamo->id)->orderBy('numero_cuota', 'asc')->get();
                            $abono->prestamo_id = $prestamo->id;

                            $abono->estado = 1;
                            $abono->anulado_user_id = NULL;
                            $abono->fecha_abono = Carbon::now();
                            $abono->created_user_id = 1;

                            $abono->total_efectivo = $valorCuota;
                            $abono->total_tarjeta = 0;
                            $abono->total_cheque = 0;
                            $abono->total_transferencia = 0;

                            $abono->referencia_tarjeta = '';
                            $abono->referencia_cheque = '';
                            $abono->referencia_transferencia = '';

                            $abono->save();

                            foreach ($cuotas as $cuota) {
                                if ($valorCuota > 0 && $cuota->monto_pendiente_cuota > 0) {
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

                                    $abonoCuota->tipo_abono = 1; //no requerido

                                    $abonoCuota->fecha_abono = Carbon::now();

                                    $abonoCuota->created_user_id = 1;
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
                                }
                            }
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage() . " / " . $ex->getLine());
        }

        return redirect()->back();
    }

    public function getAdministrativos()
    {
        return response()->json(['' => '* SELECCIONE UN USUARIO *'] + User::where('tipo_usuario', 2)->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray());
    }

    public function getClientesArray()
    {
        return response()->json(['' => '* SELECCIONE UN CLIENTE *'] + User::where('tipo_usuario', 3)->where('estado', 1)->get()->pluck('full_name', 'id_enc')->toArray());
    }

    public function getAgentes()
    {
        return response()->json(['' => '* SELECCIONE UN AGENTE *'] + User::agente()->activo()->orderBy('nombres')->orderBy('apellidos')->get()->pluck('full_name', 'id_enc')->toArray());
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

    public function indexUsuarios()
    {
        return view('usuarios.indexUsuarios');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()//admins
    {
        $usuarios=User::where('tipo_usuario',2)
            ->paginate(15);
        return view('usuarios.admins.adminsIndex',compact('usuarios'));
    }

    public function indexClientes(Request $request)
    {
        $buscar = $request->buscar;
        $usuarios=User::with('prestamos')->whereIn('tipo_usuario',[3,6])
            ->buscar($buscar)
            ->orderBy('id','desc')
            ->paginate(30);
        return view('usuarios.clientes.clientesIndex',compact('usuarios'));
    }

    public function indexAgentes(Request $request)
    {
        $buscar = $request->buscar;
        $usuarios=User::where('tipo_usuario',4)
            ->buscar($buscar)
            ->paginate(15);
        return view('usuarios.agentes.agentesIndex',compact('usuarios'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sucursales = \App\Models\sucursalModel::activa()->orderBy('nombre')->get()->pluck('nombre', 'id')->toArray();
        return view('usuarios.admins.adminsNuevo', compact('sucursales'));
    }

    public function createClientes()
    {
        $tiposDocumentos = tipoDocumentosModel::get();
        return view('usuarios.clientes.clientesNuevo',compact('tiposDocumentos'));
    }

    public function createAgente()
    {
        return view('usuarios.agentes.agentesNuevo');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(adminRequest $request)
    {
        $user = new User($request->all());
        $user->username = mb_strtolower(explode(' ', $request->nombres)[0]);
        $user->tipo_usuario = 2;
        $user->password = \Hash::make('test2023');
        $user->email = \Str::random('10') . "@gmail.com";
        $user->created_user_id = \Auth::user()->id;
        if(\Auth::user()->can('Modificar Horario de Acceso Admins'))
        {
            $user->hora_inicio = $request->get('hora_inicio');
            $user->hora_fin = $request->get('hora_fin');
        }
      
        $user->estado = 1;
        $user->sucursal_id = $request->sucursal_id ?: null;

        if ($user->save() && $request->file('foto')) {
            $imageName = utils::saveOrUpdatePhoto($request,$user->user_image_directory);
            $user->foto = $imageName;
            $user->update();
        }

        return redirect()->route('user.index')->with('success', 'Se ha creado correctamente el usuario');
    }

    public function storeCliente(clienteRequest $request)
    {
        $documentos = $request->file;
        $user = new User($request->all());
        $user->tipo_usuario = 3;
        $user->password = \Hash::make('test2023');
        $user->email = \Str::random('10') . "@gmail.com";
        $user->created_user_id=\Auth::user()->id;
        $user->dep_mun=decode($request->dep_mun);
        $user->estado = 1;
        if ($user->save()) {
            if ($request->file('foto')) {
                $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory);
                $user->foto = $imageName;
                $user->update();
            }

            if($documentos){
                foreach ($documentos as $ind => $doc) {

                    if (count($doc)) {
                        foreach ($doc as $ind2 => $dc) {
                            $userDoc = new userDocumentosModel();
                            $userDoc->user_id = $user->id;
                            $userDoc->documento_id = $ind;
                            $userDoc->url = $dc->getClientOriginalName();
                            $userDoc->save();

                            $dc->move(public_path('assets/img/documentos/'), $dc->getClientOriginalName());
                        }
                    } else {
                        $userDoc = new userDocumentosModel();
                        $userDoc->user_id = $user->id;
                        $userDoc->documento_id = $ind;
                        $userDoc->url = $doc->getClientOriginalName();
                        $userDoc->save();

                        $doc->move(public_path('assets/img/documentos/'), $doc->getClientOriginalName());
                    }
                }
            }
        }




        return redirect()->route('user.clientes.index')->with('success', 'Se ha creado correctamente el usuario');
    }

    public function storeAgente(agenteRequest $request)
    {
        $user = new User($request->all());
        $user->username = "ag_".mb_strtolower(explode(' ',$request->nombres)[0]);
        $user->tipo_usuario = 4;
        $user->password = \Hash::make('test2023');
        $user->email = \Str::random('10') . "@gmail.com";
        $user->created_user_id=\Auth::user()->id;
        $user->estado=1;
        if($user->save() && $request->file('foto')){
            $imageName = utils::saveOrUpdatePhoto($request,$user->user_image_directory);
            $user->foto = $imageName;
            $user->update();
        }
        return redirect()->route('user.agentes.index')->with('success', 'Se ha creado correctamente el usuario');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::find(decode($id));
        $sucursales = \App\Models\sucursalModel::activa()->orderBy('nombre')->get()->pluck('nombre', 'id')->toArray();
        return view('usuarios.admins.adminsEditar', compact('user', 'sucursales'));
    }

    public function editCliente(string $id)
    {
//        $listaCobradores = User::agente()->get()->pluck('full_name', 'id_enc')->toArray();
        $listaAdmin = User::where('tipo_usuario', 2)->get()->pluck('full_name', 'id_enc')->toArray();
        $user=User::find(decode($id));
        $tiposDocumentos = tipoDocumentosModel::get();

        $totalPendientePrestamos = prestamosModel::where('user_id', decode($id))
            ->where('desembolsado', 1)
            ->where('estado', 1)
            ->get();
        $totalPendiente = [];
        foreach ($totalPendientePrestamos as $prestamo) {
            $totalPendiente[] = [
                'consecutivo' => $prestamo->consecutivo,
                'pendiente' => $prestamo->total_pendiente_capital + $prestamo->total_pendiente_interes
            ];
        }

        $prestamosCliente = prestamosModel::with('negocio','negocio.documentos_negocio')->where('user_id',decode($id))
            ->where('desembolsado',0)
            ->orderBy('id','desc')
             ->orderBy('estado_aprobacion','asc')
            ->paginate(1);

        return view('usuarios.clientes.clientesEditar',compact('user','tiposDocumentos','prestamosCliente','listaAdmin','totalPendiente'));
    }

    public function editAgente(string $id)
    {
        $user=User::find(decode($id));
        return view('usuarios.agentes.agentesEditar',compact('user'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(adminRequest $request, string $id)
    {
        $user = User::findorfail(decode($id));
        $fotoActual = $user->foto;
        $user->fill($request->all());
        $user->sucursal_id = $request->sucursal_id ?: null;
        $user->updated_user_id=\Auth::user()->id;
        
        if(\Auth::user()->can('Modificar Horario de Acceso Admins'))
        {
            $user->hora_inicio = $request->get('hora_inicio');
            $user->hora_fin = $request->get('hora_fin');
        }

        if ($request->file('foto')) {
            $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory,$fotoActual);
            $user->foto = $imageName;
        }

        $user->update();

        return redirect()->route('user.index')->with('success', 'Se ha actualizado correctamente el usuario');
    }

    public function updateCliente(clienteRequest $request, string $id)
    {
        $documentos = $request->file;

        $user = User::findorfail(decode($id));
        $fotoActual = $user->foto;
        $user->fill($request->all());
        $user->dep_mun = decode($request->dep_mun);
        $user->updated_user_id = \Auth::user()->id;

        if ($request->file('foto')) {
            $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory, $fotoActual);
            $user->foto = $imageName;
        }

        if ($user->update()) {
            if ($documentos) {
                foreach ($documentos as $ind => $doc) {
                    if (is_array($doc) && count($doc)) {
                        foreach ($doc as $dc) {
                            if (!$userDoc = userDocumentosModel::where('user_id', $user->id)->where('documento_id', $ind)->where('url', $dc->getClientOriginalName())->first()) {
                                $userDoc = new userDocumentosModel();
                                $userDoc->user_id = $user->id;
                                $userDoc->documento_id = $ind;
                            }
                            $userDoc->url = $dc->getClientOriginalName();
                            $userDoc->save();
                            $imagen = Image::make($dc);
                            $imagen->save(public_path('assets/img/documentos/'). $dc->getClientOriginalName(),40);
                        }
                    }
                    else {
                        if (!$userDoc = userDocumentosModel::where('user_id', $user->id)->where('documento_id', $ind)->first()) {
                            $userDoc = new userDocumentosModel();
                            $userDoc->user_id = $user->id;
                            $userDoc->documento_id = $ind;
                        }
                        $userDoc->url = $doc->getClientOriginalName();
                        $userDoc->save();
                        $imagen = Image::make($dc);
                        $imagen->save(public_path('assets/img/documentos/') . $dc->getClientOriginalName(), 40);

//                        $doc->move(public_path('assets/img/documentos/'), $doc->getClientOriginalName());
                    }
                }
            }
        }
        return redirect()->back()->with('success', 'Se ha actualizado correctamente el usuario');
    }

    public function updateAgente(agenteRequest $request, string $id)
    {
        $user = User::findorfail(decode($id));
        $fotoActual = $user->foto;
        $user->fill($request->all());
        $user->updated_user_id = \Auth::user()->id;

        if ($request->file('foto')) {
            $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory, $fotoActual);
            $user->foto = $imageName;
        }

        $user->update();
        return redirect()->route('user.agentes.index')->with('success', 'Se ha actualizado correctamente el usuario');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail(decode($id));
        if (prestamosModel::where('vendedor_id', decode($id))->exists())
            return redirect()->back()->with('warning', 'No se puede eliminar el usuario debido a que se encuentra enlazado a préstamos');
        $user->delete();
        return redirect()->route('user.index')->with('success', 'Se ha eliminado correctamente el usuario');
    }

    public function destroyCliente(string $id)
    {
        $user = User::findOrFail(decode($id));
        if (prestamosModel::where('user_id', decode($id))->exists())
            return redirect()->back()->with('warning', 'No se puede eliminar el cliente debido a que se encuentra enlazado a préstamos');
        $user->delete();
        return redirect()->route('user.clientes.index')->with('success', 'Se ha eliminado correctamente el usuario');
    }

    public function destroyAgente(string $id)
    {
        $user = User::findOrFail(decode($id));
        if(prestamosModel::where('agente_id',decode($id))->exists())
            return redirect()->back()->with('warning','No se puede eliminar el agente debido a que se encuentra enlazado a préstamos');
        $user->delete();
        return redirect()->route('user.agentes.index')->with('success', 'Se ha eliminado correctamente el usuario');
    }

    public function cambiarContrasenyaUser(Request $request, $userId)
    {
        $user = User::find(decode($userId));
        $contrasenya = $request->contrasenya;
        $confirm_contrasenya = $request->confirm_contrasenya;

        if (str_contains($contrasenya, ' ') || str_contains($confirm_contrasenya, ' '))
            return redirect()->back()->with('warning', 'La contraseña no puede contener espacios en blanco');

        if (strlen($contrasenya) < 8)
            return redirect()->back()->with('warning', 'La contraseña debe tener al menos 8 caracteres');

        if ($contrasenya == $confirm_contrasenya) {
            if ($user) {
                $user->password = Hash::make($contrasenya);
                $user->save();
            }
            return redirect()->back()->with('success', 'La contraseña se ha actualizado correctamente');
        } else
            return redirect()->back()->with('warning', 'Las contraseñas ingresadas no coinciden');
    }

    public function eliminarDocumento($id)
    {
        $documento = userDocumentosModel::find(decode($id));
        $documento->delete();
        return redirect()->back();
    }

    public function eliminarFoto($id)
    {
        $user = User::find(decode($id));
        $user->foto = "no-photo.jpg";
        $user->save();
        return redirect()->back();
    }

    public function clientesAsignados(Request $request,$id)
    {
        $prestamos = prestamosModel::where('agente_id',decode($id))
            ->where('estado',1)
            ->orderBy('user_id','desc')->paginate(50);
        $agente = User::where('id',decode($id))->first();
        return view('usuarios.agentes.clientesAsignados',compact('prestamos','agente'));
    }

    public function asignarUsuarios($user)
    {
        $usuariosAsignados = userAsignadoModel::where('user_id', decode($user))->paginate(20);
        $user = User::find(decode($user));
        $admins = User::where('tipo_usuario',4)->get()->pluck('full_name','id_enc')->toArray();
        return view('usuarios.admins.asignarUsuarios', compact('usuariosAsignados','user','admins'));
    }

    public function asignar(Request $request, $user)
    {
        if (!userAsignadoModel::where('user_id', decode($user))->where('admin_asignado_id', decode($request->get('admins')))->exists()) {
            $asignar = new userAsignadoModel();
            $asignar->user_id = decode($user);
            $asignar->admin_asignado_id = decode($request->get('admins'));
            $asignar->save();
            return redirect()->back()->with('success', 'Se ha asignado correctamente');
        } else {
            return redirect()->back()->with('warning', 'Ya se encuentra asignado');
        }
    }
    public function quitarAsignacion($id)
    {
        userAsignadoModel::where('id', $id)->delete();
        return redirect()->back();
    }
}
