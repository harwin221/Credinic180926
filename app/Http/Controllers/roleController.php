<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class roleController extends Controller
{

    function getRol($id)
    {
        return response()->json(Role::where('id', decode($id))->first());
    }

    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $roles=Role::paginate(15);
        $admins = ['' => '* SELECCIONE UN USUARIO *'] + User::where('tipo_usuario', 2)->get()->pluck('full_name', 'id_enc')->toArray();

        return view('permissions.rolesIndex',compact('roles','admins'));
    }

    function detallePermisos($id)
    {
        $rolObj=Role::find(decode($id));
        $permisos=Permission::get()->groupBy('description');

        return view('permissions.permisosRol',compact('rolObj','permisos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Role::create(['name' => $request->get('name')]);
        return redirect()->back()->with('success', 'Rol creado correctamente');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Role::where('id',decode($id))->update(['name' => $request->get('name')]);
        return redirect()->back()->with('success', 'Rol actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Role::where('id',decode($id))->delete();
        return redirect()->back()->with('success', 'Rol eliminado correctamente');
    }

    public function asignarRol(Request $request,$rolID)
    {
        $userId = $request->user;
        $rol = Role::find(decode($rolID));
        if ($userId) {
            $usuarios = User::where('id', decode($userId))->first();
            if (!$usuarios->hasRole($rol->name))
                $usuarios->assignRole($rol->name);
        }
        return redirect()->back()->with('Rol asignado correctamente');
    }

    public function desAsignarRol($rolID,$userId)
    {
        $rol = Role::find(decode($rolID));
        if ($userId) {
            $usuarios = User::where('id', decode($userId))->first();
            $usuarios->removeRole($rol->name);
        }
        return redirect()->back()->with('Rol asignado correctamente');
    }
}
