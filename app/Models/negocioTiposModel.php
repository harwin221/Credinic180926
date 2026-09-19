<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class negocioTiposModel extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'negocio_tipos';

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }
}
