<?php

namespace App\Http\Controllers;

use App\Models\negocioTiposModel;
use App\Models\prestamosModel;
use App\Models\User;
use App\Models\userAsignadoModel;
use App\Models\userNegociosModel;
use App\Models\usersFiadoresModel;
use App\Models\abonosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $agentesAsignados = \App\Models\userAsignadoModel::where('user_id', \Auth::user()->id)
            ->get()->pluck('admin_asignado_id')->toArray();

        // Si tiene agentes asignados filtra por ellos, si no (admin total) ve todo
        $tieneAsignados = count($agentesAsignados) > 0;

        $historicoClientes = User::cliente()
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereHas('prestamos', function($q2) use ($agentesAsignados) {
                    $q2->whereIn('agente_id', $agentesAsignados);
                });
            })->count();

        $clientesActivos = User::cliente()->activo()
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereHas('prestamos', function($q2) use ($agentesAsignados) {
                    $q2->whereIn('agente_id', $agentesAsignados);
                });
            })->count();

        $totalClientesAnyo = User::cliente()->whereYear('created_at', date('Y'))
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereHas('prestamos', function($q2) use ($agentesAsignados) {
                    $q2->whereIn('agente_id', $agentesAsignados);
                });
            })->count();

        $totalClientesMes = User::cliente()->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereHas('prestamos', function($q2) use ($agentesAsignados) {
                    $q2->whereIn('agente_id', $agentesAsignados);
                });
            })->count();

        $agentes = User::agente()->where('estado', 1)
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereIn('id', $agentesAsignados);
            })->count();

        $totalPrestamos = prestamosModel::where('desembolsado', 1)
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereIn('agente_id', $agentesAsignados);
            })->count();

        $prestamosActivos = prestamosModel::where('estado', 1)->where('desembolsado', 1)
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereIn('agente_id', $agentesAsignados);
            })->count();

        $datos = [
            'historicoClientes' => $historicoClientes,
            'clientesActivos'   => $clientesActivos,
            'clientesMes'       => $totalClientesMes,
            'clientesAnyo'      => $totalClientesAnyo,
            'agentes'           => $agentes,
            'totalPrestamos'    => $totalPrestamos,
            'prestamosActivos'  => $prestamosActivos,
        ];

        $prestamosMora = prestamosModel::whereHas('cuotas', function($query) {
                $query->where('estado', 1)->where('desembolsado', 1)->whereDate('fecha_cuota', '<', date('Y-m-d'));
            })
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereIn('agente_id', $agentesAsignados);
            })
            ->limit(10)->get();

        $recuperacionAgentes = abonosModel::whereDate('created_at', Carbon::today())
            ->where('estado', 1)
            ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                $q->whereHas('prestamo', function($q2) use ($agentesAsignados) {
                    $q2->whereIn('agente_id', $agentesAsignados);
                });
            })
            ->select('created_user_id',
                DB::raw('SUM(IFNULL(total_efectivo,0) + IFNULL(total_tarjeta,0) + IFNULL(total_cheque,0) + IFNULL(total_transferencia,0)) as total'),
                DB::raw('COUNT(*) as clientes_atendidos'),
                DB::raw('MAX(created_at) as ultimo_pago')
            )
            ->groupBy('created_user_id')
            ->with('user_create')
            ->get();

        $totalRecuperado        = $recuperacionAgentes->sum('total');
        $totalClientesAtendidos = $recuperacionAgentes->sum('clientes_atendidos');

        return view('home', compact('datos', 'prestamosMora', 'recuperacionAgentes', 'totalRecuperado', 'totalClientesAtendidos'));
    }
}
