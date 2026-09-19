<?php

//use Hashids\Hashids;
use App\Models\departamentoModel;
use App\Models\departamentoMunicipioModel;
use Vinkla\Hashids\Facades\Hashids;

function encode($str): string
{
    return Hashids::encode($str);
}
function decode($str): string
{
    return implode(Hashids::decode($str));
}

function userLogeado()
{
    return \Auth::user();
}

function MAYUS($str)
{
    return mb_strtoupper($str);
}

function minus($str)
{
    return mb_strtolower($str);
}

function fecha_d_m_Y($fecha){
    return date('d-m-Y',strtotime($fecha));
}

function fecha_d_m_Y_h_i($fecha){
    return date('d-m-Y h:ia',strtotime($fecha));
}

function departamento_municipios()
{
    $departamentos = departamentoMunicipioModel::with('departamento')->get();
    $arr = [];
    foreach ($departamentos as $dep) {
        $arr[$dep->departamento->nombre][$dep->id_enc] = $dep->nombre;
    }
    return $arr;
}


function tipoprestamos()
{
    return ['' => '--- Seleccione ---', '1' => 'Nuevo', '2' => 'Represtamo', '3' => 'Reactivación','4'=>'Reestructuración'];
}

function destinoPrestamo()
{
    return [
        '1' => 'Comercio',
        '2' => 'Personales/Consumo',
        '3' => 'Servicios',
        '4' => 'Vivienda (Compra, Mejora, Ampliación, Remodelación, Otros)',
        '5' => 'Construcción',
        '6' => 'Industria',
        '7' => 'Pesca',
        '8' => 'Agricultura y Ganadería',
        '9' => 'Otros',
    ];
}
