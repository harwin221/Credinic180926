<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class prestamoCuotaAbonoModel extends Model
{
    use HasFactory, SoftDeletes;
    protected $appends = [
      'abono_tipo'
    ];

    protected $table = 'prestamo_cuota_abono';

    public function prestamo_cuota()
    {
        return $this->hasOne(prestamoCuotasModel::class, 'id', 'prestamo_cuota_id');
    }

    public function created_user()
    {
        return $this->hasOne(User::class, 'id', 'created_user_id');
    }

    public function abono()
    {
        return $this->hasOne(abonosModel::class,'id','abono_id');
    }

    public function getAbonoTipoAttribute()
    {
        if($this->tipo_abono == 1)
            return 'Pago de Cuota';
        elseif($this->tipo_abono == 2)
            return 'Abono a Interes';
        elseif($this->tipo_abono == 3)
            return 'Abono a Cuota';
        elseif($this->tipo_abono == 4)
            return 'Mora';
    }
}
