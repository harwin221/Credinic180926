<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userNegocioDocumentosModel extends Model
{
    use HasFactory;
    protected $table = "user_negocio_documentos";


    public function getIdEncAttribute()
    {
        return encode($this->id);
    }
}
