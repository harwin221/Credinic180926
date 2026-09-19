<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class solicitudPrestamoModel extends Model
{
    use HasFactory;
    protected $table = "solicitud_prestamo";

    protected $fillable = [
        'user_id',
        'moneda',
        'monto_solicitado',
        'plazo_solicitado',
        'forma_pago_solicitada',
        'tasa_propuesta',
        'fecha_primer_pago',
        'observaciones',
    ];
    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    public function getEstadoSolicitudAttribute()
    {
        if ($this->estado == 1)
            return "Pendiente";
        elseif ($this->estado == 2)
            return "Rechazado";
        elseif ($this->estado == 3)
            return "Aprobado";
    }

    public function getMonedaSolicitudAttribute()
    {
        if ($this->moneda == 1)
            return "C$";
        else
            return "U$";
    }

    public function getFormaPagoAttribute()
    {
        $formas = ['1' => 'Diario', '2' => 'Semanal', '3' => 'Quincenal', '4' => 'Mensual', '5' => 'Anual'];
        return $formas[$this->forma_pago_solicitada];
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
       public function creado()
    {
        return $this->hasOne(User::class,'id','created_user_id');
    }
}
