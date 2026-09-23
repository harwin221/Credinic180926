<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class abonosModel extends Model
{
    use HasFactory;
    protected $table = "abonos";

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    public function getTipoAttribute()
    {
        if ($this->tipo_abono == 0)
            return 'Ordinario';
        if ($this->tipo_abono == 1)
            return 'Deducción';
        if ($this->tipo_abono == 2)
            return 'Dispensa';
        if ($this->tipo_abono == 3)
            return 'Cancelación';
        return 'Ordinario';
    }

    public function prestamo()
    {
        return $this->hasOne(prestamosModel::class, 'id', 'prestamo_id');
    }

    public function abono_detalle()
    {
        return $this->hasMany(prestamoCuotaAbonoModel::class,'abono_id','id');
    }


    public function user_create()
    {
        return $this->hasOne(User::class,'id','created_user_id');
    }

    public function getTotalAbonadoAttribute()
    {
        return $this->abono_detalle()->where('estado',1)->sum('monto_abono');
    }

    public function getTotalAbonadoCapitalAttribute()
    {
        return $this->abono_detalle()->where('estado', 1)->sum('total_capital');
    }

    public function getTotalAbonadoInteresAttribute()
    {
        return $this->abono_detalle()->where('estado', 1)->sum('total_interes');
    }

    public function getTotalAbonadoMoraAttribute()
    {
        return $this->abono_detalle()->where('estado', 1)->sum('total_mora');
    }

    public function getEstadoAbonoAttribute()
    {
        if($this->estado == 1)
            return 'Activo';
        elseif($this->estado == 2)
            return 'Anulado';
    }

    public function userAnulado()
    {
        return $this->hasOne(User::class,'id','anulado_user_id');
    }
}
