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

        // Token con Laravel Sanctum
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
                    'id'             => $cliente->id,
                    'id_enc'         => $cliente->id_enc,
                    'nombres'        => $cliente->nombres,
                    'apellidos'      => $cliente->apellidos,
                    'full_name'      => $cliente->full_name,
                    'cedula'         => $cliente->cedula ?? '',
                    'telefono1'      => $cliente->telefono1 ?? '',
                    'direccion'      => $cliente->direccion ?? '',
                    'prestamos'      => [],
                    'totalPendiente' => 0,
                    'categoria'      => 'AL_DIA',
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
                'id'              => $prestamo->id,
                'id_enc'          => $prestamo->id_enc,
                'consecutivo'     => $prestamo->consecutivo,
                'monto'           => (float)$prestamo->monto,
                'pendiente_abono' => (float)$prestamo->pendiente_abono,
                'moneda'          => $prestamo->moneda_prestamo ?? 1,
                'forma_pago_tipo' => $prestamo->forma_pago_tipo,
                'estado'          => $prestamo->estado,
                'agente_id'       => $prestamo->agente_id,
                'es_externo'      => false,
                'cuotas'          => $cuotas,
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
            'prestamo_id' => 'required|integer',
            'monto'       => 'required|numeric|min:0.01',
            'fecha_abono' => 'required|date',
        ]);

        $agente     = $request->user();
        $prestamoId = $request->prestamo_id;
        $monto      = (float)$request->monto;
        $fechaAbono = $request->fecha_abono;

        $prestamo = prestamosModel::where('id', $prestamoId)
            ->where('estado', 1)
            ->first();

        if (!$prestamo) {
            return response()->json(['success' => false, 'message' => 'Préstamo no encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            // Crear el abono con los mismos campos que usa la web
            $abono                      = new abonosModel();
            $abono->prestamo_id         = $prestamoId;
            $abono->fecha_abono         = $fechaAbono;
            $abono->tipo_abono          = 1; // Ordinario
            $abono->estado              = 1;
            $abono->anulado_user_id     = null;
            $abono->created_user_id     = $agente->id;
            $abono->total_efectivo      = $monto;
            $abono->total_tarjeta       = 0;
            $abono->total_cheque        = 0;
            $abono->total_transferencia = 0;
            $abono->referencia_tarjeta      = '';
            $abono->referencia_cheque       = '';
            $abono->referencia_transferencia = '';
            $abono->save();

            // Aplicar el monto a las cuotas pendientes (misma lógica que la web)
            // Prioridad: Interés → Capital → Mora
            $valorRestante = $monto;
            $cuotas = prestamoCuotasModel::where('prestamo_id', $prestamoId)
                ->whereIn('estado', [1, 2])
                ->orderBy('numero_cuota', 'asc')
                ->get();

            $totalAbonado2 = 0;

            foreach ($cuotas as $cuota) {
                if ($valorRestante <= 0) break;
                if ($cuota->monto_pendiente_cuota <= 0) continue;

                $totalAbonoIntereses = (float)$cuota->total_pendiente_interes_cuota;
                $totalAbonoCapital   = (float)$cuota->total_pendiente_capital_cuota;
                $totalAbonoMora      = (float)$cuota->total_pendiente_mora_cuota;

                $montoAbonarInteres = 0;
                $montoAbonarCapital = 0;
                $montoAbonarMora    = 0;
                $totalAbonado       = 0;

                // Interés primero
                if ($totalAbonoIntereses > 0) {
                    $totalAbonoIntereses -= $valorRestante;
                    if ($totalAbonoIntereses <= 0) {
                        $montoAbonarInteres = (float)$cuota->total_pendiente_interes_cuota;
                        $valorRestante      = abs($totalAbonoIntereses);
                    } else {
                        $montoAbonarInteres = (float)$cuota->total_pendiente_interes_cuota - $totalAbonoIntereses;
                        $valorRestante     -= $montoAbonarInteres;
                    }
                    $totalAbonado += $montoAbonarInteres;
                }

                // Capital
                if ($totalAbonoCapital > 0 && $valorRestante > 0) {
                    $totalAbonoCapital -= $valorRestante;
                    if ($totalAbonoCapital <= 0) {
                        $montoAbonarCapital = (float)$cuota->total_pendiente_capital_cuota;
                        $valorRestante      = abs($totalAbonoCapital);
                    } else {
                        $montoAbonarCapital = (float)$cuota->total_pendiente_capital_cuota - $totalAbonoCapital;
                        $valorRestante     -= $montoAbonarCapital;
                    }
                    $totalAbonado += $montoAbonarCapital;
                }

                // Mora
                if ($totalAbonoMora > 0 && $valorRestante > 0) {
                    $totalAbonoMora -= $valorRestante;
                    if ($totalAbonoMora <= 0) {
                        $montoAbonarMora = (float)$cuota->total_pendiente_mora_cuota;
                        $valorRestante   = abs($totalAbonoMora);
                    } else {
                        $montoAbonarMora = (float)$cuota->total_pendiente_mora_cuota - $totalAbonoMora;
                        $valorRestante  -= $montoAbonarMora;
                    }
                    $totalAbonado += $montoAbonarMora;
                }

                // Detalle del abono (prestamo_cuota_abono)
                $abonoCuota                    = new prestamoCuotaAbonoModel();
                $abonoCuota->abono_id          = $abono->id;
                $abonoCuota->prestamo_cuota_id = $cuota->id;
                $abonoCuota->estado            = 1;
                $abonoCuota->monto_abono       = $totalAbonado;
                $abonoCuota->total_interes     = $montoAbonarInteres;
                $abonoCuota->total_capital     = $montoAbonarCapital;
                $abonoCuota->total_mora        = $montoAbonarMora;
                $abonoCuota->tipo_abono        = 1;
                $abonoCuota->fecha_abono       = \Carbon\Carbon::now();
                $abonoCuota->created_user_id   = $agente->id;
                $abonoCuota->save();

                $totalAbonado2 += $totalAbonado;

                // Actualizar estado de la cuota
                $cuota->refresh();
                if ($cuota->monto_pendiente_cuota == 0) {
                    $cuota->estado       = 3;
                    $cuota->fecha_pagado = \Carbon\Carbon::now();
                    $cuota->save();
                }

                // Si el préstamo quedó saldado, marcarlo como pagado
                if ($cuota->prestamo->pendiente_abono == 0) {
                    $cuota->prestamo->estado = 2;
                    $cuota->prestamo->save();
                }
            }

            if ($totalAbonado2 === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron montos pendientes para aplicar el abono',
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'abono_id' => $abono->id,
                'local_id' => $request->local_id ?? null,
                'message'  => 'Abono registrado correctamente',
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

        $abonos = abonosModel::with(['prestamo'])
            ->whereHas('prestamo', function ($q) use ($agente) {
                $q->where('agente_id', $agente->id);
            })
            ->whereDate('fecha_abono', $hoy)
            ->where('estado', 1)
            ->get();

        $totalRecuperado = 0;
        $diaRecaudado    = 0;
        $moraRecaudada   = 0;
        $clientesIds     = [];

        foreach ($abonos as $ab) {
            $total            = $ab->total_abonado;
            $totalRecuperado += $total;
            $diaRecaudado    += $total;
            $clientesIds[$ab->prestamo->user_id ?? 0] = true;
        }

        $totalCartera = prestamosModel::where('agente_id', $agente->id)
            ->whereNull('fecha_clasificacion')
            ->where('estado', 1)
            ->where('desembolsado', 1)
            ->count();

        return response()->json([
            'success'           => true,
            'fecha'             => $hoy,
            'total_recuperado'  => $totalRecuperado,
            'dia_recaudado'     => $diaRecaudado,
            'mora_recaudada'    => $moraRecaudada,
            'clientes_cobrados' => count($clientesIds),
            'total_cartera'     => $totalCartera,
        ]);
    }

    // ─── Helper: categoría del cliente ────────────────────────────────────────
    private function determinarCategoria(array $prestamos, string $hoy): string
    {
        foreach ($prestamos as $p) {
            if ($p['pendiente_abono'] <= 0) continue;

            $cuotas = $p['cuotas'] ?? [];

            // Cuota del día
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
