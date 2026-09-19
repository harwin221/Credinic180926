<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class prestamoCuotasModel extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'prestamo_coutas';

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }
    public function abonos()
    {
        return $this->hasMany(prestamoCuotaAbonoModel::class, 'prestamo_cuota_id', 'id');
    }

    public function prestamo()
    {
        return $this->hasOne(prestamosModel::class,'id','prestamo_id');
    }

    public function getPendienteCuotaAttribute()
    {
        return $this->monto_cuota;
    }

    public function getSumaAbonosAttribute()//cuota principal
    {
        return $this->abonos()
            ->whereIn('tipo_abono',[1,3])
            ->where('estado',1)//no tomar en cuenta anulados
            ->sum('monto_abono');//1:monto cuota, 2:interes 3:otro monto 4:mora
    }

    public function getSumaAbonosInteresesAttribute()//_Todo lo que se ha abonado de tipo intereses
    {
        return $this->abonos()
            ->where('estado',1)//no tomar en cuenta anulados
            ->where('tipo_abono',2)
            ->sum('monto_abono');
    }

    public function getTotalPendienteInteresCuotaAttribute()
    {
        $interesAbonado = $this->abonos()
            ->where('estado',1)//no tomar en cuenta anulados
            ->sum('total_interes');
        return $this->monto_interes - $interesAbonado;
    }

    public function getTotalPendienteCapitalCuotaAttribute()
    {
        $capitalAbonado = $this->abonos()
            ->where('estado', 1)//no tomar en cuenta anulados
            ->sum('total_capital');
        return ($this->monto_cuota - $this->monto_interes) - $capitalAbonado;
    }

    public function getTotalPendienteMoraCuotaAttribute()
    {
        $moraAbonada = $this->abonos()
            ->where('estado', 1)//no tomar en cuenta anulados
            ->sum('total_mora');
        return $this->monto_mora - $moraAbonada;
    }

    public function getSumaAbonosMoraAttribute()
    {
        return $this->abonos()
            ->where('estado',1)//no tomar en cuenta anulados
            ->where('tipo_abono',4)
            ->sum('monto_abono');
    }

    public function getMontoPendienteCuotaAttribute()
    {
        return ($this->monto_cuota + $this->monto_mora) - ($this->suma_abonos + $this->suma_abonos_mora);
    }

    public function getMontoTotalCuotaAttribute()
    {
        return $this->monto_cuota + $this->monto_mora;
    }

    public function getEstadoCuotaAttribute()
    {
        if($this->estado==1)
            return 'Pendiente';
        if($this->estado==2)
            return 'Vencida';
        if($this->estado==3)
            return 'Pagada';
        if($this->estado==4)
            return 'Anulada';
    }
}
