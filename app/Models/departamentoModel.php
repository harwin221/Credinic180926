<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class departamentoModel extends Model
{
    use HasFactory;
    protected $table = "departamento";

    public function municipios()
    {
        return $this->hasMany(departamentoMunicipioModel::class, 'departamento_id', 'id');
    }
}
