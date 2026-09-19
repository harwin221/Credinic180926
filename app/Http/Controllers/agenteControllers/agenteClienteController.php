<?php

namespace App\Http\Controllers\agenteControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\clienteRequest;
use App\Models\negocioTiposModel;
use App\Models\prestamosModel;
use App\Models\solicitudPrestamoModel;
use App\Models\tipoDocumentosModel;
use App\Models\User;
use App\Models\userDocumentosModel;
use App\Models\userNegocioDocumentosModel;
use App\Models\userNegociosModel;
use App\Models\usersFiadoresModel;
use App\utils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class agenteClienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $estado = $request->estado;
        $usuarios = User::with('prestamos')->whereIn('tipo_usuario', [3,6])
            ->buscar($buscar)
            ->when($estado && $estado!=4,function ($query) use ($estado){
                $query->join('prestamos as P','P.user_id','users.id')
                    ->where('estado_aprobacion',$estado)
                    ->where('agente_id',userLogeado()->id)
                    ->where('desembolsado',0);
            })
            ->orderBy('users.id', 'desc')
            ->paginate(30);

        $solicitudesPendientes = prestamosModel::with('cliente')->where('estado_aprobacion',1)->where('agente_id',userLogeado()->id)->orderBy('id','desc')->get();
        $solicitudesAprobadas = prestamosModel::where('estado_aprobacion',2)->where('created_user_id',userLogeado()->id)->whereDate('created_at',Carbon::now()->toDateString())->orderBy('id','desc')->count();
        $solicitudesRechazadas = prestamosModel::where('estado_aprobacion',3)->where('created_user_id',userLogeado()->id)->whereDate('created_at',Carbon::now()->toDateString())->orderBy('id','desc')->count();
        return view('agentesViews.registroClientes.agenteClientesIndex', compact('usuarios','solicitudesPendientes','solicitudesAprobadas','solicitudesRechazadas'));
    }

    public function create()
    {
        $tiposDocumentos = tipoDocumentosModel::get();
        return view('agentesViews.registroClientes.agenteClientesCreate', compact('tiposDocumentos'));
    }

    public function store(clienteRequest $request)
    {
        try {
            DB::beginTransaction();
            $documentos = $request->file;
            $user = new User($request->all());
            $user->tipo_usuario = 3;
            $user->password = \Hash::make('test2023');
            $user->email = \Str::random('10') . "@gmail.com";
            $user->created_user_id = \Auth::user()->id;
            $user->dep_mun = decode($request->dep_mun);
            $user->estado = 1;
            if ($user->save()) {
                if ($request->file('foto')) {
                    $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory);
                    $user->foto = $imageName;
                    $user->update();
                }

                if ($documentos) {
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

//                $solicitud = new solicitudPrestamoModel($request->all());
//                $solicitud->user_id = $user->id;
//                $solicitud->estado = 1;//pendiente
//                $solicitud->created_user_id = userLogeado()->id;
//                $solicitud->save();
            }
            DB::commit();
            return redirect()->route('agentes.user.clientes.index')->with('success', 'Se ha creado correctamente el usuario');
        } catch (\Exception $ex) {
            logger()->error('No se ha podido guardar el cliente '.$ex->getMessage());
            return redirect()->back()->with('error','No se ha podido guardar los datos del cliente');
        }
    }

    public function edit($id)
    {
        $user = User::find(decode($id));
        $tiposDocumentos = tipoDocumentosModel::get();

        $totalPendientePrestamos = prestamosModel::where('user_id', decode($id))
            ->where('desembolsado', 1)
            ->where('estado', 1)
            ->where('agente_id', userLogeado()->id)
            ->get();

        $totalPendiente = [];
        foreach ($totalPendientePrestamos as $prestamo) {
            $totalPendiente[] = [
                'consecutivo' => $prestamo->consecutivo,
                'pendiente' => $prestamo->total_pendiente_capital + $prestamo->total_pendiente_interes
            ];
        }

        $prestamosCliente = prestamosModel::where('user_id',decode($id))
            ->where('agente_id',userLogeado()->id)
            ->where('desembolsado',0)
            ->orderBy('id','desc')
            ->paginate(1);

        $listaAdmin = User::where('tipo_usuario', 2)->get()->pluck('full_name', 'id_enc')->toArray();


        return view('agentesViews.registroClientes.agenteClientesEditar', compact('user', 'tiposDocumentos','prestamosCliente','totalPendiente','listaAdmin'));
    }

    public function update(clienteRequest $request, $id)
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

                            $dc->move(public_path('assets/img/documentos/'), $dc->getClientOriginalName());
                        }
                    } else {
                        if (!$userDoc = userDocumentosModel::where('user_id', $user->id)->where('documento_id', $ind)->first()) {
                            $userDoc = new userDocumentosModel();
                            $userDoc->user_id = $user->id;
                            $userDoc->documento_id = $ind;
                        }
                        $userDoc->url = $doc->getClientOriginalName();
                        $userDoc->save();

                        $doc->move(public_path('assets/img/documentos/'), $doc->getClientOriginalName());
                    }
                }
            }
        }
        return redirect()->back()->with('success', 'Se ha actualizado correctamente el usuario');
    }

    public function destroy(string $id)
    {
        $user = User::find(decode($id));
        if ($user->estado != 3 || count($user->prestamos))
            return redirect()->back()->with('error', 'El ciente ya no puede ser eliminado');
        $user->delete();
        return redirect()->route('agentes.user.clientes.index')->with('success', 'Se ha eliminado correctamente el usuario');
    }

    public function createNegocio(Request $request)
    {
        $user = User::where('id', decode($request->user))->first();
        return view('agentesViews.registroClientes.negocios.agenteNegociosCrear', compact('user'));
    }

    public function storeNegocio(Request $request)
    {
        $negocio = new userNegociosModel();
        $negocio->nombre = $request->nombre;
        $negocio->user_id = decode($request->clienteId);
        $negocio->municipio_id = decode($request->dep_mun);
        $negocio->direccion = $request->direccion;
        $negocio->punto_geografico = $request->punto_geografico;
        $negocio->telefono_negocio = $request->telefono_negocio;
        $negocio->comentarios = $request->comentarios;
        $negocio->created_user_id = userLogeado()->id;

        if ($negocio->save()) {
            if ($request->documentos) {
                foreach ($request->documentos as $doc) {
                    $user_negocio_documento = new userNegocioDocumentosModel();
                    $user_negocio_documento->user_negocio_id = $negocio->id;
                    $user_negocio_documento->url = $doc->getClientOriginalName();
                    $user_negocio_documento->created_user_id = userLogeado()->id;
                    $user_negocio_documento->save();
                    $doc->move(public_path('assets/img/negocios/'), $doc->getClientOriginalName());
                }
            }
            return redirect()->back()->with('success', 'Se ha guardado correctamente el negocio');
        }
    }

    public function editNegocio($id)
    {
        $tiposNegocios = negocioTiposModel::get()->pluck('nombre', 'id_enc')->toArray();
        $user_negocio = userNegociosModel::where('id', decode($id))->first();
        return view('agentesViews.registroClientes.negocios.agenteNegociosEditar', compact('tiposNegocios', 'user_negocio'));
    }

    public function updateNegocio(Request $request, $id)
    {
        $negocio = userNegociosModel::find(decode($id));
        if ($negocio) {
            $negocio->nombre = $request->nombre;
            $negocio->municipio_id = decode($request->dep_mun);
            $negocio->direccion = $request->direccion;
            $negocio->punto_geografico = $request->punto_geografico;
            $negocio->telefono_negocio = $request->telefono_negocio;
            $negocio->comentarios = $request->comentarios;
            $negocio->updated_user_id = userLogeado()->id;

            if ($negocio->save()) {
                if ($request->documentos) {
                    foreach ($request->documentos as $doc) {
                        $user_negocio_documento = new userNegocioDocumentosModel();
                        $user_negocio_documento->user_negocio_id = decode($id);
                        $user_negocio_documento->url = $doc->getClientOriginalName();
                        $user_negocio_documento->created_user_id = userLogeado()->id;
                        $user_negocio_documento->save();
                        $doc->move(public_path('assets/img/negocios/'), $doc->getClientOriginalName());
                    }
                }
                return redirect()->back()->with('success', 'Se ha actualizado correctamente del negocio');
            }
        } else
            return redirect()->back()->with('warning', 'No se ha logrado obtener los datos del negocio');
    }

    public function createFiador(Request $request)
    {
        $cliente = User::find(decode($request->user));
        return view('agentesViews.registroClientes.fiadores.agenteFiadorCrear',compact('cliente'));
    }

    public function storeFiador(Request $request)
    {
          try {
            DB::beginTransaction();
            $user = new User($request->all());
            $user->tipo_usuario = 5;
            $user->password = \Hash::make('test2023');
            $user->email = \Str::random('10') . "@gmail.com";
            $user->dep_mun = decode($request->dep_mun);
            $user->created_user_id = \Auth::user()->id;
            $user->estado = 1;
            if ($user->save()) {
                if($request->file('foto')){
                    $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory);
                    $user->foto = $imageName;
                    $user->update();
                }
                $fiador_usuario = new usersFiadoresModel();
                $fiador_usuario->user_id = decode($request->clienteId);
                $fiador_usuario->user_fiador_id = $user->id;
                $fiador_usuario->observaciones = $request->observaciones;
                $fiador_usuario->created_user_id = \Auth::user()->id;
                $fiador_usuario->save();
            }
            DB::commit();
            return redirect()->back()->with('success', 'Se ha creado correctamente el fiador');
        }catch (\Throwable $ex){
            logger()->error($ex->getMessage());
            DB::rollBack();
            return redirect()->back()->with('error','Ha ocurrido un error al intentar crear al fiador');
        }
    }

    public function editFiador($id)
    {
        $user=User::findOrFail(decode($id));
        return view('agentesViews.registroClientes.fiadores.agenteFiadorEdit',compact('user'));
    }

    public function updateFiador(Request $request, $id)
    {

        $user = User::findorfail(decode($id));
        $fotoActual = $user->foto;
        $user->fill($request->all());
        $user->dep_mun=decode($request->dep_mun);
        $user->updated_user_id=\Auth::user()->id;
        if ($request->file('foto')) {
            $imageName = utils::saveOrUpdatePhoto($request, $user->user_image_directory,$fotoActual);
            $user->foto = $imageName;
        }

        $user->update();
        return redirect()->back()->with('success','Se ha actualizado correctamente el fiador');
    }
}
