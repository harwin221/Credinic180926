<?php

namespace App\Http\Controllers;

use App\Http\Requests\fiadorRequest;
use App\Models\prestamosModel;
use App\Models\User;
use App\Models\usersFiadoresModel;
use App\utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class fiadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $usuarios = User::where('tipo_usuario', 5)
            ->buscar($buscar)
            ->paginate(15);
        return view('usuarios.agentes.agentesIndex', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $cliente = User::find(decode($request->user));
        return view('usuarios.fiadores.fiadorCrear',compact('cliente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(fiadorRequest $request)
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

    public function storeFiador(Request $request,$clienteId)//axios
    {
        if(User::where('cedula',trim($request->cedula))->exists())
            return response()->json([
                'message'=>'La cedula ingresada ya se encuentra registrada'
            ],404);

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
                $fiador_cliente = new usersFiadoresModel();
                $fiador_cliente->user_id = decode($clienteId);
                $fiador_cliente->user_fiador_id = $user->id;
                $fiador_cliente->observaciones = $request->observaciones;
                $fiador_cliente->created_user_id = \Auth::user()->id;

                $fiador_cliente->save();
            }
            DB::commit();
            return response()->json(encode($user->id));
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json("Error " . $ex->getMessage(),404);
        }
    }

    public function getListFiadoresUser($userId)
    {
        $fiadores = usersFiadoresModel::where('user_id', decode($userId))->get()->pluck('full_name', 'id_enc_fiador')->toArray();
        return response()->json([''=>'**Seleccione un Fiador**']+$fiadores);
    }

    public function getDatos($userId)
    {
        $userInfo = User::find(decode($userId));
        $userInfo->datos_fiador = $userInfo->fiador_cliente;
        $userInfo->dep_mun_fiador = $userInfo->fiador_cliente->fiador->departamento_municipio->departamento->nombre." / ".$userInfo->fiador_cliente->fiador->departamento_municipio->nombre;
        return response()->json($userInfo);
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
        $user=User::findOrFail(decode($id));
        return view('usuarios.fiadores.fiadorEdit',compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(fiadorRequest $request, string $id)
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $userFiador = usersFiadoresModel::find(decode($id));
        $user = User::findOrFail($userFiador->user_fiador_id);
        if($user && prestamosModel::where('fiador_id',$userFiador->user_fiador_id)->exists())
            return redirect()->back()->with('error','No se ha podido eliminar el fiador, se encuentra relacionado con prestamos');

        $user->delete();
        usersFiadoresModel::where('user_fiador_id',$userFiador->user_fiador_id)->where('user_id',$userFiador->user_id)->delete();

        return redirect()->back()->with('success', 'Se ha eliminado correctamente el usuario');
    }
}
