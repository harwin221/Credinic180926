<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamoAgenteHistorialModel extends Model
{
    use HasFactory;

    protected $table = 'prestamos_agente_historial';

    protected $fillable = [
        'prestamo_id',
        'agente_anterior_id',
        'agente_nuevo_id',
        'reasignado_por',
        'saldo_al_reasignar',
        'fecha_reasignacion',
        'motivo',
    ];

    protected $casts = [
        'fecha_reasignacion' => 'date',
        'saldo_al_reasignar' => 'decimal:2',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function prestamo()
    {
        return $this->belongsTo(prestamosModel::class, 'prestamo_id');
    }

    public function agenteAnterior()
    {
        return $this->belongsTo(User::class, 'agente_anterior_id');
    }

    public function agenteNuevo()
    {
        return $this->belongsTo(User::class, 'agente_nuevo_id');
    }

    public function reasignadoPor()
    {
        return $this->belongsTo(User::class, 'reasignado_por');
    }

    // ─── Helper estático ──────────────────────────────────────────────────────

    /**
     * Registra una reasignación antes de cambiar el agente_id en prestamos.
     * Calcula el saldo pendiente en ese momento usando la misma lógica
     * que usa el sistema en los demás reportes.
     */
    public static function registrar(prestamosModel $prestamo, int $agenteNuevoId, int $reasignadoPorId, ?string $motivo = null): self
    {
        // Calcular saldo pendiente actual del préstamo
        $totalCuotas = \DB::table('prestamo_coutas')
            ->where('prestamo_id', $prestamo->id)
            ->where('estado', '!=', 4)
            ->sum('monto_cuota');

        $totalAbonado = \DB::table('prestamo_cuota_abono as pca')
            ->join('prestamo_coutas as pc', 'pc.id', '=', 'pca.prestamo_cuota_id')
            ->where('pc.prestamo_id', $prestamo->id)
            ->where('pca.estado', 1)
            ->sum('pca.monto_abono');

        $saldoPendiente = max(0, $totalCuotas - $totalAbonado);

        return self::create([
            'prestamo_id'        => $prestamo->id,
            'agente_anterior_id' => $prestamo->agente_id,
            'agente_nuevo_id'    => $agenteNuevoId,
            'reasignado_por'     => $reasignadoPorId,
            'saldo_al_reasignar' => $saldoPendiente,
            'fecha_reasignacion' => now()->toDateString(),
            'motivo'             => $motivo,
        ]);
    }
}
