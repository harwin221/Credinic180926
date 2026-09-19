<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class tipoDocumentosModel extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="tipo_documentos";

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    public function getDocumentoTipoAttribute()
    {
        return ($this->tipo == 1) ? 'Requerido' : 'Opcional';
    }

}
