<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class sucursalModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'estado',
        'created_user_id',
    ];

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    public function getEstadoSucursalAttribute()
    {
        return $this->estado == 1 ? 'Activa' : 'Inactiva';
    }

    // Admins asignados a esta sucursal
    public function admins()
    {
        return $this->hasMany(User::class, 'sucursal_id', 'id')
                    ->where('tipo_usuario', 2);
    }

    // Scope solo activas
    public function scopeActiva($query)
    {
        return $query->where('estado', 1);
    }
}
