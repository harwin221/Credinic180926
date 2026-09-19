@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.user.clientes.index')])
    Nuevo Cliente
@endsection

@section('content')
    {{html()->form('POST',route('agentes.user.clientes.store'))->acceptsFiles()->open()}}
    @include('agentesViews.registroClientes.agenteFormClientes')
    <hr>

{{--    <div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <h4>Nueva solicitud de desembolso</h4>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <br>--}}
{{--    <div class="row">--}}
{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for="monto"><b>Monto Solicitado:</b></label>--}}
{{--                <div class="input-group mb-3">--}}
{{--                    <span class="input-group-text">--}}
{{--                        {{html()->select('moneda',['1'=>'C$','2'=>'U$'])->id('moneda')->required()}}--}}
{{--                    </span>--}}
{{--                        <input type="number" name="monto_solicitado" placeholder="Monto Solicitado" min="1" step="0.001" value="{{old('monto_solicitado')}}" class="form-control" aria-label="Monto" id="monto">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for="plazo"><b>Plazo Solicitado (Meses):</b></label>--}}
{{--                <input type="number" placeholder="Plazo" class="form-control" required name="plazo_solicitado" value="{{old('plazo_solicitado')}}" step="0.01" min="1" id="plazo">--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for="forma_pago"><b>Forma de Pago:</b></label>--}}
{{--                {{html()->select('forma_pago_solicitada',[''=>'* Seleccione *','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual'],old('forma_pago_solicitada'))->class('form-control')->required()->id('forma_pago')}}--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for="fecha"><b>Fecha Primer Pago: <span style="color: red">*</span></b></label>--}}
{{--                <input type="date" min="{{date('Y-m-d')}}" name="fecha_primer_pago" required class="form-control" id="fecha">--}}
{{--            </div>--}}
{{--        </div>--}}

{{--             <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for="forma_pago"><b>Tasa Propuesta:</b></label>--}}
{{--                <input type="number" placeholder="Tasa" value="{{old('tasa_propuesta')}}" class="form-control" required name="tasa_propuesta" step="0.01" min="1" id="tasa">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <div class="form-group">--}}
{{--                <label for="observaciones"><b>Observaciones:</b></label>--}}
{{--                <textarea rows="2" name="observaciones" class="form-control" id="observaciones">{{old('observaciones')}}</textarea>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <br>--}}
    <div class="row justify-content-center">
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Guardar datos del cliente <i class="fa fa-save"></i>
            </button>
        </div>
    </div>

    {{html()->form()->close()}}
@endsection
{{--@section('script')--}}
{{--     <script>--}}

{{--        const inputs = document.querySelectorAll("input[type='file']");--}}

{{--        // Crear un contenedor para las imágenes--}}
{{--        const contenedor = document.querySelector(".contenedor-imagenes");--}}

{{--        // Escuchar el evento change de cada input file--}}
{{--        inputs.forEach((input) => {--}}
{{--            input.addEventListener("change", () => {--}}
{{--                // Obtener las imágenes seleccionadas--}}
{{--                const imagenes = input.files;--}}

{{--                Array.from(imagenes).forEach(imagen => {--}}
{{--                    // Mostrar el preview de la imagen--}}
{{--                    const imagenPrevisualizacion = document.createElement("img");--}}
{{--                    imagenPrevisualizacion.src = URL.createObjectURL(imagen);--}}
{{--                    imagenPrevisualizacion.style.width = "100px";--}}
{{--                    imagenPrevisualizacion.style.height = "100px";--}}
{{--                    contenedor.appendChild(imagenPrevisualizacion);--}}
{{--                });--}}
{{--            });--}}
{{--        });--}}
{{--    </script>--}}
{{--@endsection--}}
