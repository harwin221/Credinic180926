@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Estado de Cuenta
@endsection
@section('content')
    {{html()->form('GET',route('reportes.estadoCuentaCliente'))->id('frmEstadoCuenta')->open()}}
    <div class="row">
        <div class="col-12 col-md-6 mb-3">
            <div class="form-group">
                <label for="selCliente"><b>Cliente:</b></label>
                {{html()->select('cliente',[''=>'-- Seleccione --']+$listaClientes,request()->get('cliente'))->class('form-control select2')->required()->id('selCliente')->style('width: 100%')}}
            </div>
        </div>

        <div class="col-12 col-md-6 mb-3">
            <div class="form-group">
                <label for="selPrestamos"><b>Préstamos:</b></label>
                {{html()->select('prestamos',[''=>'-- Seleccione --']+$prestamos,request()->get('prestamos'))->class('form-control select2')->required()->id('selPrestamos')->style('width: 100%')}}
            </div>
        </div>
    </div>
    @if($prestamoSel)
    <div class="row justify-content-center mt-3">
        <div class="col-12 col-md-6 col-lg-4">
            <button name="exportar" value="1" class="btn btn-sm btn-success w-100">Generar Estado de Cuenta <i class="fa fa-share"></i> </button>
        </div>
    </div>
    @endif
    {{html()->form()->close()}}
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded',function (){
            // Inicializar select2 para ambos selectores
            $('#selCliente').select2({
                width: '100%',
                placeholder: '-- Seleccione --'
            });
            
            $('#selPrestamos').select2({
                width: '100%',
                placeholder: '-- Seleccione --'
            });
        })

        $("#selCliente").on('change', function () {
            $("#selPrestamos").val('')
            // Quitar target para que se mantenga en la misma pestaña al cambiar cliente
            $("#frmEstadoCuenta").removeAttr('target')
            $("#frmEstadoCuenta").submit()
        })

        $("#selPrestamos").on('change', function () {
            // Quitar target para que se mantenga en la misma pestaña al cambiar préstamo
            $("#frmEstadoCuenta").removeAttr('target')
            $("#frmEstadoCuenta").submit()
        })

        // Cuando se hace clic en el botón "Generar Estado de Cuenta", abrir en nueva pestaña
        $('button[name="exportar"]').on('click', function(e) {
            $("#frmEstadoCuenta").attr('target', '_blank')
        })

    </script>
@endsection
