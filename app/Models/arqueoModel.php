<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class arqueoModel extends Model
{
    use HasFactory;
    protected $table = "arqueo";

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    protected $fillable =[
        'fecha_arqueo',
        'estado',
        'moneda_c_050',
        'billete_d_1',
        'moneda_c_1',
        'billete_d_2',
        'moneda_c_5',
        'billete_d_5',
        'billete_c_5',
        'billete_d_10',
        'billete_c_10',
        'billete_d_20',
        'billete_c_20',
        'billete_d_50',
        'billete_c_50',
        'billete_d_100',
        'billete_c_100',
        'billete_c_200',
        'billete_c_500',
        'billete_c_1000',
        'total_cordoba',
        'total_dolar',
        'desembolsos',
        'created_user_id',
    ];

    public function created_user()
    {
        return $this->hasOne(User::class,'id','created_user_id');
    }

    function cobrador()
    {
        return $this->hasOne(User::class,'id','cobrador_id');
    }
}
