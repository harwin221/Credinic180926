<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userAsignadoModel extends Model
{
    use HasFactory;
    protected $table = "user_asignados";

    public function asignado()
    {
        return $this->hasOne(User::class,'id','admin_asignado_id');
    }
}
