<?php

namespace App\Http\Controllers;

use App\Models\abonosModel;
use App\Models\prestamoCuotasModel;
use App\Models\prestamoCuotaAbonoModel;
use App\Models\prestamosModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MobileApiController extends Controller
{
    // ─── POST /api/mobile/login ───────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->whereIn('tipo_usuario', [2, 4]) // agente o admin
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        if ($user->estado != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario inactivo',
            ], 403);
        }

        // Token simple con Laravel Sanctum (si está instalado) o token básico
        $token = null;
        if (method_exists($user, 'createToken')) {
            $user->tokens()->where('name', 'mobile')->delete(); // revocar tokens previos
            $token = $user->createToken('mobile')->plainTextToken;
        } else {
            // Fallback: token firmado simple
            $token = base64_encode($user->id . '|' . now()->timestamp . '|' . md5($user->password));
        }

        return response()->json([
            'success'  => true,
            'token'    => $token,
            'user'     => [
                'id'       => $user->id,
                'name'     => $user->full_name,
                'username' => $user->username,
            ],
            'message'  => 'Login exitoso',
        ]);
    }

    // ─── GET /api/mobile/cartera ──────────────────────────────────────────────
    public function cartera(Request $request)
    {
        $agente = $request->user();

        $prestamos = prestamosModel::with(['cliente', 'cuotas'])
            ->where('agente_id', $agente->id)
            ->whereNull('fecha_clasificacion')
            ->where('estado', 1)
            ->where('desembolsado', 1)
            ->get();

        // Agrupar por cliente
        $clientesMap = [];
        foreach ($prestamos as $prestamo) {
            $cliente = $prestamo->cliente;
            if (!$cliente) continue;

            $cid = $cliente->id;
            if (!isset($clientesMap[$cid])) {
                $clientesMap[$cid] = [
                    'id'            => $cliente->id,
                    'id_enc'        => $cliente->id_enc,
                    'nombres'       => $cliente->nombres,
                    'apellidos'     => $cliente->apellidos,
                    'full_name'     => $cliente->full_name,
                    'cedula'        => $cliente->cedula ?? '',
                    'telefono1'     => $cliente->telefono1 ?? '',
                    'direccion'     => $cliente->direccion ?? '',
                    'prestamos'     => [],
                    'totalPendiente'=> 0,
                    'categoria'     => 'AL_DIA',
                ];
            }

            // Cuotas del préstamo
            $cuotas = $prestamo->cuotas->map(function ($c) {
                return [
                    'id'                    => $c->id,
                    'id_enc'                => encode($c->id),
                    'numero_cuota'          => $c->numero_cuota,
                    'fecha_cuota'           => $c->fecha_cuota,
                    'monto_cuota'           => (float)$c->monto_cuota,
                    'monto_pendiente_cuota' => (float)$c->monto_pendiente_cuota,
                    'estado'                => $c->estado,
                ];
            })->values()->toArray();

            $clientesMap[$cid]['prestamos'][] = [
                'id'             => $prestamo->id,
                'id_enc'         => $prestamo->id_enc,
                'consecutivo'    => $prestamo->consecutivo,
                'monto'          => (float)$prestamo->monto,
                'pendiente_abono'=> (float)$prestamo->pendiente_abono,
                'moneda'         => $prestamo->moneda_prestamo ?? 1,
                'forma_pago_tipo'=> $prestamo->forma_pago_tipo,
                'estado'         => $prestamo->estado,
                'agente_id'      => $prestamo->agente_id,
                'es_externo'     => false,
                'cuotas'         => $cuotas,
            ];

            $clientesMap[$cid]['totalPendiente'] += (float)$prestamo->pendiente_abono;
        }

        // Determinar categoría de cada cliente
        $hoy = now()->toDateString();
        foreach ($clientesMap as &$c) {
            $c['categoria'] = $this->determinarCategoria($c['prestamos'], $hoy);
        }

        return response()->json([
            'success'  => true,
            'clientes' => array_values($clientesMap),
        ]);
    }

    // ─── POST /api/mobile/abono ───────────────────────────────────────────────
    public function abono(Request $request)
    {
        $request->validate([
            'prestamo_id'  => 'required|integer',
            'monto'        => 'required|numeric|min:0.01',
            'fecha_abono'  => 'required|date',
        ]);

        $agente    = $request->user();
        $prestamoId = $request->prestamo_id;
        $monto      = (float)$request->monto;
        $fechaAbono = $request->fecha_abono;
        $notas      = $request->notas ?? '';

        $prestamo = prestamosModel::where('id', $prestamoId)
            ->where('estado', 1)
            ->first();

        if (!$prestamo) {
            return response()->json(['success' => false, 'message' => 'Préstamo no encontrado'], 404);
        }

        // Verificar que el agente puede cobrar este préstamo
        $esPropio   = $prestamo->agente_id == $agente->id;
        $esExterno  = !$esPropio; // El agente puede cobrar externos también

        try {
            DB::beginTransaction();

            // Crear el abono
            $abono = new abonosModel();
            $abono->prestamo_id     = $prestamoId;
            $abono->fecha_abono     = $fechaAbono;
            $abono->tipo_abono      = 0; // Ordinario
            $abono->estado          = 1;
            $abono->created_user_id = $agente->id;
            $abono->notas           = $notas;
            $abono->save();

            // Aplicar el monto a las cuotas pendientes (de la más antigua a la más reciente)
            $montoRestante = $monto;
            $cuotasPendientes = prestamoCuotasModel::where('prestamo_id', $prestamoId)
                ->whereIn('estado', [1, 2]) // pendiente o parcial
                ->orderBy('numero_cuota', 'asc')
                ->get();

            foreach ($cuotasPendientes as $cuota) {
                if ($montoRestante <= 0) break;

                $pendienteCuota = (float)$cuota->monto_pendiente_cuota;
                $aAplicar = min($montoRestante, $pendienteCuota);

                // Calcular capital e interés proporcional
                $proporcion     = $cuota->monto_cuota > 0 ? ($aAplicar / $cuota->monto_cuota) : 0;
                $capitalAplicado = round($aAplicar * 0.6, 2); // Estimado — ajustar según tu lógica
                $interesAplicado = round($aAplicar - $capitalAplicado, 2);

                // Detalle de abono
                $detalle = new prestamoCuotaAbonoModel();
                $detalle->abono_id          = $abono->id;
                $detalle->prestamo_cuota_id = $cuota->id;
                $detalle->monto_abono       = $aAplicar;
                $detalle->total_capital     = $capitalAplicado;
                $detalle->total_interes     = $interesAplicado;
                $detalle->total_mora        = 0;
                $detalle->estado            = 1;
                $detalle->save();

                // Actualizar cuota
                $cuota->monto_pendiente_cuota = max(0, $pendienteCuota - $aAplicar);
                $cuota->estado = $cuota->monto_pendiente_cuota == 0 ? 3 : 2;
                if ($cuota->estado == 3) $cuota->fecha_pagado = $fechaAbono;
                $cuota->save();

                $montoRestante -= $aAplicar;
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'abono_id'   => $abono->id,
                'local_id'   => $request->local_id ?? null,
                'message'    => 'Abono registrado correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar abono: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── GET /api/mobile/dashboard ────────────────────────────────────────────
    public function dashboard(Request $request)
    {
        $agente = $request->user();
        $hoy    = now()->toDateString();

        $abonos = abonosModel::whereDate('fecha_abono', $hoy)
            ->where('created_user_id', $agente->id)
            ->where('estado', 1)
            ->with('abono_detalle')
            ->get();

        $totalRecuperado = 0;
        $diaRecaudado    = 0;
        $moraRecaudada   = 0;
        $clientesIds     = [];

        foreach ($abonos as $ab) {
            $total = $ab->total_abonado;
            $totalRecuperado += $total;
            $clientesIds[$ab->prestamo->user_id ?? 0] = true;
            // Clasificación simplificada
            $diaRecaudado += $total;
        }

        $totalCartera = prestamosModel::where('agente_id', $agente->id)
            ->whereNull('fecha_clasificacion')
            ->where('estado', 1)
            ->where('desembolsado', 1)
            ->count();

        return response()->json([
            'success'          => true,
            'fecha'            => $hoy,
            'total_recuperado' => $totalRecuperado,
            'dia_recaudado'    => $diaRecaudado,
            'mora_recaudada'   => $moraRecaudada,
            'clientes_cobrados'=> count($clientesIds),
            'total_cartera'    => $totalCartera,
        ]);
    }

    // ─── Helper: categoría del cliente ────────────────────────────────────────
    private function determinarCategoria(array $prestamos, string $hoy): string
    {
        foreach ($prestamos as $p) {
            if ($p['pendiente_abono'] <= 0) continue;

            // Buscar cuota del día
            $cuotas = $p['cuotas'] ?? [];
            foreach ($cuotas as $c) {
                if ($c['estado'] != 3 && $c['fecha_cuota'] === $hoy) return 'DEL_DIA';
            }
            // Mora: cuota vencida pendiente
            foreach ($cuotas as $c) {
                if ($c['estado'] != 3 && $c['fecha_cuota'] < $hoy) return 'EN_MORA';
            }
        }
        return 'AL_DIA';
    }
}
