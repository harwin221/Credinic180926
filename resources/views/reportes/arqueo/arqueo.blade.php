@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.arqueo.list')])
    Editar Arqueo
@endsection
@section('content')
    @if($arqueo && $arqueo->fecha_arqueo!=date('Y-m-d'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                    <strong>Este arqueo no puede ser modificado</strong>
                </div>
            </div>
        </div>
    @endif

    <div class="row text-center">
        <div class="col-md-12">
            <p style="color: blue"><b>*No se puede modificar arqueos de fechas anteriores*</b></p>
        </div>
    </div>

    <div class="row justify-content-center">
        @if($arqueo)
            <div class="col-md-2">
                <a href="{{route('reportes.arqueo.arqueoDetalleCuotas',[$arqueo->fecha_arqueo,encode($arqueo->cobrador_id),$arqueo->id_enc])}}" target="_blank" class="btn btn-primary w-100">Detalle Cuotas <i class="fa fa-list"></i></a>
            </div>
            <div class="col-md-2">
                <a href="{{route('reportes.arqueo.exportarArqueo',$arqueo->id_enc)}}" class="btn btn-success w-100">Exportar <i class="fa fa-file-excel"></i></a>
            </div>
        @endif
    </div>
    <hr>
    <br>
    @if($arqueo && $arqueo->fecha_arqueo==date('Y-m-d'))
    {{html()->form('POST',route('reportes.arqueo.saveUpdate'))->id('frmArqueo')->open()}}
    @endif
    <input type="hidden" name="fecha" value="{{($arqueo)?$arqueo->fecha_arqueo:request()->get('fecha')}}">
    <input type="hidden" name="cobrador" value="{{($arqueo)?encode($arqueo->cobrador_id):request()->get('cobrador')}}">

    <div class="row justify-content-center">
        <div class="col-md-3">
            <div class="form-group text-center">
                <label for="efectivo"><b>Total Recuperado:</b></label>
                <input type="text" name="efectivo" id="efectivo" readonly value="{{$total_recuperado}}" class="form-control text-center">
            </div>
        </div>

         <div class="col-md-3">
            <div class="form-group text-center">
                <label for="tipocambio"><b>Tipo de Cambio:</b></label>
                <input type="number" name="tipocambio" id="tipocambio" value="{{($arqueo?$arqueo->tipocambio:0)}}" step="0.01" min="0" class="form-control text-center">
            </div>
        </div>
    </div>
    <br>
    @include('reportes.arqueo.controlesArqueo')
    <br>
    @if($arqueo && $arqueo->fecha_arqueo==date('Y-m-d'))
    <div class="row">
        <div class="col-md-12">
            <button type="submit" id="btnGuardar" class="w-100 btn btn-primary">Guardar Arqueo <i class="fa fa-save"></i> </button>
        </div>
    </div>
    {{html()->form()->close()}}
    @endif
    <br>
    <div class="row">
        <div class="col-md-12">
            <h4>Lista de Abonos</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table style="width: 100%;border-collapse: collapse;border: 1px black;text-align: left">
                <tr style="border: 1px black;background: silver">
                    <th>#</th>
                    <th>Consecutivo</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Capital</th>
                    <th>Interes</th>
                    <th>Total Abonado</th>
                </tr>
                <?php $suma = 0; ?>
                @foreach($abonos as $ab)
                    @php
                        $esCancelacion = ($ab->tipo_abono == 3 || str_contains(strtolower($ab->referencia_transferencia ?? ''), 'cancelaci') || str_contains(strtolower($ab->tipo ?? ''), 'cancelaci'));
                        $esExterno = !$esCancelacion && ($ab->prestamo->agente_id !== $ab->created_user_id);
                    @endphp
                    <tr @if($esCancelacion) style="background:#f5f3ff;" @elseif($esExterno) style="background:#fffbe6;" @endif>
                        <td>{{$loop->index+1}}</td>
                        <td>{{$ab->prestamo->consecutivo}}</td>
                        <td>{{$ab->prestamo->cliente->full_name}}</td>
                        <td>
                            @if($esCancelacion)
                                <span style="background:#7c3aed;color:#fff;font-size:11px;padding:2px 7px;border-radius:4px;font-weight:600;">
                                    Cancelación
                                </span>
                            @elseif($esExterno)
                                <span style="background:#ffc107;color:#000;font-size:11px;padding:2px 7px;border-radius:4px;font-weight:600;">
                                    Externo
                                </span>
                            @else
                                <span style="background:#198754;color:#fff;font-size:11px;padding:2px 7px;border-radius:4px;font-weight:600;">
                                    Cartera
                                </span>
                            @endif
                        </td>
                        <td>{{$ab->total_abonado_capital}}</td>
                        <td>{{$ab->total_abonado_interes}}</td>
                        <td>{{$ab->total_abonado}}</td>
                    </tr>
                @endforeach
                <tr style="background: lightgrey">
                    <th colspan="5" style="text-align: right;margin-right: 10px">Total</th>
                    <th></th>
                    <th>{{number_format($total_recuperado,2)}}</th>
                </tr>
            </table>
        </div>
    </div>

@endsection

@section('script')
    <script>

        document.querySelectorAll('.valor').forEach((element) => {
            element.addEventListener('keyup', calcularValores)
            element.addEventListener('change', calcularValores)
        })

        function calcularValores(e){

            let tipoCambio = document.getElementById('tipocambio').value
            let efectivo = document.getElementById('efectivo').value

            if (tipoCambio === '' || efectivo === '') {
                Swal.fire('Warning', 'Debe ingresar el valor del dolar y el total efectivo para seguir', 'warning')
                e.target.value = ''
                return
            }

            const caja_resultado = document.querySelector("[data-name="+e.target.name+"]")
            caja_resultado.value = ""

            if(!isNaN(e.target.value)){
                let valor = '0';
                if (e.target.value !== '')
                    valor = e.target.dataset.valor

                caja_resultado.value = (parseFloat(e.target.value) * parseFloat(valor)).toFixed(2)

                recalcularTotales()
            }
        }

        function recalcularTotales() {
            let tipoCambio = parseFloat(document.getElementById('tipocambio').value) || 0
            let efectivo = parseFloat(document.getElementById('efectivo').value) || 0
            let desembolsos = parseFloat(document.getElementById('desembolsos').value) || 0

            let totalDolar = 0
            let totalCordoba = 0

            document.querySelectorAll('.resultado').forEach((res) => {
                if (res.value !== '' && !isNaN(res.value)) {
                    if (res.classList.contains('cordoba'))
                        totalCordoba += parseFloat(res.value)
                    if (res.classList.contains('dolar'))
                        totalDolar += parseFloat(res.value)
                }
            })

            let dolar_a_cordoba = tipoCambio * totalDolar
            let sumaTotal = totalCordoba + dolar_a_cordoba

            document.getElementById('total_c').value = totalCordoba.toFixed(2)
            document.getElementById('total_d').value = totalDolar.toFixed(2)
            document.getElementById('subtotal').value = sumaTotal.toFixed(2)
            document.getElementById('conversion').value = dolar_a_cordoba.toFixed(2)
            // Diferencia = Recuperado - (Billetaje + Desembolsos)
            document.getElementById('neto').value = (efectivo - (sumaTotal + desembolsos)).toFixed(2)
        }

        // document.getElementById('fecha').addEventListener('change',function (){
        //     document.getElementById('frmCargarArqueo').submit()
        // })

        addEventListener('DOMContentLoaded', (event) => {
            document.querySelectorAll('.valor').forEach((el) => {
                if (el.value !== '' && !isNaN(el.value)) {
                    const caja_resultado = document.querySelector("[data-name="+el.name+"]")
                    if (caja_resultado) {
                        caja_resultado.value = (parseFloat(el.value) * parseFloat(el.dataset.valor || 0)).toFixed(2)
                    }
                }
            })
            recalcularTotales()

            // Recalcular cuando cambia el campo desembolsos
            document.getElementById('desembolsos').addEventListener('input', recalcularTotales)
            document.getElementById('desembolsos').addEventListener('change', recalcularTotales)
        });

        document.getElementById('btnGuardar').addEventListener('click', function (e) {
            e.preventDefault();
            if (document.getElementById('efectivo').value !== '' && document.getElementById('tipocambio').value !== '') {
                document.getElementById('frmArqueo').submit();
            } else {
                Swal.fire('Campo Obligatorio','La fecha, el cobrador,el tipo de cambio y el efectivo son campos obligatorios','warning');
            }
        })


        document.querySelectorAll('input[type=number]').forEach(input => {
            input.addEventListener('wheel', function(e) {
                e.preventDefault();
            }, { passive: false });
        });

    </script>
@endsection
