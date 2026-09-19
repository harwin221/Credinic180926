<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class userNegociosModel extends Model
{
    use HasFactory,SoftDeletes;
    protected $table ="user_negocios";

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

//    public function getStringTiposAttribute()
//    {
//        $tipos = negocioTiposModel::whereIn('id',json_decode($this->tipo_negocio_id))->get();
//        $tiposNegocio = "";
//        if($tipos)
//            foreach ($tipos as $tipo)
//            {
//                $tiposNegocio .= $tipo->nombre.", ";
//            }
//
//        return $tiposNegocio;
//    }

//    public function getTiposNegociosAttribute()
//    {
//        return negocioTiposModel::whereIn('id',json_decode($this->tipo_negocio_id))->get();
//    }

    public function tipo_negocio()
    {
        return $this->hasOne(negocioTiposModel::class,'id','tipo_negocio_id');
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function documentos_negocio()
    {
        return $this->hasMany(userNegocioDocumentosModel::class,'user_negocio_id','id');
    }

    public function prestamo()
    {
        return $this->hasMany(prestamosModel::class,'negocio_id','id');
    }

    public function departamento_municipio()
    {
        return $this->hasOne(departamentoMunicipioModel::class,'id','municipio_id');
    }
}
