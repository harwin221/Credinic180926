<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class departamentoMunicipioModel extends Model
{
    use HasFactory;
    protected $table = "departamento_municipio";

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }
    public function departamento()
    {
        return $this->hasOne(departamentoModel::class, 'id', 'departamento_id');
    }
}
