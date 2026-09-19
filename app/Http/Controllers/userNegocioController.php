<?php

namespace App\Http\Controllers;

use App\Models\negocioTiposModel;
use App\Models\tiposDocumentosModel;
use App\Models\User;
use App\Models\userNegocioDocumentosModel;
use App\Models\userNegociosModel;
use Illuminate\Http\Request;

class userNegocioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = User::where('id',decode($request->user))->first();
        return view('agentesViews.registroClientes.negocios.agenteNegociosCrear',compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
        return redirect()->back()->with('success','Se ha guardado correctamente el negocio');
        }
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
        $tiposNegocios = negocioTiposModel::get()->pluck('nombre','id_enc')->toArray();
        $user_negocio = userNegociosModel::where('id',decode($id))->first();
        return view('negocios.negociosEditar',compact('user_negocio','tiposNegocios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $negocio = userNegociosModel::find(decode($id));
        if($negocio)
        {
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
                return redirect()->back()->with('success','Se ha actualizado correctamente del negocio');
            }
        }
        else
            return redirect()->back()->with('warning','No se ha logrado obtener los datos del negocio');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $negocio = userNegociosModel::where('id',decode($id))->first();
        if($negocio && count($negocio->prestamo))
            return redirect()->back()->with('error','El negocio no se puede eliminar debido a que se encuenta enlazado a un préstamo');
        $negocio->delete();
        return redirect()->back()->with('success','El negocio se ha eliminado correctamente');
    }

    public function getInformacionNegocio($negocioID)
    {
        $userNegocio = userNegociosModel::where('id', decode($negocioID))->first();
        $userNegocio->dep = $userNegocio->departamento_municipio->departamento->nombre;
        $userNegocio->mun = $userNegocio->departamento_municipio->nombre;
        return response()->json($userNegocio);
    }

    public function getListNegociosCliente($userId)
    {
        $clienteNegocios = userNegociosModel::where('user_id', decode($userId))->get()->pluck('nombre', 'id_enc')->toArray();
        return response()->json(['' => '*SELECCIONE NEGOCIO*'] + $clienteNegocios);
    }
}
