<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Matrix\Builder;
use function Symfony\Component\String\s;

class prestamosModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prestamos';

    protected $fillable = [
        'deleted_user_id'
    ];

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    public function getMonedaAttribute()
    {
        return $this->moneda_prestamo == 1 ? 'C$' : 'U$';
    }

    public function getEstadoPrestamoAttribute()
    {
        $estado = "";
        switch ($this->estado) {
            case 1:
                $estado = "Activo";
                break;
            case 2:
                $estado = "Cancelado";
                break;
            case 3:
                $estado = "Vencido";
                break;
            case 4:
                $estado = "Anulado";
        }
        return $estado;
    }

    public function getEstadoAprobacionAtAttribute()
    {
        $estado = "";
        switch ($this->estado_aprobacion) {
            case 1:
                $estado = "Pendiente";
                break;
            case 3:
                $estado = "Rechazado";
                break;
            case 2:
                $estado = "Aprobado";
                break;
        }
        return $estado;
    }

    public function getTipoPrestamoAttribute()
    {
        if (is_null($this->tipo_desembolso))
            return 'N-E';
        if ($this->tipo_desembolso == 1)
            return 'Nuevo';
        if ($this->tipo_desembolso == 2)
            return 'Represtamo';
        if ($this->tipo_desembolso == 3)
            return 'Reactivación';
        if ($this->tipo_desembolso == 4)
            return 'Reestructuración';
    }


    public function getTipoPrestamoAbrevAttribute()
    {
        if ($this->tipo_desembolso == 1)
            return 'N';
        if ($this->tipo_desembolso == 2)
            return 'R';
        if ($this->tipo_desembolso == 3)
            return 'REAC.';
        if ($this->tipo_desembolso == 4)
            return 'REEST.';
    }

    public function getFormaPagoAttribute()
    {
        $formas = ['1' => 'Diario', '2' => 'Semanal', '3' => 'Quincenal', '4' => 'Mensual', '5' => 'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'];
        return $formas[$this->forma_pago_tipo];
    }

    public function getSumaCuotasAttribute()
    {
        return $this->cuotas()
            ->where('estado', '!=', 4)//no tomar en cuenta anuladas
            ->sum('monto_cuota');
    }

    public function getSumaPendienteCuotasVencidasAttribute()
    {
        $totalVencido = $this->cuotas()
            ->whereDate('fecha_cuota', '<=', date('Y-m-d'))
            ->where('estado', '!=', 3)//no tomar en cuenta pagadas
            ->sum('monto_cuota');

        $totalAbonadoVencido = $this->cuotas()->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'prestamo_coutas.id')
            ->whereDate('prestamo_coutas.fecha_cuota', '<=', date('Y-m-d'))
            ->where('prestamo_coutas.estado', '!=', 3)//no tomar en cuenta pagadas
            ->where('PCA.estado', '!=', 2)
            ->sum('PCA.monto_abono');

        return ($totalVencido - $totalAbonadoVencido);
    }

    public function getSumaPendienteCuotasAdelantadasAttribute()
    {
        $totalCuotasNoVencidas = $this->cuotas()
            ->whereExists(function ($query) {
                $query->select('id')
                    ->from('prestamo_cuota_abono')
                    ->whereColumn('prestamo_coutas.id', 'prestamo_cuota_abono.prestamo_cuota_id');
            })
            ->whereDate('fecha_cuota', '>=', date('Y-m-d')) // Cuotas no vencidas
            ->where('estado', '!=', 3) // No tomar cuotas pagadas
            ->sum('monto_cuota');

        $totalAbonadoNoVencidas = $this->cuotas()
            ->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'prestamo_coutas.id') // Relacionar con abonos
            ->whereDate('prestamo_coutas.fecha_cuota', '>=', date('Y-m-d')) // Cuotas no vencidas
            ->where('prestamo_coutas.estado', '!=', 3) // Excluir cuotas pagadas
            ->where('PCA.estado', '!=', 2) // Excluir abonos anulados
            ->selectRaw('SUM(PCA.monto_abono) as total_abono') // Sumar los abonos
            ->groupBy('prestamo_coutas.id') // Agrupar por cuotas
            ->having('total_abono', '>', 0) // Considerar cuotas con abonos
            ->sum('total_abono'); // Sumar los totales de las cuotas

        $sobrante = abs($totalAbonadoNoVencidas - $totalCuotasNoVencidas);
        return $sobrante ? $sobrante : 0;
    }

    public function getSumaInteresAttribute()
    {
        return $this->cuotas()
            ->where('estado', '!=', 4)//no tomar en cuenta anuladas
            ->sum('monto_interes');
    }

    public function getSumaAbonosAttribute()
    {
        $abonos = prestamosModel::join('prestamo_coutas as PC', 'PC.prestamo_id', 'prestamos.id')
            ->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'PC.id')
            ->where('prestamos.id', $this->id)
            ->where('PCA.estado', 1)
            ->select('PCA.*')
            ->sum('PCA.monto_abono');
        return $abonos;
    }

    public function getSumaCapitalAttribute()
    {
        $cuotas = $this->cuotas()
            ->where('estado', '!=', 4)//no tomar en cuenta anuladas
            ->sum('monto_cuota');

        $interes = $this->cuotas()
            ->where('estado', '!=', 4)//no tomar en cuenta anuladas
            ->sum('monto_interes');

        return ($cuotas - $interes);
    }

    function getTipoDestinoPrestamoAttribute()
    {
        if ($this->tipo_destino !== 0) {
            return destinoPrestamo()[$this->tipo_destino];
        }
        else
            return 'N/D';
    }

    public function getPendienteAbonoAttribute()
    {
        return $this->suma_cuotas - $this->suma_abonos;
    }

    public function getCuotasVencidasAttribute()
    {
        $fechaActual = now();
        $cuotasVencidas = $this->cuotas->where('estado', '!=', 3)->filter(function ($cuota) use ($fechaActual) {
            $fechaCuota = Carbon::parse($cuota->fecha_cuota);
            $fechaMora = $fechaActual->addDays($this->dias_aplicar_mora);
            return $fechaCuota->lt($fechaMora);//menor que
        });

        return $cuotasVencidas;
    }

    public function getTotalPendienteCapitalAttribute()
    {
        $totalPendiente = $this->cuotas()->sum('monto_cuota');
        $totalInteresPendiente = $this->cuotas()->sum('monto_interes');
        $capitalPendiente = $totalPendiente - $totalInteresPendiente;

        $abonosCapital = prestamoCuotaAbonoModel::whereIn('prestamo_cuota_id',$this->cuotas()->pluck('id'))
            ->join('abonos as A', 'A.id', 'prestamo_cuota_abono.abono_id')
            ->where('A.estado', 1)
            ->sum('total_capital');

        $total = $capitalPendiente - $abonosCapital;

        return $total;
    }

    public function getTotalPendienteCapitalFecha($fechaCorte)
    {
        $totalPendiente = $this->cuotas()->where('fecha_cuota','<=',$fechaCorte)->sum('monto_cuota');
        $totalInteresPendiente = $this->cuotas()->where('fecha_cuota','<=',$fechaCorte)->sum('monto_interes');
        $capitalPendiente = $totalPendiente - $totalInteresPendiente;

        $abonosCapital = prestamoCuotaAbonoModel::whereIn('prestamo_cuota_id',$this->cuotas()->where('fecha_cuota','<=',$fechaCorte)->pluck('id'))
            ->join('abonos as A', 'A.id', 'prestamo_cuota_abono.abono_id')
            ->where('A.estado', 1)
            ->sum('total_capital');

        $total = $capitalPendiente - $abonosCapital;
        return $total;
    }


    public function getTotalPendienteInteresAttribute()
    {
        $totalInteresPendiente = $this->cuotas()->sum('monto_interes');

        $abonosInteres = prestamoCuotaAbonoModel::whereIn('prestamo_cuota_id',$this->cuotas()->pluck('id'))
            ->join('abonos as A','A.id','prestamo_cuota_abono.abono_id')
            ->where('A.estado',1)
            ->sum('total_interes');

        $total = $totalInteresPendiente - $abonosInteres;

        return $total;
    }

    public function getTotalPendienteInteresFecha($fechaCorte)
    {
        $totalInteresPendiente = $this->cuotas()->where('fecha_cuota','<=',$fechaCorte)->sum('monto_interes');

        $abonosInteres = prestamoCuotaAbonoModel::whereIn('prestamo_cuota_id',$this->cuotas()->where('fecha_cuota','<=',$fechaCorte)->pluck('id'))
            ->join('abonos as A','A.id','prestamo_cuota_abono.abono_id')
            ->where('A.estado',1)
            ->sum('total_interes');

        $total = $totalInteresPendiente - $abonosInteres;

        return $total;
    }


    public function cliente()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function agente()
    {
        return $this->hasOne(User::class, 'id', 'agente_id');
    }

    public function vendedor()
    {
        return $this->hasOne(User::class, 'id', 'vendedor_id');
    }

    public function fiador()
    {
        return $this->hasOne(User::class, 'id', 'fiador_id');
    }

    public function userDesembolso()
    {
        return $this->hasOne(User::class, 'id', 'user_desembolso');
    }

    public function userCreado()
    {
        return $this->hasOne(User::class, 'id', 'created_user_id');
    }

    public function userAnulado()
    {
        return $this->hasOne(User::class, 'id', 'anulado_user_id');
    }

    public function negocio()
    {
        return $this->hasOne(userNegociosModel::class, 'id', 'negocio_id');
    }

    public function cuotas()
    {
        return $this->hasMany(prestamoCuotasModel::class, 'prestamo_id', 'id');
    }

    public function abonos()
    {
        return $this->hasMany(abonosModel::class, 'prestamo_id', 'id');
    }

    /**
     * Promedio de días de atraso — misma lógica que prestamosVer2 y estado de cuenta.
     * Reutilizable desde cualquier vista con $prestamo->promedio_dias_atraso
     */
    public function getPromedioDiasAtrasoAttribute(): float
    {
        $totalDias   = 0;
        $totalCuotas = $this->cuotas->count();

        foreach ($this->cuotas as $cuota) {
            $fechaPlan = \Carbon\Carbon::parse($cuota->fecha_cuota);

            if ($cuota->estado == 3) {
                // Cuota pagada: días entre fecha planeada y fecha real del abono
                $ultimoDetalle = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuota->id)
                    ->where('estado', 1)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($ultimoDetalle) {
                    $dias = $fechaPlan->diffInDays(\Carbon\Carbon::parse($ultimoDetalle->created_at), false);
                    if ($dias > 0) $totalDias += $dias;
                }
            } elseif (in_array($cuota->estado, [1, 2]) && $fechaPlan->isPast()) {
                // Cuota pendiente vencida: días acumulados hasta hoy
                $dias = $fechaPlan->diffInDays(\Carbon\Carbon::now(), false);
                if ($dias > 0) $totalDias += $dias;
            }
        }

        return $totalCuotas > 0 ? round($totalDias / $totalCuotas, 2) : 0;
    }

    public function getDiasAtrasoAttribute()
    {
        if (in_array($this->estado, [2, 4])) {
            return [
                'dias_atraso' => 0,
                'letra' => '-',
                'color' => '-',
            ];
        }

        $color = ['A' => 'rgba(88,139,216,0.47)', 'B' => '#96d858', 'C' => '#d8d358', 'D' => '#d3a065', 'E' => '#d36565'];
        // Optimize query to avoid N+1 problem
        $cuotasPendientes = $this->cuotas
            ->where('estado', '!=', 3)
            ->filter(function ($cuota) {
                return Carbon::parse($cuota->fecha_cuota)->lt(Carbon::now());
            });

        $primeraCuotaPendiente = $cuotasPendientes->sortBy('fecha_cuota')->first();
        $diasAtraso = 0;
        $letra = '';

        if ($primeraCuotaPendiente) {
            $diasAtraso = Carbon::createFromFormat('Y-m-d', $primeraCuotaPendiente->fecha_cuota)->diffInDays(Carbon::now());
        }

        if ($diasAtraso <= 15) {
            $letra = 'A';
        } elseif ($diasAtraso > 15 && $diasAtraso <= 30) {
            $letra = 'B';
        } elseif ($diasAtraso > 30 && $diasAtraso <= 60) {
            $letra = 'C';
        } elseif ($diasAtraso > 60 && $diasAtraso <= 90) {
            $letra = 'D';
        } elseif ($diasAtraso > 90) {
            $letra = 'E';
        }

        return [
            'dias_atraso' => $diasAtraso,
            'letra' => $letra,
            'color' => $color[$letra] ?? '-'
        ];
    }

    public function clasificadoPor()
    {
        return $this->hasOne(User::class,'id','clasificado_por');
    }
}
