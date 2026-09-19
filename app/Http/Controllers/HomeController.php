<?php

namespace App\Http\Controllers;

use App\Models\negocioTiposModel;
use App\Models\prestamosModel;
use App\Models\User;
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
//        $tiposNegocios = userNegociosModel::get();
//        foreach ($tiposNegocios as $tn)
//        {
////            $tn->tipo_negocio_id = str_replace(['[','"',']',"\\"],'',$tn->tipo_negocio_id);
//            $tn->tipo_negocio_id = json_encode((array)$tn->tipo_negocio_id);
//            $tn->save();
//        }
        $historicoClientes = User::cliente()->count();
        $clientesActivos = User::cliente()->activo()->count();
        $totalClientesAnyo = User::cliente()->whereYear('created_at',date('Y'))->count();
        $totalClientesMes = User::cliente()->whereYear('created_at',date('Y'))->whereMonth('created_at',date('m'))->count();
        $agentes = User::agente()->where('estado',1)->count();
        $totalPrestamos = prestamosModel::where('desembolsado',1)->count();
        $prestamosActivos = prestamosModel::where('estado',1)->where('desembolsado',1)->count();
        $datos = [
            'historicoClientes' => $historicoClientes,
            'clientesActivos' => $clientesActivos,
            'clientesMes' => $totalClientesMes,
            'clientesAnyo' => $totalClientesAnyo,
            'agentes' => $agentes,
            'totalPrestamos' => $totalPrestamos,
            'prestamosActivos' => $prestamosActivos,
        ];
        $prestamosMora = prestamosModel::whereHas('cuotas',function ($query){
            $query->where('estado',1)->where('desembolsado',1)->whereDate('fecha_cuota','<',date('Y-m-d'));
        })->limit(10)->get();

        $recuperacionAgentes = abonosModel::whereDate('created_at', Carbon::today())
            ->where('estado', 1)
            ->select('created_user_id',
                DB::raw('SUM(IFNULL(total_efectivo,0) + IFNULL(total_tarjeta,0) + IFNULL(total_cheque,0) + IFNULL(total_transferencia,0)) as total'),
                DB::raw('COUNT(*) as clientes_atendidos'),
                DB::raw('MAX(created_at) as ultimo_pago')
            )
            ->groupBy('created_user_id')
            ->with('user_create')
            ->get();

        $totalRecuperado = $recuperacionAgentes->sum('total');
        $totalClientesAtendidos = $recuperacionAgentes->sum('clientes_atendidos');

        return view('home', compact('datos', 'prestamosMora', 'recuperacionAgentes', 'totalRecuperado', 'totalClientesAtendidos'));
    }
}
