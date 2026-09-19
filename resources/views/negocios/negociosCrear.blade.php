@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.clientes.edit',$user->id_enc)])
    Información del Negocio [ <span style="color: blue;font-weight: bold">{{$user->full_name}}</span> ]
@endsection
@section('content')
    {{html()->form('POST',route('negocio.store'))->acceptsFiles()->open()}}
    <input type="hidden" name="clienteId" value="{{$user->id_enc}}">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="negocio" class="fw-bold">Negocio:</label>
                <input type="text" name="nombre" class="form-control" required id="nombre">
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="departamento" class="fw-bold">Departamento / Municipio:</label>
                {{html()->select('dep_mun',[''=>"* Seleccione *"]+departamento_municipios())->class('form-control')->required()->id('dep_mun')}}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="ubicacion" class="fw-bold">Ubicación Geográfica:</label>
                <input name="punto_geografico" type="text" class="form-control" id="ubicacion">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="telefono_negocio" class="fw-bold">Teléfono del Negocio:</label>
                <input type="text" id="telefono_negocio" name="telefono_negocio" class="form-control">
            </div>
        </div>

    </div>
    <br>
    <div class="row">
        <div class="form-group">
            <label for="direccion" class="fw-bold">Dirección:</label>
            <textarea name="direccion" class="form-control" required rows="2" id="direccion"></textarea>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="form-group">
            <label for="comentarios" class="fw-bold">Comentarios:</label>
            <textarea name="comentarios" class="form-control" rows="2" id="comentarios"></textarea>
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
            <div class="input-group input-group-sm mb-3">
                <input type="file" accept="image/jpeg, image/png, image/jpeg" name="documentos[]" multiple>
            </div>
        </div>
    </div>
    <br>
    @can('Crear Negocios')
    <div class="row">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-sm btn-success">Guardar Negocio<i class="fa fa-save"></i> </button>
        </div>
    </div>
    @endcan
    {{html()->form()->close()}}
@endsection
