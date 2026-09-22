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
    // ─── GET /api/mobile/dashboard (Recaudo del Día - Misma lógica exacta que la web) ───────
    public function dashboard(Request $request)
    {
        $agente = $request->user();
        $hoy = \Carbon\Carbon::today()->toDateString();

        // Abonos del agente logueado registrados HOY
        $abonosHoy = DB::table('abonos as a')
            ->join('prestamos as p', 'p.id', '=', 'a.prestamo_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            // Totales del abono (capital + interes + mora + total)
            ->leftJoin(DB::raw('(SELECT abono_id,
                                    SUM(total_capital) as capital,
                                    SUM(total_interes) as interes,
                                    SUM(total_mora)    as mora,
                                    SUM(monto_abono)   as total
                                FROM prestamo_cuota_abono
                                WHERE estado = 1
                                GROUP BY abono_id) as t_pca'), 'a.id', '=', 't_pca.abono_id')
            // Fecha de la cuota más antigua aplicada -> clasifica el tipo de cobro
            ->leftJoin(DB::raw('(SELECT pca.abono_id, MIN(pc.fecha_cuota) as fecha_cuota_min
                                FROM prestamo_cuota_abono pca
                                JOIN prestamo_coutas pc ON pc.id = pca.prestamo_cuota_id
                                WHERE pca.estado = 1
                                GROUP BY pca.abono_id) as t_fc'), 'a.id', '=', 't_fc.abono_id')
            // Detectar préstamos vencidos: sin cuotas futuras pendientes Y con saldo pendiente
            ->leftJoin(DB::raw('(SELECT prestamo_id,
                                    COUNT(*) as cuotas_futuras_pend
                                FROM prestamo_coutas
                                WHERE fecha_cuota >= CURDATE()
                                  AND estado IN (1,2)
                                GROUP BY prestamo_id) as t_fut'), 'p.id', '=', 't_fut.prestamo_id')
            ->leftJoin(DB::raw('(SELECT pc2.prestamo_id,
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
                DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as cliente_nombre"),
                't_pca.capital  as total_abonado_capital',
                't_pca.interes  as total_abonado_interes',
                't_pca.mora     as total_abonado_mora',
                't_pca.total    as total_abonado',
                't_fc.fecha_cuota_min',
                DB::raw('COALESCE(t_fut.cuotas_futuras_pend, 0) as cuotas_futuras_pend'),
                DB::raw('COALESCE(t_saldo.total_cuotas, 0) - COALESCE(t_saldo.total_abonado_prest, 0) as saldo_pendiente_prest')
            )
            ->get();

        // Clasificación idéntica a la vista web de recaudo
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
        $detallesPorAbono = DB::table('prestamo_cuota_abono as pca')
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


    // ─── POST /api/mobile/recibo ──────────────────────────────────────────────
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
        ];
    }
}
