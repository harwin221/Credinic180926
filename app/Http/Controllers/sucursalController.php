<?php

namespace App\Http\Controllers;

use App\Models\sucursalModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class sucursalController extends Controller
{
    public function index()
    {
        $this->authorize('Ver Sucursales');
        $sucursales = sucursalModel::orderBy('nombre')->paginate(20);
        return view('sucursales.sucursalesIndex', compact('sucursales'));
    }

    public function create()
    {
        $this->authorize('Crear Sucursales');
        return view('sucursales.sucursalesForm');
    }

    public function store(Request $request)
    {
        $this->authorize('Crear Sucursales');
        $request->validate([
            'nombre'    => 'required|string|max:150',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:50',
        ]);

        sucursalModel::create([
            'nombre'          => $request->nombre,
            'direccion'       => $request->direccion,
            'telefono'        => $request->telefono,
            'estado'          => 1,
            'created_user_id' => Auth::id(),
        ]);

        return redirect()->route('sucursales.index')
                         ->with('success', 'Sucursal creada correctamente');
    }

    public function edit(string $id)
    {
        $this->authorize('Editar Sucursales');
        $sucursal = sucursalModel::where('id', decode($id))->firstOrFail();
        return view('sucursales.sucursalesForm', compact('sucursal'));
    }

    public function update(Request $request, string $id)
    {
        $this->authorize('Editar Sucursales');
        $request->validate([
            'nombre'    => 'required|string|max:150',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:50',
        ]);

        $sucursal = sucursalModel::where('id', decode($id))->firstOrFail();
        $sucursal->nombre    = $request->nombre;
        $sucursal->direccion = $request->direccion;
        $sucursal->telefono  = $request->telefono;
        $sucursal->estado    = $request->estado ?? 1;
        $sucursal->save();

        return redirect()->route('sucursales.index')
                         ->with('success', 'Sucursal actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $this->authorize('Eliminar Sucursales');
        $sucursal = sucursalModel::where('id', decode($id))->firstOrFail();

        // Verificar que no tenga admins activos asignados
        if ($sucursal->admins()->count() > 0) {
            return redirect()->back()
                             ->with('error', 'No se puede eliminar la sucursal, tiene administrativos asignados');
        }

        $sucursal->delete();
        return redirect()->route('sucursales.index')
                         ->with('success', 'Sucursal eliminada correctamente');
    }

    // AJAX: lista de sucursales activas para selects
    public function getListSucursales()
    {
        $lista = sucursalModel::activa()
                              ->orderBy('nombre')
                              ->get()
                              ->pluck('nombre', 'id_enc')
                              ->toArray();
        return response()->json(['' => '-- Seleccione Sucursal --'] + $lista);
    }
}
