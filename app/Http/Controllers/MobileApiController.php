<?php

namespace App\Http\Controllers;

use App\Models\abonosModel;
use App\Models\prestamoCuotasModel;
use App\Models\prestamoCuotaAbonoModel;
use App\Models\prestamosModel;
use App\Models\User;
use Carbon\Carbon;
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

        $roleName = $user->tipo_usuario == 2 ? 'GERENTE' : 'AGENTE';
        return response()->json([
            'success'  => true,
            'token'    => $token,
            'user'     => [
                'id'       => $user->id,
                'name'     => $user->full_name,
                'username' => $user->username,
                'role'     => $roleName,
            ],
            'message'  => 'Login exitoso',
        ]);
    }

    // ─── GET /api/mobile/cartera ──────────────────────────────────────────────
    public function cartera(Request $request)
    {
        $agente = $request->user();

        $hoy = \Carbon\Carbon::now()->format('Y-m-d');
        $prestamos = prestamosModel::with(['cliente', 'cuotas', 'abonos' => function($q) { $q->where('estado', 1)->orderBy('id', 'desc'); }])
            ->where('agente_id', $agente->id)
            ->whereNull('fecha_clasificacion')
            ->where('desembolsado', 1)
            ->where(function($q) use ($hoy) {
                $q->where('estado', 1) // Activos
                  ->orWhereHas('abonos', function($aq) use ($hoy) {
                      $aq->where('estado', 1)
                         ->where(function($dateQ) use ($hoy) {
                             $dateQ->whereDate('fecha_abono', $hoy)
                                   ->orWhereDate('created_at', $hoy);
                         });
                  });
            })
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

            $abonosHoy = $prestamo->abonos->filter(function($a) use ($hoy) {
                if ($a->estado != 1) return false;
                $fAbono = substr($a->fecha_abono ?? '', 0, 10);
                $fCreated = substr($a->created_at ?? '', 0, 10);
                return $fAbono === $hoy || $fCreated === $hoy;
            });
            $montoCobradoHoy = $abonosHoy->sum(function($a) {
                return (float)$a->total_abonado;
            });
            $ultimoAbonoHoy = $abonosHoy->first();

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
                'cobrado_hoy'     => $montoCobradoHoy,
                'ultimo_abono_id' => $ultimoAbonoHoy ? $ultimoAbonoHoy->id : null,
                'tiene_abono_hoy' => $montoCobradoHoy > 0,
                'promedio_atraso' => (float)$prestamo->promedio_dias_atraso,
            ];

            $clientesMap[$cid]['totalPendiente'] += (float)$prestamo->pendiente_abono;
        }

        // Determinar categoría de cada cliente
        $hoy = now()->toDateString();
        foreach ($clientesMap as &$c) {
            $c['categoria'] = $this->determinarCategoria($c['prestamos'], $hoy);
        }

        return response()->json([
            'success'   => true,
            'fecha_hoy' => $hoy,
            'clientes'  => array_values($clientesMap),
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
    // ─── GET /api/mobile/dashboard (Recaudo del Día - Misma lógica exacta que la web) ───────
    public function dashboard(Request $request)
    {
        $agente = $request->user();
        $hoy = \Carbon\Carbon::today()->toDateString();

        // Si es ADMINISTRATIVO / GERENTE (tipo_usuario = 2)
        if ($agente->tipo_usuario == 2) {
            $agentesAsignados = \App\Models\userAsignadoModel::where('user_id', $agente->id)
                ->get()->pluck('admin_asignado_id')->toArray();
            $tieneAsignados = count($agentesAsignados) > 0;

            $solicitudesPendientes = \App\Models\prestamosModel::where('estado_aprobacion', 1)
                ->where('desembolsado', 0)
                ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                    $q->whereIn('agente_id', $agentesAsignados);
                })
                ->count();

            $desembolsosPendientes = \App\Models\prestamosModel::where('estado_aprobacion', 2)
                ->where('desembolsado', 0)
                ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                    $q->whereIn('agente_id', $agentesAsignados);
                })
                ->count();

            $abonosHoy = \App\Models\abonosModel::whereDate('created_at', \Carbon\Carbon::today())
                ->where('estado', 1)
                ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                    $q->whereHas('prestamo', function($q2) use ($agentesAsignados) {
                        $q2->whereIn('agente_id', $agentesAsignados);
                    });
                })
                ->get();

            $resumen = [
                'total_recuperado'    => 0,
                'dia_recaudado'       => 0,
                'mora_recaudada'      => 0,
                'proximo_recaudado'   => 0,
                'vencido_recaudado'   => 0,
                'total_clientes'      => 0,
            ];
            $clientesUnicos = [];
            $abonoIds = $abonosHoy->pluck('id')->toArray();

            $detallesPorAbono = \DB::table('prestamo_cuota_abono as pca')
                ->join('prestamo_coutas as pc', 'pc.id', '=', 'pca.prestamo_cuota_id')
                ->whereIn('pca.abono_id', $abonoIds)
                ->where('pca.estado', 1)
                ->select('pca.abono_id', 'pca.monto_abono', 'pc.fecha_cuota')
                ->get()
                ->groupBy('abono_id');

            foreach ($abonosHoy as $abono) {
                $p = \App\Models\prestamosModel::find($abono->prestamo_id);
                if (!$p) continue;

                $cuotasFuturasPend = \DB::table('prestamo_coutas')
                    ->where('prestamo_id', $p->id)
                    ->where('fecha_cuota', '>=', date('Y-m-d'))
                    ->whereIn('estado', [1, 2])
                    ->count();

                $totalAbonado = \DB::table('prestamo_cuota_abono as pca')
                    ->join('prestamo_coutas as pc', 'pc.id', '=', 'pca.prestamo_cuota_id')
                    ->where('pc.prestamo_id', $p->id)
                    ->where('pca.estado', 1)
                    ->sum('pca.monto_abono');

                $totalCuotasMonto = \DB::table('prestamo_coutas')
                    ->where('prestamo_id', $p->id)
                    ->sum('monto_cuota');

                $saldoPendiente = max(0, $totalCuotasMonto - $totalAbonado);

                $clientesUnicos[$p->user_id] = true;
                $monto = (float)($abono->total_abono ?? $abono->total_efectivo ?? 0);
                if ($monto <= 0) {
                    $monto = (float)($abono->total_tarjeta ?? 0) + (float)($abono->total_cheque ?? 0) + (float)($abono->total_transferencia ?? 0);
                }
                
                $resumen['total_recuperado'] += $monto;

                if ($p->estado == 3 || ($cuotasFuturasPend == 0 && $saldoPendiente > 0)) {
                    $resumen['vencido_recaudado'] += $monto;
                } else {
                    $fechaAbono = substr($abono->created_at, 0, 10);
                    $detalles = $detallesPorAbono->get($abono->id, collect());
                    foreach ($detalles as $detalle) {
                        $montoCuota = (float)$detalle->monto_abono;
                        $fechaCuota = substr($detalle->fecha_cuota, 0, 10);
                        if ($fechaCuota < $fechaAbono) {
                            $resumen['mora_recaudada'] += $montoCuota;
                        } elseif ($fechaCuota > $fechaAbono) {
                            $resumen['proximo_recaudado'] += $montoCuota;
                        } else {
                            $resumen['dia_recaudado'] += $montoCuota;
                        }
                    }
                }
            }
            $resumen['total_clientes'] = count($clientesUnicos);

            // Recaudación por gestores (hoy)
            $recuperacionAgentes = \App\Models\abonosModel::whereDate('created_at', \Carbon\Carbon::today())
                ->where('estado', 1)
                ->when($tieneAsignados, function($q) use ($agentesAsignados) {
                    $q->whereHas('prestamo', function($q2) use ($agentesAsignados) {
                        $q2->whereIn('agente_id', $agentesAsignados);
                    });
                })
                ->select('created_user_id',
                    \DB::raw('SUM(COALESCE(total_efectivo,0) + COALESCE(total_tarjeta,0) + COALESCE(total_cheque,0) + COALESCE(total_transferencia,0)) as total_recaudado'),
                    \DB::raw('SUM(COALESCE(total_efectivo,0) + COALESCE(total_tarjeta,0) + COALESCE(total_cheque,0)) as cordobas'),
                    \DB::raw('SUM(COALESCE(total_transferencia,0)) as dolares'),
                    \DB::raw('MAX(created_at) as ultimo_pago')
                )
                ->groupBy('created_user_id')
                ->get();

            $recaudacionPorGestor = [];
            foreach ($recuperacionAgentes as $ra) {
                $gestor = \App\Models\User::find($ra->created_user_id);
                if (!$gestor) continue;

                $recaudacionPorGestor[] = [
                    'gestorId' => $gestor->id,
                    'gestorName' => $gestor->full_name,
                    'totalRecaudado' => (float)$ra->total_recaudado,
                    'cordobas' => (float)$ra->cordobas,
                    'dolares' => (float)$ra->dolares,
                    'ultimaCuota' => $ra->ultimo_pago,
                    'ultimaCuotaFormateada' => \Carbon\Carbon::parse($ra->ultimo_pago)->format('H:i')
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'gestorName' => $agente->full_name,
                    'totalRecuperacion' => round($resumen['total_recuperado'], 2),
                    'diaRecaudado' => round($resumen['dia_recaudado'], 2),
                    'moraRecaudada' => round($resumen['mora_recaudada'], 2),
                    'proximoRecaudado' => round($resumen['proximo_recaudado'], 2),
                    'vencidoRecaudado' => round($resumen['vencido_recaudado'], 2),
                    'totalClientesCobrados' => $resumen['total_clientes'],
                    'solicitudesPendientes' => $solicitudesPendientes,
                    'desembolsosPendientes' => $desembolsosPendientes,
                    'recaudacionPorGestor' => $recaudacionPorGestor
                ]
            ]);
        }

        // Si es GESTOR / AGENTE (original)
        $abonosHoy = \DB::table('abonos as a')
            ->join('prestamos as p', 'p.id', '=', 'a.prestamo_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin(\DB::raw('(SELECT abono_id,
                                    SUM(total_capital) as capital,
                                    SUM(total_interes) as interes,
                                    SUM(total_mora)    as mora,
                                    SUM(monto_abono)   as total
                                FROM prestamo_cuota_abono
                                WHERE estado = 1
                                GROUP BY abono_id) as t_pca'), 'a.id', '=', 't_pca.abono_id')
            ->leftJoin(\DB::raw('(SELECT pca.abono_id, MIN(pc.fecha_cuota) as fecha_cuota_min
                                FROM prestamo_cuota_abono pca
                                JOIN prestamo_coutas pc ON pc.id = pca.prestamo_cuota_id
                                WHERE pca.estado = 1
                                GROUP BY pca.abono_id) as t_fc'), 'a.id', '=', 't_fc.abono_id')
            ->leftJoin(\DB::raw('(SELECT prestamo_id,
                                    COUNT(*) as cuotas_futuras_pend
                                FROM prestamo_coutas
                                WHERE fecha_cuota >= CURDATE()
                                  AND estado IN (1,2)
                                GROUP BY prestamo_id) as t_fut'), 'p.id', '=', 't_fut.prestamo_id')
            ->leftJoin(\DB::raw('(SELECT pc2.prestamo_id,
                                    SUM(pc2.monto_cuota) as total_cuotas,
                                    COALESCE((SELECT SUM(pca2.monto_abono)
                                              FROM prestamo_cuota_abono pca2
                                              JOIN prestamo_coutas pc3 ON pc3.id = pca2.prestamo_cuota_id
                                              WHERE pc3.prestamo_id = pc2.prestamo_id
                                                AND pca2.estado = 1), 0) as total_abonado_prest
                                FROM prestamo_coutas pc2
                                GROUP BY pc2.prestamo_id) as t_saldo'), 'p.id', '=', 't_saldo.prestamo_id')
            ->where('a.created_user_id', $agente->id)
            ->where('a.estado', 1)
            ->where(function($dateQ) use ($hoy) {
                $dateQ->whereDate('a.fecha_abono', $hoy)
                      ->orWhereDate('a.created_at', $hoy);
            })
            ->select(
                'a.id', 'a.prestamo_id', 'a.fecha_abono', 'a.tipo_abono', 'a.total_transferencia',
                'p.estado as prestamo_estado',
                'p.user_id as cliente_id',
                \DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_nombre"),
                't_pca.capital  as total_abonado_capital',
                't_pca.interes  as total_abonado_interes',
                't_pca.mora     as total_abonado_mora',
                't_pca.total    as total_abonado',
                't_fc.fecha_cuota_min',
                \DB::raw('COALESCE(t_fut.cuotas_futuras_pend, 0) as cuotas_futuras_pend'),
                \DB::raw('COALESCE(t_saldo.total_cuotas, 0) - COALESCE(t_saldo.total_abonado_prest, 0) as saldo_pendiente_prest')
            )
            ->get();

        $resumen = [
            'total_recuperado'    => 0,
            'total_transferencia' => 0,
            'dia_recaudado'       => 0,
            'mora_recaudada'      => 0,
            'proximo_recaudado'   => 0,
            'vencido_recaudado'   => 0,
            'total_clientes'      => 0,
        ];
        $clientesUnicos = [];
        $abonoIds = $abonosHoy->pluck('id')->toArray();
        $detallesPorAbono = \DB::table('prestamo_cuota_abono as pca')
            ->join('prestamo_coutas as pc', 'pc.id', '=', 'pca.prestamo_cuota_id')
            ->whereIn('pca.abono_id', $abonoIds)
            ->where('pca.estado', 1)
            ->select('pca.abono_id', 'pca.monto_abono', 'pc.fecha_cuota')
            ->get()
            ->groupBy('abono_id');

        foreach ($abonosHoy as $abono) {
            $monto = (float) ($abono->total_abonado ?? 0);
            $clientesUnicos[$abono->cliente_id] = true;
            $resumen['total_recuperado']    += $monto;
            $resumen['total_transferencia'] += (float) ($abono->total_transferencia ?? 0);

            if ($abono->prestamo_estado == 3 ||
                ($abono->cuotas_futuras_pend == 0 && $abono->saldo_pendiente_prest > 0)) {
                $resumen['vencido_recaudado'] += $monto;
            } else {
                $fechaAbono = substr($abono->fecha_abono, 0, 10);
                $detalles   = $detallesPorAbono->get($abono->id, collect());
                foreach ($detalles as $detalle) {
                    $montoCuota  = (float) $detalle->monto_abono;
                    $fechaCuota  = substr($detalle->fecha_cuota, 0, 10);
                    if ($fechaCuota < $fechaAbono) {
                        $resumen['mora_recaudada']    += $montoCuota;
                    } elseif ($fechaCuota > $fechaAbono) {
                        $resumen['proximo_recaudado'] += $montoCuota;
                    } else {
                        $resumen['dia_recaudado']     += $montoCuota;
                    }
                }
            }
        }
        $resumen['total_clientes'] = count($clientesUnicos);

        return response()->json([
            'success'               => true,
            'fecha'                 => $hoy,
            'total_recuperado'      => round($resumen['total_recuperado'], 2),
            'total_transferencia'   => round($resumen['total_transferencia'], 2),
            'dia_recaudado'         => round($resumen['dia_recaudado'], 2),
            'mora_recaudada'        => round($resumen['mora_recaudada'], 2),
            'proximo_recaudado'     => round($resumen['proximo_recaudado'], 2),
            'vencido_recaudado'     => round($resumen['vencido_recaudado'], 2),
            'clientes_cobrados'     => $resumen['total_clientes'],
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

    public function recibo(Request $request)
    {
        $abonoId = $request->abono_id ?? $request->paymentId;
        $prestamoId = $request->prestamo_id ?? $request->creditId;

        $abono = null;
        if ($abonoId) {
            $abono = abonosModel::with(['prestamo.cliente', 'prestamo.cuotas', 'user_create', 'abono_detalle'])
                ->where('id', $abonoId)
                ->first();
        } elseif ($prestamoId) {
            $abono = abonosModel::with(['prestamo.cliente', 'prestamo.cuotas', 'user_create', 'abono_detalle'])
                ->where('prestamo_id', $prestamoId)
                ->where('estado', 1)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$abono) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el recibo o abono solicitado',
            ], 404);
        }

        $receiptData = $this->calcularDatosRecibo($abono);

        return response()->json([
            'success' => true,
            'data'    => $receiptData,
        ]);
    }

    // ─── GET /api/mobile/recibo/{id} ──────────────────────────────────────────
    public function reciboPorId($id)
    {
        $abono = abonosModel::with(['prestamo.cliente', 'prestamo.cuotas', 'user_create', 'abono_detalle'])
            ->where('id', $id)
            ->first();

        if (!$abono) {
            return response()->json([
                'success' => false,
                'message' => 'Recibo no encontrado',
            ], 404);
        }

        $receiptData = $this->calcularDatosRecibo($abono);

        return response()->json([
            'success' => true,
            'data'    => $receiptData,
        ]);
    }

    // ─── Calcular datos de recibo (lógica idéntica a recibo.blade.php) ────────
    private function calcularDatosRecibo(abonosModel $abono): array
    {
        $fechaAbono = \Carbon\Carbon::parse($abono->fecha_abono ?? $abono->created_at)->startOfDay();
        $fechaReferencia = $fechaAbono;

        $montoCuotaDia = 0;
        $montoAtraso = 0;

        // 1. Cuota del día: cuotas cuya fecha coincide con el día del abono
        $cuotasDelDia = $abono->prestamo->cuotas->filter(function($cuota) use ($fechaReferencia) {
            return \Carbon\Carbon::parse($cuota->fecha_cuota)->startOfDay()->equalTo($fechaReferencia);
        });

        foreach ($cuotasDelDia as $cuota) {
            $abonadoAntes = DB::table('prestamo_cuota_abono as PCA')
                ->join('abonos as A', 'A.id', 'PCA.abono_id')
                ->where('PCA.prestamo_cuota_id', $cuota->id)
                ->where('A.id', '<', $abono->id)
                ->where('PCA.estado', 1)
                ->where('A.estado', 1)
                ->sum('PCA.monto_abono');

            $pendiente = (float)$cuota->monto_cuota - (float)$abonadoAntes;
            if ($pendiente > 0) {
                $montoCuotaDia += $pendiente;
            }
        }

        // 2. Mora / Atraso: cuotas anteriores a la fecha del abono
        $cuotasVencidas = $abono->prestamo->cuotas->filter(function($cuota) use ($fechaReferencia) {
            return \Carbon\Carbon::parse($cuota->fecha_cuota)->startOfDay()->lt($fechaReferencia);
        });

        foreach ($cuotasVencidas as $cuota) {
            $abonadoAntes = DB::table('prestamo_cuota_abono as PCA')
                ->join('abonos as A', 'A.id', 'PCA.abono_id')
                ->where('PCA.prestamo_cuota_id', $cuota->id)
                ->where('A.id', '<', $abono->id)
                ->where('PCA.estado', 1)
                ->where('A.estado', 1)
                ->sum('PCA.monto_abono');

            $pendiente = (float)$cuota->monto_cuota - (float)$abonadoAntes;
            if ($pendiente > 0) {
                $montoAtraso += $pendiente;
            }
        }

        // 3. Días de mora desde la cuota más antigua vencida
        $cuotaMasAntigua = $cuotasVencidas->filter(function($cuota) use ($abono) {
            $abonadoAntes = DB::table('prestamo_cuota_abono as PCA')
                ->join('abonos as A', 'A.id', 'PCA.abono_id')
                ->where('PCA.prestamo_cuota_id', $cuota->id)
                ->where('A.id', '<', $abono->id)
                ->where('PCA.estado', 1)
                ->where('A.estado', 1)
                ->sum('PCA.monto_abono');

            return (float)$abonadoAntes < (float)$cuota->monto_cuota;
        })->sortBy('fecha_cuota')->first();

        $diasMora = $cuotaMasAntigua ? (int)$fechaReferencia->diffInDays(\Carbon\Carbon::parse($cuotaMasAntigua->fecha_cuota)) : 0;
        $totalAPagar = $montoCuotaDia + $montoAtraso;

        // 4. Saldo anterior y saldo nuevo
        $abonadoHastaAntesDeEste = DB::table('prestamos')
            ->join('prestamo_coutas as PC', 'PC.prestamo_id', 'prestamos.id')
            ->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'PC.id')
            ->join('abonos as A', 'A.id', 'PCA.abono_id')
            ->where('prestamos.id', $abono->prestamo_id)
            ->where('PCA.estado', 1)
            ->where('A.estado', 1)
            ->where('A.id', '<', $abono->id)
            ->sum('PCA.monto_abono');

        $abonadoHastaEsteAbono = DB::table('prestamos')
            ->join('prestamo_coutas as PC', 'PC.prestamo_id', 'prestamos.id')
            ->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'PC.id')
            ->join('abonos as A', 'A.id', 'PCA.abono_id')
            ->where('prestamos.id', $abono->prestamo_id)
            ->where('PCA.estado', 1)
            ->where('A.estado', 1)
            ->where('A.id', '<=', $abono->id)
            ->sum('PCA.monto_abono');

        $sumaCuotas = (float)($abono->prestamo->suma_cuotas ?? $abono->prestamo->cuotas->sum('monto_cuota'));
        $saldoAnterior = max(0, $sumaCuotas - (float)$abonadoHastaAntesDeEste);
        $nuevoSaldo    = max(0, $sumaCuotas - (float)$abonadoHastaEsteAbono);

        $cliente = $abono->prestamo->cliente;
        $usuario = $abono->user_create ?? Auth::user();
        $sucursalNombre = ($cliente && $cliente->sucursal) ? $cliente->sucursal->nombre : 'PRINCIPAL';

        $totalAbonado = (float)$abono->total_abonado;
        if ($totalAbonado <= 0 && $abono->total_efectivo > 0) {
            $totalAbonado = (float)$abono->total_efectivo;
        }

        return [
            'transactionNumber' => 'REC-' . str_pad($abono->id, 6, '0', STR_PAD_LEFT),
            'creditNumber'      => (string)($abono->prestamo->consecutivo ?? $abono->prestamo->id),
            'clientName'        => $cliente ? ($cliente->full_name ?? trim($cliente->nombres . ' ' . $cliente->apellidos)) : 'CLIENTE',
            'clientCode'        => $cliente ? ($cliente->cedula ?? (string)$cliente->id) : '',
            'paymentDate'       => \Carbon\Carbon::parse($abono->created_at)->format('d/m/Y h:i A'),
            'cuotaDelDia'       => round($montoCuotaDia, 2),
            'montoAtrasado'     => round($montoAtraso, 2),
            'diasMora'          => $diasMora,
            'totalAPagar'       => round($totalAPagar, 2),
            'montoCancelacion'  => round($saldoAnterior, 2),
            'amountPaid'        => round($totalAbonado, 2),
            'saldoAnterior'     => round($saldoAnterior, 2),
            'nuevoSaldo'        => round($nuevoSaldo, 2),
            'managedBy'         => $usuario ? ($usuario->full_name ?? $usuario->name) : 'AGENTE',
            'sucursal'          => $sucursalNombre,
            'role'              => 'AGENTE DE COBRO',
            'abono_id'          => $abono->id,
            'prestamo_id'       => $abono->prestamo_id,
            'tipo_abono'        => $abono->tipo_abono,
            'is_cancelacion'    => ($abono->tipo_abono == 3 || str_contains(strtolower($abono->referencia_transferencia ?? ''), 'cancelaci')),
            'concepto'          => ($abono->tipo_abono == 3 || str_contains(strtolower($abono->referencia_transferencia ?? ''), 'cancelaci')) ? 'CANCELACIÓN DE CRÉDITO' : 'ABONO DE CRÉDITO',
        ];
    }

    // ─── GET /api/mobile/clientes-externos ──────────────────────────────────────
    // Búsqueda de clientes con préstamos activos que NO pertenecen a la cartera del agente
    public function clientesExternos(Request $request)
    {
        $agente = $request->user();
        $buscar = trim($request->get('buscar', ''));

        if (strlen($buscar) < 2) {
            return response()->json([
                'success' => true,
                'clientes' => []
            ]);
        }

        // IDs de clientes que ya pertenecen a la cartera del agente (para excluirlos)
        $idsCarteraPropia = prestamosModel::where('agente_id', $agente->id)
            ->whereNull('fecha_clasificacion')
            ->where('estado', 1)
            ->where('desembolsado', 1)
            ->pluck('user_id')
            ->toArray();

        $prestamos = prestamosModel::with([
                'cliente', 
                'cliente.departamento_municipio.departamento',
                'cuotas',
                'abonos' => function($q) { $q->where('estado', 1)->orderBy('id', 'desc'); }
            ])
            ->whereNull('fecha_clasificacion')
            ->where('estado', 1)
            ->where('desembolsado', 1)
            ->where('agente_id', '!=', $agente->id)
            ->whereNotIn('user_id', $idsCarteraPropia)
            ->whereHas('cliente', function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('nombres', 'like', '%' . $buscar . '%')
                        ->orWhere('apellidos', 'like', '%' . $buscar . '%')
                        ->orWhere('cedula', 'like', '%' . $buscar . '%');
                });
            })
            ->limit(25)
            ->get();

        $hoy = \Carbon\Carbon::now()->format('Y-m-d');
        $lista = [];

        foreach ($prestamos as $prestamo) {
            $cliente = $prestamo->cliente;
            if (!$cliente) continue;

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

            $cuotaDelDia = $prestamo->cuotas->filter(function($c) use ($hoy) {
                return substr($c->fecha_cuota ?? '', 0, 10) === $hoy && $c->estado != 3;
            })->first();

            $cuotaNum = $cuotaDelDia ? $cuotaDelDia->numero_cuota : ($prestamo->cuotas->where('estado', '!=', 3)->first()->numero_cuota ?? 1);
            $cuotaMonto = $cuotaDelDia ? (float)$cuotaDelDia->monto_pendiente_cuota : (float)($prestamo->cuotas->where('estado', '!=', 3)->first()->monto_pendiente_cuota ?? 0);

            $lista[] = [
                'id'           => $prestamo->id,
                'id_enc'       => $prestamo->id_enc,
                'creditNumber' => (string)($prestamo->consecutivo ?? $prestamo->id),
                'clientId'     => $cliente->id,
                'clientName'   => $cliente->full_name ?? trim($cliente->nombres . ' ' . $cliente->apellidos),
                'clientCode'   => $cliente->cedula ?? (string)$cliente->id,
                'clientAddress'=> $cliente->direccion ?? '',
                'cuotaNumero'  => $cuotaNum,
                'es_externo'   => true,
                'agente_id'    => $prestamo->agente_id,
                'details'      => [
                    'dueTodayAmount'   => $cuotaMonto,
                    'overdueAmount'    => 0,
                    'remainingBalance' => (float)$prestamo->pendiente_abono,
                    'lateDays'         => 0,
                    'diasVencido'      => 0,
                    'paidToday'        => 0,
                ],
                'prestamo'     => [
                    'id'              => $prestamo->id,
                    'id_enc'          => $prestamo->id_enc,
                    'consecutivo'     => $prestamo->consecutivo,
                    'monto'           => (float)$prestamo->monto,
                    'pendiente_abono' => (float)$prestamo->pendiente_abono,
                    'moneda'          => $prestamo->moneda_prestamo ?? 1,
                    'cuotas'          => $cuotas,
                ]
            ];
        }

        return response()->json([
            'success'  => true,
            'clientes' => $lista,
        ]);
    }

    // ─── GET /api/mobile/mis-clientes ──────────────────────────────────────────
    // Listado de clientes asignados a este agente ordenados alfabéticamente (idéntico a la web)
    public function misClientes(Request $request)
    {
        $agente = $request->user();
        $buscar = trim($request->get('search', $request->get('buscar', '')));

        // Obtener clientes del agente que tienen préstamos desembolsados y activos
        $clientes = User::join('prestamos as P', 'users.id', 'P.user_id')
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('users.nombres', 'like', '%' . $buscar . '%')
                      ->orWhere('users.apellidos', 'like', '%' . $buscar . '%')
                      ->orWhere('users.cedula', 'like', '%' . $buscar . '%');
                });
            })
            ->where('P.desembolsado', 1)
            ->whereNotIn('P.estado', [2, 4]) // excluir pagados y anulados
            ->where('P.agente_id', $agente->id)
            ->whereNull('P.fecha_clasificacion')
            ->select('users.*')
            ->distinct()
            ->orderBy('users.nombres', 'asc')
            ->orderBy('users.apellidos', 'asc')
            ->get();

        $all = [];
        $reloan = [];
        $renewal = []; // Pestaña eliminada, retorna vacío

        foreach ($clientes as $c) {
            $prestamosActivos = prestamosModel::where('user_id', $c->id)
                ->where('agente_id', $agente->id)
                ->where('estado', 1)
                ->where('desembolsado', 1)
                ->whereNull('fecha_clasificacion')
                ->get();

            $totalSaldo = $prestamosActivos->sum('pendiente_abono');
            $primerPrestamoActivo = $prestamosActivos->first();

            $clientData = [
                'id'            => $c->id,
                'id_enc'        => $c->id_enc,
                'name'          => $c->full_name ?? trim($c->nombres . ' ' . $c->apellidos),
                'clientNumber'  => $c->codigo_cliente ?? (string)$c->id,
                'cedula'        => $c->cedula ?? '',
                'phone'         => $c->telefono1 ? ($c->telefono1 . ($c->telefono2 ? ' / ' . $c->telefono2 : '')) : ($c->telefono2 ?? ''),
                'address'       => $c->direccion ?? '',
                'municipio'     => ($c->departamento_municipio && $c->departamento_municipio->departamento)
                                     ? ($c->departamento_municipio->nombre . ', ' . $c->departamento_municipio->departamento->nombre)
                                     : '',
                'totalSaldo'    => (float)$totalSaldo,
                'activeCredits' => $prestamosActivos->count(),
                'creditNumber'  => $primerPrestamoActivo ? ($primerPrestamoActivo->consecutivo ?? $primerPrestamoActivo->id) : '',
            ];

            $all[] = $clientData;

            // Clasificación de REPRÉSTAMOS únicamente:
            // - Tiene pagado el 75% o más del monto total / total financiado
            // - Promedio de días de atraso del crédito actual < 2.5 días
            if ($primerPrestamoActivo && $primerPrestamoActivo->monto_financiado > 0) {
                $pagado = max(0, (float)$primerPrestamoActivo->monto_financiado - (float)$primerPrestamoActivo->pendiente_abono);
                $pctPagado = ($pagado / (float)$primerPrestamoActivo->monto_financiado) * 100;
                $promedioAtraso = (float)$primerPrestamoActivo->promedio_dias_atraso;

                if ($pctPagado >= 75 && $pctPagado < 100 && $promedioAtraso < 2.5) {
                    $reloan[] = $clientData;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'all'     => $all,
                'reloan'  => $reloan,
                'renewal' => $renewal,
            ],
        ]);
    }

    public function clienteDetalle(Request $request)
    {
        $clientId = $request->get('clientId', $request->get('id'));
        if (!$clientId) {
            return response()->json(['success' => false, 'message' => 'ID de cliente requerido'], 400);
        }

        $cliente = User::with(['departamento_municipio.departamento'])->find($clientId);
        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
        }

        // Obtener préstamos desembolsados del cliente
        $todosPrestamos = prestamosModel::with([
                'cuotas' => function($q) { $q->orderBy('numero_cuota', 'asc'); },
                'abonos' => function($q) { $q->where('estado', 1)->orderBy('fecha_abono', 'asc')->orderBy('id', 'asc'); },
                'abonos.user_create'
            ])
            ->where('user_id', $cliente->id)
            ->whereNull('fecha_clasificacion')
            ->where('desembolsado', 1)
            ->orderBy('id', 'desc')
            ->get();

        // ── Calcular Promedio de Días de Atraso Global (suma de promedios de todos los créditos / total de créditos)
        $totalPromediosCreditos = 0;
        $conteoCreditosConPromedio = 0;

        foreach ($todosPrestamos as $prest) {
            $totalDiasAtrasoP = 0;
            $totalCuotasP = $prest->cuotas->count();
            foreach ($prest->cuotas as $cuotaAtraso) {
                $fechaPlanCuotaP = \Carbon\Carbon::parse($cuotaAtraso->fecha_cuota);
                if ($cuotaAtraso->estado == 3) {
                    $ultimoAbonoP = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuotaAtraso->id)
                        ->where('estado', 1)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($ultimoAbonoP) {
                        $fechaPagoRealP = \Carbon\Carbon::parse($ultimoAbonoP->created_at);
                        $diasAtrasoP = $fechaPlanCuotaP->diffInDays($fechaPagoRealP, false);
                        if ($diasAtrasoP > 0) $totalDiasAtrasoP += $diasAtrasoP;
                    }
                } elseif (in_array($cuotaAtraso->estado, [1, 2]) && $fechaPlanCuotaP->isPast()) {
                    $diasAtrasoP = $fechaPlanCuotaP->diffInDays(\Carbon\Carbon::now(), false);
                    if ($diasAtrasoP > 0) $totalDiasAtrasoP += $diasAtrasoP;
                }
            }
            $promedioP = $totalCuotasP > 0 ? round($totalDiasAtrasoP / $totalCuotasP, 2) : 0;
            $totalPromediosCreditos += $promedioP;
            $conteoCreditosConPromedio++;
        }

        $promedioAtrasoGlobal = $conteoCreditosConPromedio > 0 
            ? round($totalPromediosCreditos / $conteoCreditosConPromedio, 2) 
            : 0;

        // Tomar ÚNICAMENTE el crédito activo o el más reciente para evitar duplicidad
        $prestamoActivo = $todosPrestamos->where('estado', 1)->first() ?? $todosPrestamos->first();

        $creditsData = [];
        if ($prestamoActivo) {
            $p = $prestamoActivo;

            // 1. Plan de Pago (idéntico al reporte Plan de Pago de la web)
            $saldoAnterior = (float)$p->monto_financiado;
            $paymentPlan = [];
            foreach ($p->cuotas as $c) {
                $valorCuota = (float)$c->monto_cuota;
                $nuevoSaldo = max(0, $saldoAnterior - $valorCuota);
                $capital = (float)($c->monto_cuota - $c->monto_interes);
                $interes = (float)$c->monto_interes;

                $paymentPlan[] = [
                    'id'            => $c->id,
                    'paymentNumber' => $c->numero_cuota,
                    'paymentDate'   => substr($c->fecha_cuota, 0, 10),
                    'principal'     => $capital,
                    'interest'      => $interes,
                    'amount'        => $valorCuota,
                    'saldoAnterior' => $saldoAnterior,
                    'balance'       => $nuevoSaldo,
                    'mora'          => (float)($c->monto_mora ?? 0),
                    'status'        => $c->estado == 3 ? 'PAGADA' : ($c->estado == 2 ? 'PARCIAL' : 'PENDIENTE'),
                ];
                $saldoAnterior = $nuevoSaldo;
            }

            // 2. Historial de Pagos / Abonos (idéntico a Estado de Cuenta / Abonos de la web)
            $paymentHistory = [];
            $hoy = \Carbon\Carbon::now()->format('Y-m-d');
            foreach ($p->abonos as $index => $a) {
                $montoAbonado = (float)($a->total_abonado ?? ($a->total_efectivo + $a->total_tarjeta + $a->total_cheque + $a->total_transferencia));
                $fechaAbono = substr($a->fecha_abono ?? $a->created_at, 0, 10);
                $isCancelacion = ($a->tipo_abono == 3 || str_contains(strtolower($a->referencia_transferencia ?? ''), 'cancelaci'));
                $paymentHistory[] = [
                    'id'                => $a->id,
                    'receiptNumber'     => 'REC-' . str_pad($a->id, 6, '0', STR_PAD_LEFT),
                    'paymentDate'       => $fechaAbono,
                    'amount'            => $montoAbonado,
                    'principalApplied'  => (float)($a->total_abonado_capital ?? 0),
                    'interestApplied'   => (float)($a->total_abonado_interes ?? 0),
                    'moraApplied'       => (float)($a->total_abonado_mora ?? 0),
                    'receivedBy'        => $a->user_create->full_name ?? ($a->user_create->nombres ?? 'Agente'),
                    'status'            => $a->estado == 1 ? 'VÁLIDO' : 'ANULADO',
                    'tipo'              => $isCancelacion ? 'Cancelación Crédito' : ($a->tipo ?? 'Ordinario'),
                    'tipo_abono'        => $a->tipo_abono,
                    'is_cancelacion'    => $isCancelacion,
                    'referencia'        => $a->referencia_transferencia ?? '',
                ];
            }
            // 3. Monto en Mora y Días de Atraso del Crédito Actual
            $cuotasVencidas = $p->cuotas->filter(function($c) use ($hoy) {
                return $c->estado != 3 && substr($c->fecha_cuota, 0, 10) < $hoy;
            });

            $montoEnMora = $cuotasVencidas->sum(function($c) {
                return (float)($c->monto_pendiente_cuota ?? $c->monto_cuota);
            });

            // Días de atraso actual: días desde la primera cuota vencida pendiente
            $primeraCuotaVencida = $cuotasVencidas->sortBy('fecha_cuota')->first();
            $diasAtrasoActual = 0;
            if ($primeraCuotaVencida) {
                $diasAtrasoActual = \Carbon\Carbon::parse(substr($primeraCuotaVencida->fecha_cuota, 0, 10))->diffInDays(\Carbon\Carbon::now());
            }

            // Promedio de días de atraso de este crédito (idéntico a la web)
            $totalDiasAtrasoCredito = 0;
            $totalCuotasCredito = $p->cuotas->count();
            foreach ($p->cuotas as $cuotaAtraso) {
                $fechaPlanCuota = \Carbon\Carbon::parse($cuotaAtraso->fecha_cuota);
                if ($cuotaAtraso->estado == 3) {
                    $ultimoAbono = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuotaAtraso->id)
                        ->where('estado', 1)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($ultimoAbono) {
                        $fechaPagoReal = \Carbon\Carbon::parse($ultimoAbono->created_at);
                        $diasAtraso = $fechaPlanCuota->diffInDays($fechaPagoReal, false);
                        if ($diasAtraso > 0) $totalDiasAtrasoCredito += $diasAtraso;
                    }
                } elseif (in_array($cuotaAtraso->estado, [1, 2]) && $fechaPlanCuota->isPast()) {
                    $diasAtraso = $fechaPlanCuota->diffInDays(\Carbon\Carbon::now(), false);
                    if ($diasAtraso > 0) $totalDiasAtrasoCredito += $diasAtraso;
                }
            }
            $promedioAtrasoCredito = $totalCuotasCredito > 0 ? round($totalDiasAtrasoCredito / $totalCuotasCredito, 2) : 0;

            // Fechas clave
            $fechaDesembolso = substr($p->fecha_desembolso ?? $p->fecha_prestamo, 0, 10);
            $fechaPrimerPago = substr($p->fecha_primer_pago ?? ($p->cuotas->first()->fecha_cuota ?? ''), 0, 10);
            $ultimaCuota = $p->cuotas->sortByDesc('numero_cuota')->first();
            $fechaUltimoPago = $ultimaCuota ? substr($ultimaCuota->fecha_cuota, 0, 10) : '';

            $sumaCuotas = (float)($p->suma_cuotas ?? $p->cuotas->sum('monto_cuota'));
            $totalPagado = max(0, $sumaCuotas - (float)$p->pendiente_abono);

            $creditsData[] = [
                'id'                      => $p->id,
                'creditNumber'            => (string)($p->consecutivo ?? $p->id),
                'interestRate'            => (float)$p->tasa_prestamo,
                'paymentFrequency'        => $p->forma_pago,
                'termMonths'              => (string)$p->plazo_pago,
                'amount'                  => (float)$p->monto_prestamo,
                'totalAmount'             => (float)$p->monto_financiado,
                'installmentAmount'       => (float)$p->monto_cuota,
                'disbursementDate'        => $fechaDesembolso,
                'firstPaymentDate'        => $fechaPrimerPago,
                'dueDate'                 => $fechaUltimoPago,
                'totalPaid'               => (float)$totalPagado,
                'remainingBalance'        => (float)$p->pendiente_abono,
                'overdueAmount'           => (float)$montoEnMora,
                'lateDays'                => (int)$diasAtrasoActual,
                'avgLateDaysCurrentCredit'=> (float)$promedioAtrasoCredito,
                'avgLateDaysGlobal'       => (float)$promedioAtrasoGlobal,
                'status'                  => $p->estado == 1 ? 'Activo' : ($p->estado == 2 ? 'Pagado' : 'Cancelado'),
                'paymentPlan'             => $paymentPlan,
                'paymentHistory'          => $paymentHistory,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'client' => [
                    'id'           => $cliente->id,
                    'name'         => $cliente->full_name ?? trim($cliente->nombres . ' ' . $cliente->apellidos),
                    'clientNumber' => $cliente->codigo_cliente ?? (string)$cliente->id,
                    'cedula'       => $cliente->cedula ?? '',
                    'phone'        => $cliente->telefono1 ? ($cliente->telefono1 . ($cliente->telefono2 ? ' / ' . $cliente->telefono2 : '')) : ($cliente->telefono2 ?? ''),
                    'address'      => $cliente->direccion ?? '',
                    'neighborhood' => $cliente->barrio ?? '',
                    'municipality' => $cliente->departamento_municipio->nombre ?? '',
                    'department'   => $cliente->departamento_municipio->departamento->nombre ?? '',
                ],
                'credits' => $creditsData,
            ]
        ]);
    }



    // ─── POST /api/mobile/mobile_create_credit ────────────────────────────────
    public function crearSolicitud(Request $request)
    {
        $agente = $request->user();

        // Validaciones de datos mínimos necesarios
        if (!$request->clientId || !$request->amount || !$request->interestRate || !$request->termMonths || !$request->firstPaymentDate) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan campos obligatorios para procesar la solicitud.',
            ], 400);
        }

        // 1. Mapear frecuencia de pago al ID que espera el controlador de la web
        $paymentFrequency = $request->paymentFrequency ?? 'Semanal';
        $formaPagoId = "2"; // Semanal por defecto
        if (strcasecmp($paymentFrequency, 'Diario') === 0) {
            $formaPagoId = "1";
        } elseif (strcasecmp($paymentFrequency, 'Semanal') === 0) {
            $formaPagoId = "2";
        } elseif (strcasecmp($paymentFrequency, 'Quincenal') === 0) {
            $formaPagoId = "3";
        } elseif (strcasecmp($paymentFrequency, 'Catorcenal') === 0) {
            $formaPagoId = "7";
        }

        $amount = (float)$request->amount;
        $interestRate = (float)$request->interestRate;
        $termMonths = (float)$request->termMonths;

        // 2. Determinar número de cuotas según la frecuencia y el plazo en meses
        $numeroCuotas = 0;
        switch ($formaPagoId) {
            case "1": // Diario
                $numeroCuotas = $termMonths * 20;
                break;
            case "2": // Semanal
                $numeroCuotas = $termMonths * 4;
                break;
            case "3": // Quincenal
                $numeroCuotas = $termMonths * 2;
                break;
            case "7": // Catorcenal
                $numeroCuotas = $termMonths * 2;
                break;
            default:
                $numeroCuotas = $termMonths * 4; // Mensual por defecto si fallara
                break;
        }

        if ($numeroCuotas <= 0) {
            $numeroCuotas = 1;
        }

        // 3. Cálculos matemáticos financieros idénticos a los del simulador / formulario web
        $interesMes = $amount * ($interestRate / 100);
        $totalIntereses = $interesMes * $termMonths;
        $montoTotalFinanciar = $amount + $totalIntereses;

        $montoCuota = round($montoTotalFinanciar / $numeroCuotas, 2);
        $interesPagar = round($totalIntereses / $numeroCuotas, 2);

        // 4. Determinar días/fechas y días preferidos
        $firstPaymentCarbon = \Carbon\Carbon::parse($request->firstPaymentDate);
        
        $diaSemanaPreferido = $request->diaSemanaPreferido ?? null;
        if (in_array($formaPagoId, ["2", "7"]) && empty($diaSemanaPreferido)) {
            $diaSemanaPreferido = $firstPaymentCarbon->dayOfWeek; // 0=Dom, 1=Lun, etc.
        }

        // 5. Crear una emulación del Request de Laravel para el controlador web
        $webRequest = new Request();
        $webRequest->merge([
            'cliente'             => encode($request->clientId),
            'agente'              => encode($agente->id),
            'vendedor'            => encode($agente->id),
            'fiador'              => null,
            'negocio'             => null,
            'fechaPrestamo'       => \Carbon\Carbon::now()->toDateString(),
            // CRÍTICO: 'desembolso' no debe ser null para evitar error en decode($request->desembolso)
            'desembolso'          => encode($agente->id), 
            'fechaDesembolso'     => \Carbon\Carbon::now()->toDateString(),
            'moneda'              => 1,
            'montoFinanciar'      => $amount,
            'chkSolicitud'        => true, // Marcador crítico para indicar que es una solicitud
            'montoTotalFinanciar' => $montoTotalFinanciar,
            'formaPago'           => $formaPagoId,
            'plazoPago'           => $termMonths,
            'fechaPago'           => $firstPaymentCarbon->toDateString(),
            'montoCuota'          => $montoCuota,
            'tasaInteres'         => $interestRate,
            'interesPagar'        => $interesPagar,
            'interesMes'          => $interesMes,
            'totalIntereses'      => $totalIntereses,
            'comentarios'         => $request->comentarios ?? 'Solicitud creada desde el APK móvil',
            'dias_mora'           => 1,
            'moraTipo'            => 1,
            'monto_mora'          => 0.00,
            'tipo_prestamo'       => $request->tipoPrestamo ?? '1', // 1: Nuevo, 2: Represtamo, etc.
            'tipo_destino'        => $request->tipoDestino ?? '2',  // 1: Comercio, 2: Consumo, etc.
            'diaSemanaPreferido'  => $diaSemanaPreferido,
            'dia_pago_preferido'  => $request->diaPagoPreferido ?? null,
            'diasPago'            => null,
        ]);

        try {
            // 6. Invocar al método store del prestamosController usando el contenedor
            $webController = app(\App\Http\Controllers\prestamosController::class);
            $webResponse = $webController->store($webRequest);

            // Analizar la respuesta del controlador
            $responseContent = json_decode($webResponse->getContent(), true);

            if (isset($responseContent['type']) && $responseContent['type'] === 'success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud de crédito enviada y registrada con éxito en el sistema.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $responseContent['message'] ?? 'Error al procesar la solicitud en el servidor.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al guardar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── POST /api/mobile/crear-cliente ─────────────────────────────────────────
    public function crearCliente(Request $request)
    {
        $agente = $request->user();

        $request->validate([
            'nombres'   => 'required|string',
            'apellidos' => 'required|string',
            'cedula'    => 'required|string|unique:users,cedula',
            'telefono1' => 'numeric|nullable',
            'telefono2' => 'numeric|nullable',
            'direccion' => 'string|nullable',
            'sexo'      => 'integer|nullable',
            'estado_civil' => 'integer|nullable',
            'dep_mun'   => 'string|nullable',
        ], [
            'cedula.unique' => 'La cédula ya se encuentra ingresada',
        ]);

        try {
            $user = new User();
            $user->nombres = $request->nombres;
            $user->apellidos = $request->apellidos;
            $user->cedula = $request->cedula;
            $user->telefono1 = $request->telefono1;
            $user->telefono2 = $request->telefono2;
            $user->direccion = $request->direccion;
            $user->tipo_usuario = 3; // Cliente
            $user->password = \Hash::make('test2023');
            $user->email = \Str::random('10') . "@gmail.com";
            $user->created_user_id = $agente->id;
            
            $user->sexo = $request->has('sexo') ? $request->sexo : null;
            $user->estado_civil = $request->has('estado_civil') ? $request->estado_civil : null;
            
            if ($request->filled('dep_mun')) {
                $user->dep_mun = decode($request->dep_mun);
            } else {
                $user->dep_mun = $agente->dep_mun;
            }
            
            $user->sucursal_id = $agente->sucursal_id;
            $user->estado = 1;

            if ($user->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente creado exitosamente',
                    'data'    => [
                        'id'        => $user->id,
                        'full_name' => $user->full_name,
                        'cedula'    => $user->cedula,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el cliente en la base de datos'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDepartamentoMunicipios()
    {
        try {
            $raw_deps = departamento_municipios();
            $formatted = [];
            
            foreach ($raw_deps as $dep_name => $municipios) {
                $muns_list = [];
                foreach ($municipios as $id_enc => $mun_name) {
                    $muns_list[] = [
                        'id_enc' => $id_enc,
                        'nombre' => $mun_name
                    ];
                }
                
                // Ordenar municipios por nombre
                usort($muns_list, function($a, $b) {
                    return strcmp($a['nombre'], $b['nombre']);
                });

                $formatted[] = [
                    'departamento' => $dep_name,
                    'municipios' => $muns_list
                ];
            }

            // Ordenar departamentos por nombre
            usort($formatted, function($a, $b) {
                return strcmp($a['departamento'], $b['departamento']);
            });

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener departamento-municipios: ' . $e->getMessage()
            ], 500);
        }
    }

    // ─── ENDPOINTS ADMINISTRATIVOS / GERENCIA ─────────────────────────────────

    public function requests(Request $request)
    {
        try {
            $user = $request->user() ?? \App\Models\User::find($request->get('userId'));
            $agentesAsignados = $user ? \App\Models\userAsignadoModel::where('user_id', $user->id)
                ->get()->pluck('admin_asignado_id')->toArray() : [];
            $tieneAsignados = count($agentesAsignados) > 0;

            $prestamos = \App\Models\prestamosModel::with(['cliente.departamento_municipio.departamento', 'negocio.departamento_municipio.departamento', 'userCreado'])
                ->where('estado', '!=', 4)
                ->where(function($q) {
                    $q->where(function($sub) {
                        $sub->where('estado_aprobacion', 1)
                            ->where(function($d) {
                                $d->where('desembolsado', 0)->orWhereNull('desembolsado');
                            });
                    })
                    ->orWhere(function($sub) {
                        $sub->where('estado_aprobacion', 3)
                            ->whereDate('updated_at', \Carbon\Carbon::today());
                    });
                })
                ->when($tieneAsignados, function ($query) use ($agentesAsignados) {
                    $query->whereIn('agente_id', $agentesAsignados);
                })
                ->orderBy('id', 'desc')
                ->get();

            $formatted = [];
            foreach ($prestamos as $p) {
                $isPending = ($p->estado_aprobacion == 1);
                $isRejected = ($p->estado_aprobacion == 3);

                if (!$isPending && !$isRejected) {
                    continue;
                }

                $saldoPendiente = 0;
                if ($p->user_id) {
                    $otrosPrestamos = \App\Models\prestamosModel::where('user_id', $p->user_id)
                        ->where('estado', 1)
                        ->where('desembolsado', 1)
                        ->where('id', '!=', $p->id)
                        ->get();
                    $saldoPendiente = $otrosPrestamos->sum(function($item) {
                        return (float)($item->pendiente_abono ?? 0);
                    });
                }

                $montoPrestamo = (float)($p->monto_prestamo ?? $p->monto ?? 0);
                $netDisbursement = max(0, $montoPrestamo - $saldoPendiente);

                $dir = $p->cliente ? ($p->cliente->direccion ?? '') : '';
                if (empty($dir) && $p->negocio) {
                    $dir = $p->negocio->direccion ?? '';
                }

                $mun = $p->cliente && $p->cliente->departamento_municipio ? $p->cliente->departamento_municipio->nombre : '';
                if (empty($mun) && $p->negocio && $p->negocio->departamento_municipio) {
                    $mun = $p->negocio->departamento_municipio->nombre ?? '';
                }

                $dep = $p->cliente && $p->cliente->departamento_municipio && $p->cliente->departamento_municipio->departamento ? $p->cliente->departamento_municipio->departamento->nombre : '';
                if (empty($dep) && $p->negocio && $p->negocio->departamento_municipio && $p->negocio->departamento_municipio->departamento) {
                    $dep = $p->negocio->departamento_municipio->departamento->nombre ?? '';
                }

                $barrio = $p->cliente ? ($p->cliente->barrio ?? '') : '';

                $rejectedBy = 'N/A';
                if ($p->updated_user_id) {
                    $uReject = \App\Models\User::find($p->updated_user_id);
                    if ($uReject) {
                        $rejectedBy = $uReject->full_name ?? trim($uReject->nombres . ' ' . $uReject->apellidos);
                    }
                }

                $formatted[] = [
                    'id' => $p->id,
                    'clientName' => $p->cliente ? ($p->cliente->full_name ?? trim($p->cliente->nombres . ' ' . $p->cliente->apellidos)) : 'N/A',
                    'creditNumber' => $p->consecutivo ?? (string)$p->id,
                    'status' => $isPending ? 'Pending' : 'Rejected',
                    'amount' => $montoPrestamo,
                    'totalAmount' => (float)($p->monto_financiado ?? 0),
                    'installmentAmount' => (float)($p->monto_cuota ?? 0),
                    'outstandingBalance' => (float)$saldoPendiente,
                    'netDisbursementAmount' => (float)$netDisbursement,
                    'termMonths' => (int)($p->plazo_pago ?? 0),
                    'paymentFrequency' => $p->forma_pago ?? 'N/A',
                    'interestRate' => (float)($p->tasa_prestamo ?? 0),
                    'firstPaymentDate' => $p->fecha_primer_pago ?? null,
                    'department' => $dep,
                    'municipality' => $mun,
                    'address' => $dir,
                    'neighborhood' => $barrio,
                    'collectionsManager' => $p->userCreado ? ($p->userCreado->full_name ?? $p->userCreado->nombres) : 'N/A',
                    'applicationDate' => $p->created_at ? $p->created_at->toIso8601String() : date('c'),
                    'rejectionReason' => $p->comentarios_rechazado ?? '',
                    'rejectedBy' => $rejectedBy
                ];
            }

            return response()->json([
                'success' => true,
                'requests' => $formatted
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar solicitudes: ' . $e->getMessage(),
                'requests' => []
            ], 500);
        }
    }

    public function approveCredit(Request $request)
    {
        $request->validate([
            'creditId' => 'required',
        ]);

        try {
            $prestamoId = $request->creditId;
            $user = $request->user() ?? \App\Models\User::find($request->get('userId'));

            $prestamo = \App\Models\prestamosModel::find($prestamoId);

            if (!$prestamo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la solicitud.'
                ], 404);
            }

            $prestamo->estado_aprobacion = 2; // Aprobado
            $prestamo->updated_user_id = $user ? $user->id : null;
            $prestamo->save();

            if ($prestamo->cliente && $prestamo->cliente->tipo_usuario == 6) {
                $prestamo->cliente->tipo_usuario = 3;
                $prestamo->cliente->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectCredit(Request $request)
    {
        $request->validate([
            'creditId' => 'required',
            'reason' => 'required|string',
        ]);

        try {
            $prestamoId = $request->creditId;
            $user = $request->user() ?? \App\Models\User::find($request->get('userId'));

            $prestamo = \App\Models\prestamosModel::find($prestamoId);

            if (!$prestamo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la solicitud.'
                ], 404);
            }

            $prestamo->estado_aprobacion = 3; // Rechazado
            $prestamo->comentarios_rechazado = $request->reason;
            $prestamo->updated_user_id = $user ? $user->id : null;
            $prestamo->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function disbursements(Request $request)
    {
        try {
            $user = $request->user() ?? \App\Models\User::find($request->get('userId'));
            $agentesAsignados = $user ? \App\Models\userAsignadoModel::where('user_id', $user->id)
                ->get()->pluck('admin_asignado_id')->toArray() : [];
            $tieneAsignados = count($agentesAsignados) > 0;

            $prestamos = \App\Models\prestamosModel::with(['cliente.departamento_municipio.departamento', 'negocio.departamento_municipio.departamento', 'userCreado', 'cuotas'])
                ->where('estado', '!=', 4)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('estado_aprobacion', 2)
                            ->where(function($d) {
                                $d->where('desembolsado', 0)->orWhereNull('desembolsado');
                            });
                    })
                    ->orWhere(function ($sub) {
                        $sub->where('desembolsado', 1)
                            ->whereDate('fecha_desembolso', \Carbon\Carbon::today());
                    })
                    ->orWhere(function ($sub) {
                        $sub->where('estado_aprobacion', 3)
                            ->whereDate('updated_at', \Carbon\Carbon::today());
                    });
                })
                ->when($tieneAsignados, function ($query) use ($agentesAsignados) {
                    $query->whereIn('agente_id', $agentesAsignados);
                })
                ->orderBy('id', 'desc')
                ->get();

            $formatted = [];
            foreach ($prestamos as $p) {
                $status = 'Approved';
                if ($p->desembolsado == 1) {
                    $status = 'Active';
                } elseif ($p->estado_aprobacion == 3) {
                    $status = 'Rejected';
                }

                $saldoPendiente = 0;
                if ($p->user_id) {
                    $otrosPrestamos = \App\Models\prestamosModel::where('user_id', $p->user_id)
                        ->where('estado', 1)
                        ->where('desembolsado', 1)
                        ->where('id', '!=', $p->id)
                        ->get();
                    $saldoPendiente = $otrosPrestamos->sum(function($item) {
                        return (float)($item->pendiente_abono ?? 0);
                    });
                }

                $montoPrestamo = (float)($p->monto_prestamo ?? $p->monto ?? 0);
                $netDisbursement = max(0, $montoPrestamo - $saldoPendiente);

                $dir = $p->cliente ? ($p->cliente->direccion ?? '') : '';
                if (empty($dir) && $p->negocio) {
                    $dir = $p->negocio->direccion ?? '';
                }

                $mun = $p->cliente && $p->cliente->departamento_municipio ? $p->cliente->departamento_municipio->nombre : '';
                if (empty($mun) && $p->negocio && $p->negocio->departamento_municipio) {
                    $mun = $p->negocio->departamento_municipio->nombre ?? '';
                }

                $dep = $p->cliente && $p->cliente->departamento_municipio && $p->cliente->departamento_municipio->departamento ? $p->cliente->departamento_municipio->departamento->nombre : '';
                if (empty($dep) && $p->negocio && $p->negocio->departamento_municipio && $p->negocio->departamento_municipio->departamento) {
                    $dep = $p->negocio->departamento_municipio->departamento->nombre ?? '';
                }

                $barrio = $p->cliente ? ($p->cliente->barrio ?? '') : '';

                $rejectedBy = 'N/A';
                if ($p->updated_user_id) {
                    $uReject = \App\Models\User::find($p->updated_user_id);
                    if ($uReject) {
                        $rejectedBy = $uReject->full_name ?? trim($uReject->nombres . ' ' . $uReject->apellidos);
                    }
                }

                $formatted[] = [
                    'id' => $p->id,
                    'clientName' => $p->cliente ? ($p->cliente->full_name ?? trim($p->cliente->nombres . ' ' . $p->cliente->apellidos)) : 'N/A',
                    'creditNumber' => $p->consecutivo ?? (string)$p->id,
                    'status' => $status,
                    'amount' => $montoPrestamo,
                    'totalAmount' => (float)($p->monto_financiado ?? 0),
                    'installmentAmount' => (float)($p->monto_cuota ?? 0),
                    'outstandingBalance' => (float)$saldoPendiente,
                    'netDisbursementAmount' => (float)$netDisbursement,
                    'totalInstallmentAmount' => (float)($p->monto_financiado ?? 0),
                    'termMonths' => (int)($p->plazo_pago ?? 0),
                    'paymentFrequency' => $p->forma_pago ?? 'N/A',
                    'interestRate' => (float)($p->tasa_prestamo ?? 0),
                    'firstPaymentDate' => $p->fecha_primer_pago ?? null,
                    'department' => $dep,
                    'municipality' => $mun,
                    'address' => $dir,
                    'neighborhood' => $barrio,
                    'collectionsManager' => $p->userCreado ? ($p->userCreado->full_name ?? $p->userCreado->nombres) : 'N/A',
                    'applicationDate' => $p->created_at ? $p->created_at->toIso8601String() : date('c'),
                    'disbursementDate' => $p->fecha_desembolso ? $p->fecha_desembolso : null,
                    'rejectionReason' => $p->comentarios_rechazado ?? '',
                    'rejectedBy' => $rejectedBy
                ];
            }

            return response()->json([
                'success' => true,
                'disbursements' => $formatted
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en disbursements: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar desembolsos: ' . $e->getMessage(),
                'disbursements' => []
            ], 500);
        }
    }

    public function disburseCredit(Request $request)
    {
        $request->validate([
            'creditId' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $prestamoId = $request->creditId;
            $user = $request->user() ?? \App\Models\User::find($request->get('userId'));
            $prestamo = \App\Models\prestamosModel::find($prestamoId);

            if (!$prestamo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el préstamo.'
                ], 404);
            }

            if ($prestamo->estado_aprobacion == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Para desembolsar este préstamo primero tiene que aprobar la solicitud.'
                ], 400);
            }

            $prestamo->desembolsado = 1;
            $prestamo->user_desembolso = $user ? $user->id : null;
            $prestamo->fecha_desembolso = date('Y-m-d');
            $prestamo->save();

            // Auto-cancelar crédito(s) anterior(es) activo(s) del cliente si tiene saldo pendiente
            $gestorCobradorId = $prestamo->agente_id ?? ($user ? $user->id : null);
            $prestamosAnteriores = \App\Models\prestamosModel::where('user_id', $prestamo->user_id)
                ->where('estado', 1)
                ->where('desembolsado', 1)
                ->where('id', '!=', $prestamo->id)
                ->get();

            foreach ($prestamosAnteriores as $pAnt) {
                $cuotasPendientes = \App\Models\prestamoCuotasModel::where('prestamo_id', $pAnt->id)
                    ->whereIn('estado', [1, 2])
                    ->orderBy('numero_cuota', 'asc')
                    ->get();

                $totalSaldoPendiente = 0;
                foreach ($cuotasPendientes as $cp) {
                    $totalSaldoPendiente += (float)($cp->monto_pendiente_cuota > 0 ? $cp->monto_pendiente_cuota : $cp->monto_cuota);
                }

                if ($totalSaldoPendiente > 0 && count($cuotasPendientes) > 0) {
                    $abono = new \App\Models\abonosModel();
                    $abono->prestamo_id = $pAnt->id;
                    $abono->fecha_abono = \Carbon\Carbon::now();
                    $abono->tipo_abono = 3; // 3: Cancelación / Représtamo
                    $abono->estado = 1;
                    $abono->anulado_user_id = null;
                    $abono->created_user_id = $gestorCobradorId;
                    $abono->total_efectivo = $totalSaldoPendiente;
                    $abono->total_tarjeta = 0;
                    $abono->total_cheque = 0;
                    $abono->total_transferencia = 0;
                    $abono->referencia_tarjeta = '';
                    $abono->referencia_cheque = '';
                    $abono->referencia_transferencia = 'Cancelación Crédito #' . ($prestamo->consecutivo ?? $prestamo->id);
                    $abono->save();

                    foreach ($cuotasPendientes as $cuota) {
                        $capitalPend = (float)($cuota->total_pendiente_capital_cuota ?? max(0, $cuota->monto_cuota - $cuota->monto_interes));
                        $interesPend = (float)($cuota->total_pendiente_interes_cuota ?? $cuota->monto_interes);
                        $moraPend    = (float)($cuota->total_pendiente_mora_cuota ?? $cuota->monto_mora ?? 0);
                        $montoAbonoCuota = (float)($cuota->monto_pendiente_cuota > 0 ? $cuota->monto_pendiente_cuota : ($capitalPend + $interesPend + $moraPend));

                        $abonoCuota = new \App\Models\prestamoCuotaAbonoModel();
                        $abonoCuota->abono_id = $abono->id;
                        $abonoCuota->prestamo_cuota_id = $cuota->id;
                        $abonoCuota->monto_abono = $montoAbonoCuota;
                        $abonoCuota->fecha_abono = \Carbon\Carbon::now();
                        $abonoCuota->tipo_abono = 1;
                        $abonoCuota->estado = 1;
                        $abonoCuota->total_capital = $capitalPend;
                        $abonoCuota->total_interes = $interesPend;
                        $abonoCuota->total_mora = $moraPend;
                        $abonoCuota->created_user_id = $gestorCobradorId;
                        $abonoCuota->save();

                        $cuota->estado = 3; // Pagada
                        $cuota->fecha_pagado = Carbon::now();
                        $cuota->save();
                    }

                    $pAnt->estado = 2; // Pagado / Cancelado
                    $pAnt->save();
                } else {
                    $pAnt->estado = 2;
                    $pAnt->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Préstamo desembolsado exitosamente y crédito anterior cancelado.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al desembolsar préstamo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function denyDisbursement(Request $request)
    {
        $request->validate([
            'creditId' => 'required',
            'reason' => 'required|string',
        ]);

        try {
            $prestamoId = $request->creditId;
            $user = $request->user() ?? \App\Models\User::find($request->get('userId'));

            $prestamo = \App\Models\prestamosModel::find($prestamoId);

            if (!$prestamo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el préstamo.'
                ], 404);
            }

            $prestamo->estado_aprobacion = 3; // Rechazado / Cancelado
            $prestamo->comentarios_rechazado = $request->reason;
            $prestamo->updated_user_id = $user ? $user->id : null;
            $prestamo->save();

            return response()->json([
                'success' => true,
                'message' => 'Desembolso denegado y préstamo cancelado.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al denegar desembolso: ' . $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $q = trim($request->get('q', $request->get('buscar', $request->get('search', ''))));
        if (strlen($q) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $user = $request->user() ?? \App\Models\User::find($request->get('userId'));
        $agentesAsignados = $user ? \App\Models\userAsignadoModel::where('user_id', $user->id)
            ->get()->pluck('admin_asignado_id')->toArray() : [];
        $tieneAsignados = count($agentesAsignados) > 0;

        $hoy = \Carbon\Carbon::now()->format('Y-m-d');

        $prestamos = \App\Models\prestamosModel::with(['cliente.departamento_municipio.departamento', 'userCreado', 'cuotas' => function($cq) {
                $cq->whereIn('estado', [1, 2])->orderBy('numero_cuota', 'asc');
            }])
            ->where('desembolsado', 1)
            ->where('estado', 1)
            ->where(function($query) use ($q) {
                $query->where('consecutivo', 'like', '%' . $q . '%')
                    ->orWhere('id', $q)
                    ->orWhereHas('cliente', function($cq) use ($q) {
                        $cq->where('nombres', 'like', '%' . $q . '%')
                           ->orWhere('apellidos', 'like', '%' . $q . '%')
                           ->orWhere('cedula', 'like', '%' . $q . '%')
                           ->orWhere('telefono1', 'like', '%' . $q . '%');
                    });
            })
            ->when($tieneAsignados, function ($query) use ($agentesAsignados) {
                $query->whereIn('agente_id', $agentesAsignados);
            })
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $results = [];
        foreach ($prestamos as $p) {
            $cliente = $p->cliente;
            $cuotasPendientes = $p->cuotas;

            $cuotasHoy = $cuotasPendientes->filter(function($c) use ($hoy) {
                return $c->fecha_cuota === $hoy;
            });
            $cuotasVencidas = $cuotasPendientes->filter(function($c) use ($hoy) {
                return $c->fecha_cuota < $hoy;
            });

            $dueTodayAmount = $cuotasHoy->sum(function($c) {
                return (float)($c->monto_pendiente_cuota ?? $c->monto_cuota ?? 0);
            });

            $overdueAmount = $cuotasVencidas->sum(function($c) {
                return (float)($c->monto_pendiente_cuota ?? $c->monto_cuota ?? 0);
            });

            $results[] = [
                'id'                 => $p->id,
                'id_enc'             => $p->id_enc,
                'clientId'           => $cliente ? $cliente->id : null,
                'clientName'         => $cliente ? ($cliente->full_name ?? trim($cliente->nombres . ' ' . $cliente->apellidos)) : 'N/A',
                'clientCode'         => $cliente ? ($cliente->cedula ?? (string)$cliente->id) : '',
                'creditNumber'       => $p->consecutivo ?? (string)$p->id,
                'collectionsManager' => $p->userCreado ? ($p->userCreado->full_name ?? $p->userCreado->nombres) : 'N/A',
                'remainingBalance'   => (float)$p->pendiente_abono,
                'dueTodayAmount'     => (float)$dueTodayAmount,
                'overdueAmount'      => (float)$overdueAmount,
                'status'             => 'Activo',
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }
}
