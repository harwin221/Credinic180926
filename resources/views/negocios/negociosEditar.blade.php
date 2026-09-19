@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.clientes.edit',encode($user_negocio->user_id))])
    Información del Negocio [ <span style="color: blue;font-weight: bold">{{$user_negocio->user->full_name}}</span> ]
@endsection
@section('content')
    {{html()->form('POST',route('negocio.update',$user_negocio->id_enc))->acceptsFiles()->open()}}
    @method('PUT')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="negocio" class="fw-bold">Negocio:</label>
                <input type="text" name="nombre" class="form-control" required id="nombre" value="{{$user_negocio->nombre}}">
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="departamento" class="fw-bold">Departamento / Municipio:</label>
                {{html()->select('dep_mun',[''=>"* Seleccione *"]+departamento_municipios(),encode($user_negocio->municipio_id))->class('form-control')->required()->id('dep_mun')}}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="ubicacion" class="fw-bold">Ubicación Geográfica:</label>
                <input name="punto_geografico" type="text" class="form-control" value="{{$user_negocio->punto_geografico}}" id="ubicacion">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="telefono_negocio" class="fw-bold">Teléfono del Negocio:</label>
                <input type="text" id="telefono_negocio" name="telefono_negocio" value="{{$user_negocio->telefono_negocio}}" class="form-control">
            </div>
        </div>

    </div>
    <br>
    <div class="row">
        <div class="form-group">
            <label for="direccion" class="fw-bold">Dirección:</label>
            <textarea name="direccion" class="form-control" rows="2" id="direccion">{{$user_negocio->direccion}}</textarea>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="form-group">
            <label for="comentarios" class="fw-bold">Comentarios:</label>
            <textarea name="comentarios" class="form-control" rows="2" id="comentarios">{{$user_negocio->comentarios}}</textarea>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <h4>Fotos de Negocio</h4>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">

                @foreach($user_negocio->documentos_negocio as $docs)
                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btnEliminarImagen"><i class="fa fa-times"></i></a>
                    <img style="width: 200px" src="{{ asset('assets/img/negocios/'.$docs->url)}}" class="img-thumbnail" alt="">
                @endforeach
            </div>
            <br>
            <div class="input-group input-group-sm mb-3">
                <input type="file" accept="image/jpeg, image/png, image/jpeg" name="documentos[]" multiple>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        @can('Editar Negocios')
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-sm btn-success">Actualizar Información <i class="fa fa-save"></i> </button>
        </div>
        @endcan
    </div>
    {{html()->form()->close()}}
@endsection
