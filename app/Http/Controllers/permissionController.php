<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class permissionController extends Controller
{

    function getPermiso($id){
        return response()->json(Permission::where('id', decode($id))->first());
    }

    function asignarPermisosRol(Request $request, $id)
    {
        $rol=Role::find(decode($id));
        $permisos = $request->get('permiso');
        $arrPermisos = [];
        if($permisos)
        {
            $arrPermisos = array_map(function ($el){
                return decode($el);
            },$permisos);
        }

        $rol->syncPermissions($arrPermisos);
        return redirect()->back()->with('success', 'Permisos asignados al rol');

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permisos=Permission::orderBy('id','desc')->paginate(20);
        return view('permissions.permisosIndex',compact('permisos'));
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
        Permission::create(['name' => $request->get('name'),'description'=>$request->get('description')]);
        return redirect()->back()->with('success', 'Permiso creado correctamente');

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
        Permission::where('id',implode(\Hashids::decode($id)))->update(['name' => $request->get('name'),'description'=>$request->get('description')]);
        return redirect()->back()->with('success', 'Permiso actualizado correctamente');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Permission::where('id',implode(\Hashids::decode($id)))->delete();
        return redirect()->back()->with('success', 'Permiso eliminado correctamente');
    }
}
