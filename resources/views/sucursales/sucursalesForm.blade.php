@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack', ['url' => route('sucursales.index')])
    {{ isset($sucursal) ? 'Editar Sucursal' : 'Nueva Sucursal' }}
@endsection
@section('content')

    @if(isset($sucursal))
        {{ html()->modelForm($sucursal, 'POST', route('sucursales.update', $sucursal->id_enc))->open() }}
        @method('PUT')
    @else
        {{ html()->form('POST', route('sucursales.store'))->open() }}
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="fw-bold required">Nombre:</label>
                {{ html()->text('nombre')->class('form-control')->required() }}
                @error('nombre') <strong class="text-danger">{{ $message }}</strong> @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="fw-bold">Teléfono:</label>
                {{ html()->text('telefono')->class('form-control') }}
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="fw-bold">Dirección:</label>
                {{ html()->textarea('direccion')->class('form-control')->rows(3) }}
            </div>
        </div>
    </div>

    @if(isset($sucursal))
    <br>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="fw-bold">Estado:</label>
                {{ html()->select('estado', ['1' => 'Activa', '2' => 'Inactiva'])->class('form-control') }}
            </div>
        </div>
    </div>
    @endif

    <br>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <button type="submit" class="btn btn-success w-100">
                <i class="fa fa-save"></i> {{ isset($sucursal) ? 'Actualizar' : 'Guardar' }}
            </button>
        </div>
    </div>

    {{ html()->form()->close() }}

@endsection
