@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    Arqueo Agentes
@endsection
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                <strong>Una vez que se guarda el arqueo, este no podrá ser modificado</strong>
            </div>
        </div>
    </div>
    @if(!isset($arqueo) || $arqueo->fecha_arqueo === date('d-m-Y'))
    {{html()->form('POST',route('agentes.arqueo.saveOrUpdate'))->id('frmArqueo')->open()}}
    @endif
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="form-group text-center">
                <label for="fecha"><b>Fecha:</b></label>
                <input type="date" id="fecha" readonly value="{{($arqueo?$arqueo->fecha_arqueo:Carbon\Carbon::now()->toDateString())}}" name="fecha" class="form-control text-center">
            </div>
        </div>
    </div>
    <hr>
    <br>

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
                <input type="number" name="tipocambio" id="tipocambio" value="{{isset($arqueo)?$arqueo->tipocambio:null}}" step="0.01" min="0" class="form-control text-center">
            </div>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4">
                    0.50c C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="0.50 C$" value="{{($arqueo)?$arqueo->moneda_c_050:null}}" min="0" tabindex="1" step="1" name="moneda_c_050" data-valor="0.5" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="moneda_c_050" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    1 C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="1 C$" value="{{($arqueo)?$arqueo->moneda_c_1:null}}" min="0" tabindex="2" step="1" name="moneda_c_1" data-valor="1" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="moneda_c_1" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    5 C$ (Mon)
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="5 C$ (Mon)" value="{{($arqueo)?$arqueo->moneda_c_5:null}}" min="0" tabindex="3" step="1" name="moneda_c_5" data-valor="5" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="moneda_c_5" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    5 C$ (Bill)
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="5 C$ (Bill)" value="{{($arqueo)?$arqueo->billete_c_5:null}}" min="0" tabindex="3" step="1" name="billete_c_5" data-valor="5" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_c_5" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    10 C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="10 C$" value="{{($arqueo)?$arqueo->billete_c_10:null}}" min="0" tabindex="4" step="1" name="billete_c_10" data-valor="10" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_c_10" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    20 C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="20 C$" value="{{($arqueo)?$arqueo->billete_c_20:null}}" min="0" tabindex="5" step="1" name="billete_c_20" data-valor="20" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_c_20" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    50 C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="50 C$" value="{{($arqueo)?$arqueo->billete_c_50:null}}" min="0" tabindex="6" step="1" name="billete_c_50" data-valor="50" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_c_50" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    100 C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="100 C$" value="{{($arqueo)?$arqueo->billete_c_100:null}}" min="0" tabindex="7" step="1" name="billete_c_100" data-valor="100" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_c_100" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    200 C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="200 C$" value="{{($arqueo)?$arqueo->billete_c_200:null}}" min="0" tabindex="8" step="1" name="billete_c_200" data-valor="200" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_c_200" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    500 C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="500 C$" value="{{($arqueo)?$arqueo->billete_c_500:null}}" min="0" tabindex="9" step="1" name="billete_c_500" data-valor="500" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_c_500" class="form-control resultado cordoba" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    1000 C$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="1000 C$" value="{{($arqueo)?$arqueo->billete_c_1000:null}}" min="0" tabindex="10" step="1" name="billete_c_1000" data-valor="1000" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_c_1000" class="form-control resultado cordoba" disabled>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4">
                    1 U$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="1 U$" value="{{($arqueo)?$arqueo->billete_d_1:null}}" min="0" tabindex="11" step="1" name="billete_d_1" data-valor="1" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_d_1" class="form-control resultado dolar" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    2 U$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="2 U$" value="{{($arqueo)?$arqueo->billete_d_2:null}}" min="0" tabindex="12" step="1" name="billete_d_2" data-valor="2" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_d_2" class="form-control resultado dolar" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    5 U$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="5 U$" value="{{($arqueo)?$arqueo->billete_d_5:null}}" min="0" tabindex="13" step="1" name="billete_d_5" data-valor="5" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_d_5" class="form-control resultado dolar" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    10 U$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="10 U$" value="{{($arqueo)?$arqueo->billete_d_10:null}}" min="0" tabindex="14" step="1" name="billete_d_10" data-valor="10" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_d_10" class="form-control resultado dolar" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    20 U$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="20 U$" value="{{($arqueo)?$arqueo->billete_d_20:null}}" min="0" tabindex="15" step="1" name="billete_d_20" data-valor="20" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_d_20" class="form-control resultado dolar" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    50 U$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="50 U$" value="{{($arqueo)?$arqueo->billete_d_50:null}}" min="0" tabindex="16" step="1" name="billete_d_50" data-valor="50" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_d_50" class="form-control resultado dolar" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    100 U$
                </div>
                <div class="col-md-4">
                    <input type="number" placeholder="100 U$" value="{{($arqueo)?$arqueo->billete_d_100:null}}" min="0" tabindex="17" step="1" name="billete_d_100" data-valor="100" class="form-control text-center valor">
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" data-name="billete_d_100" class="form-control resultado dolar" disabled>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-8 text-end">
                    Total C$:
                </div>
                <div class="col-md-4">
                    <input type="number"  id="total_c" min="0" step="1" name="total_c" class="form-control" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-8 text-end">
                    Subtotal C$ + U$:
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" name="subtotal" class="form-control" disabled id="subtotal">
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-8 text-end">
                    Diferencia C$:
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" name="neto" class="form-control" disabled id="neto">
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-8 text-end">
                    Total U$:
                </div>
                <div class="col-md-4">
                    <input type="number" id="total_d" min="0" step="1" name="total_d" class="form-control" disabled>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-8 text-end">
                    Conversión U$ - C$:
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" step="1" name="conversion" class="form-control" disabled id="conversion">
                </div>
            </div>
        </div>
    </div>
    <br>

    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(!isset($arqueo) || $arqueo->fecha_arqueo === date('d-m-Y'))
                <button type="submit" id="btnGuardar" class="w-100 btn btn-sm btn-primary">Guardar Arqueo <i class="fa fa-save"></i> </button>
            @else
                <button type="button" class="w-100 btn btn-lg btn-danger" disabled>Este arqueo ya no puede ser modificado</button>
            @endif
        </div>
    </div>
    @if(!isset($arqueo) || $arqueo->fecha_arqueo === date('d-m-Y'))
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
                    <th colspan="6" style="text-align: right;margin-right: 10px">Total</th>
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
                Swal.fire('Warning', 'Debe ingresar el valor del dolar y haber registros de recuperación para seguir', 'warning')
                e.target.value = ''
                return
            }

            const caja_resultado = document.querySelector("[data-name="+e.target.name+"]") //elemento relacionado donde se coloca el total de esa moneda
            caja_resultado.value = ""

            if(!isNaN(e.target.value)){ //si es número y diferente de vacio

                let valor = '0';
                if (e.target.value !== '')
                    valor = e.target.dataset.valor

                caja_resultado.value = (parseFloat(e.target.value) * parseFloat(valor)).toFixed(2) //total monedas * cantidad

                let totalDolar = 0
                let totalCordoba = 0

                document.querySelectorAll('.resultado').forEach((res)=>{ //totalizar todos los valores y ponerlos en caja de subtotal
                    if(res.value!=='' && !isNaN(res.value)){
                        if(res.classList.contains('cordoba'))
                            totalCordoba+=parseFloat(res.value)
                        if(res.classList.contains('dolar'))
                            totalDolar+=parseFloat(res.value)
                    }
                })

                let dolar_a_cordoba = parseFloat(tipoCambio) * parseFloat(totalDolar);
                let sumaTotal = parseFloat(totalCordoba) + dolar_a_cordoba;

                document.getElementById('total_c').value = parseFloat(totalCordoba).toFixed(2)
                document.getElementById('total_d').value = parseFloat(totalDolar).toFixed(2)
                document.getElementById('subtotal').value = parseFloat(sumaTotal).toFixed(2)
                document.getElementById('conversion').value = dolar_a_cordoba.toFixed(2)
                document.getElementById('neto').value = (parseFloat(efectivo) - parseFloat(sumaTotal)).toFixed(2)
            }
        }

        // document.getElementById('fecha').addEventListener('change',function (){
        //     document.getElementById('frmCargarArqueo').submit()
        // })

        addEventListener('DOMContentLoaded', (event) => {
            document.querySelectorAll('.valor').forEach((el)=>{
                if(!isNaN(el.value)){ //si es número y diferente de vacio

                    let tipoCambio = document.getElementById('tipocambio').value
                    let efectivo = document.getElementById('efectivo').value

                    let valor = 0;
                    if (el.value !== '')
                        valor = el.dataset.valor

                    const caja_resultado = document.querySelector("[data-name="+el.name+"]") //elemento relacionado donde se coloca el total de esa moneda
                    caja_resultado.value = ""

                    caja_resultado.value = (parseFloat(el.value) * parseFloat(valor)).toFixed(2) //total monedas * cantidad

                    let totalDolar = 0
                    let totalCordoba = 0

                    document.querySelectorAll('.resultado').forEach((res)=>{ //totalizar todos los valores y ponerlos en caja de subtotal
                        if(res.value!=='' && !isNaN(res.value)){
                            if(res.classList.contains('cordoba'))
                                totalCordoba+=parseFloat(res.value)
                            if(res.classList.contains('dolar'))
                                totalDolar+=parseFloat(res.value)
                        }
                    })

                    let dolar_a_cordoba = parseFloat(tipoCambio) * parseFloat(totalDolar);
                    let sumaTotal = parseFloat(totalCordoba) + dolar_a_cordoba;

                    document.getElementById('total_c').value = parseFloat(totalCordoba).toFixed(2)
                    document.getElementById('total_d').value = parseFloat(totalDolar).toFixed(2)
                    document.getElementById('subtotal').value = parseFloat(sumaTotal).toFixed(2)
                    document.getElementById('conversion').value = dolar_a_cordoba.toFixed(2)
                    document.getElementById('neto').value = (parseFloat(efectivo) - parseFloat(sumaTotal)).toFixed(2)
                }
            })
        });

        document.getElementById('btnGuardar').addEventListener('click', function (e) {
            e.preventDefault();
            if (document.getElementById('efectivo').value !== '' && document.getElementById('tipocambio').value !== '') {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea guardar el arqueo?, una vez guardado no podrá ser modificado",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#23cd26',
                    confirmButtonText: 'Guardar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('frmArqueo').submit();
                    }
                })
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

