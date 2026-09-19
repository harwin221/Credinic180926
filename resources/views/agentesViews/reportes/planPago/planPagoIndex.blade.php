@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    Plan de Pago
@endsection
@section('content')
    {{html()->form('GET',route('agentes.reportes.planPago'))->id('frmPlanPago')->open()}}
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
            <button name="exportar" value="1" class="btn btn-sm btn-success w-100">Generar Plan de Pago <i class="fa fa-share"></i> </button>
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
            $("#frmPlanPago").removeAttr('target')
            $("#frmPlanPago").submit()
        })

        $("#selPrestamos").on('change', function () {
            // Quitar target para que se mantenga en la misma pestaña al cambiar préstamo
            $("#frmPlanPago").removeAttr('target')
            $("#frmPlanPago").submit()
        })

        // Cuando se hace clic en el botón "Generar Plan de Pago", abrir en nueva pestaña
        $('button[name="exportar"]').on('click', function(e) {
            $("#frmPlanPago").attr('target', '_blank')
        })

    </script>
@endsection
