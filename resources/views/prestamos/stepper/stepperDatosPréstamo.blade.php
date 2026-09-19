@if(!isset($agente))
    <div class="d-flex justify-content-center">
        <div class="form-check form-switch">
            <input class="form-check-input" style="font-size: 20px" type="checkbox" value="1" name="chkSolicitud" id="chkSolicitud">
            <label class="form-check-label fw-bold text-uppercase" style="font-size: 20px;color: blue" for="chkSolicitud">Solicitud de Desembolso</label>
        </div>
    </div>
    <hr>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="realizadoPor">Vendedor:</label>
            @if(isset($agente))
                <input type="hidden" name="vendedor" value="{{userLogeado()->id_enc}}">
                <input type="text" id="selectVendedor" disabled class="form-control" value="{{userLogeado()->full_name}}">
            @else
                {{html()->select('vendedor',[],null)->class('form-control select2')->style(['width'=>'100%'])->id('selectVendedor')}}
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="realizadoPor">Cobrador:</label>
            @if(isset($agente))
                <input type="hidden" name="agente" value="{{userLogeado()->id_enc}}">
                <input type="text" id="selectAgente" disabled class="form-control" value="{{userLogeado()->full_name}}">
            @else
                {{html()->select('agente',[],null)->class('form-control select2')->style(['width'=>'100%'])->id('selectAgente')}}
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="desembolso">Desembolsado Por:</label>
            @if(isset($agente))
                <input type="hidden" name="desembolso" value="{{userLogeado()->id_enc}}">
                <input type="text" id="selectDesembolso" disabled class="form-control" value="{{userLogeado()->full_name}}">
            @else
                {{html()->select('desembolso',[],null)->class('form-control select2')->style(['width'=>'100%'])->id('selectDesembolso')}}
            @endif
        </div>
    </div>
</div>
<br>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="fechaPrestamo" class="required">Fecha del Préstamo:</label>
            <input type="date" name="fechaPrestamo" min="{{date('Y-m-d')}}" value="{{date('Y-m-d')}}" class="form-control" id="fechaPrestamo">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="fechaDesembolso" class="required">Fecha Desembolso:</label>
            <input type="date" name="fechaDesembolso" min="{{date('Y-m-d')}}" value="{{date('Y-m-d')}}" class="form-control" id="fechaDesembolso">
        </div>
    </div>
</div>

<br>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="montoFinanciar">Monto a Financiar:</label>
            <div class="input-group mb-3">
                <span class="input-group-text">
                    {{html()->select('moneda',['1'=>'C$','2'=>'U$'])->id('moneda')->required()}}
                </span>
                <input type="number" name="montoFinanciar" min="1" step="0.001" class="form-control calcular" aria-label="Monto" id="montoFinanciar">
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="formaPago">Forma de Pago:</label>
            {{html()->select('formaPago',[''=>'* Seleccione *','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'])->class('form-control calcular')->id('formaPago')->required()}}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="plazoPago" class="required">Plazo de Pago (Meses):</label>
            <input type="number" step="0.5" min="0" name="plazoPago" required class="form-control calcular" id="plazoPago">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="tasaInteres">Tasa de Interes: % (Mensual)</label>
            <input type="number" min="0" step="0.01" required class="form-control calcular" name="tasaInteres" id="tasaInteres">
        </div>
    </div>


</div>
<div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="montoTotalFinanciar" class="required">Monto Total a Financiar:</label>
                <input type="number" step="0.01" name="montoTotalFinanciar" style="background: lightgray" required class="form-control calcular" id="montoTotalFinanciar" readonly>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="montoCuota" class="required">Monto de la Cuota</label>
                <input type="number" step="0.01" name="montoCuota" required class="form-control" style="background: lightgray" id="montoCuota" readonly>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="interesPagar" class="required">Intereses a Pagar</label>
                <input type="number" step="0.01" name="interesPagar" required class="form-control" style="background: lightgray" id="interesPagar" readonly>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="interesMes" class="required">Total de Intereses por Mes:</label>
                <input type="number" step="0.01" name="interesMes" required class="form-control calcular" style="background: lightgray" id="interesMes" readonly>
            </div>
        </div>
</div>
<br>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="totalIntereses" class="required">Total de Intereses a Pagar:</label>
            <input type="number" step="0.01" name="totalIntereses" required class="form-control calcular" style="background: lightgray" id="totalIntereses" readonly>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="fechaPago">Fecha del Primer Pago:</label>
            <input type="date" class="form-control" name="fechaPago" id="fechaPago" min="{{date('Y-m-d')}}">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="fechaPago">Fecha del Ultimo Pago:</label>
            <input type="text" disabled class="form-control" id="fechaUltimoPago">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="fechaPago">Tipo de Préstamo:</label>
            {{html()->select('tipo_prestamo',[''=>'-- Seleccione --'] +tipoprestamos())->class('form-control')->required()->id('selTipoPrestamo')}}
        </div>
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="fechaPago">Tipo Destino:</label>
            {{html()->select('tipo_destino',[''=>'-- Seleccione --'] +destinoPrestamo())->class('form-control')->required()->id('selTipoDestino')}}
        </div>
    </div>

    <div class="col-md-3" id="divDiasPreferidos" style="display: none;">
        <div class="form-group">
            <label for="dia_pago_preferido" id="labelDiasPreferidos">Día Preferido (Quincenal): <small><b>Día del mes (1-31)</b></small></label>
            {{html()->number('dia_pago_preferido')->class('form-control')->id('dia_pago_preferido')->attributes(['min'=>1,'max'=>31])}}
        </div>
    </div>

    <div class="col-md-3" id="divDiaSemanaPreferido" style="display: none;">
        <div class="form-group">
            <label for="diaSemanaPreferido" id="labelDiaSemana">Día de la Semana: <small><b>Para pagos semanales</b></small></label>
            {{html()->select('diaSemanaPreferido',[''=>'-- Seleccione --','1'=>'Lunes','2'=>'Martes','3'=>'Miércoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sábado'])->class('form-control')->id('diaSemanaPreferido')}}
        </div>
    </div>
</div>
{{--<br>--}}
{{--<div class="row">--}}
{{--    <div class="col-md-4">--}}
{{--        <label for="fechaPago">Mora:</label>--}}
{{--        <div class="input-group mb-3">--}}
{{--            <label class="input-group-text" for="moraTipo">Tipo Mora:</label>--}}
{{--            <select required class="form-select" name="moraTipo" id="moraTipo">--}}
{{--                <option value="" selected>*Seleccione*</option>--}}
{{--                <option value="1">Valor Fijo</option>--}}
{{--                <option value="2">Valor Porcentaje</option>--}}
{{--            </select>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="col-md-4">--}}
{{--        <label for="moraTipo">Valor Aplicar:</label>--}}
{{--        <input type="number" min="0" placeholder="Ingrese el valor de la mora" class="form-control" step="1" name="monto_mora">--}}
{{--    </div>--}}
{{--    <div class="col-md-4">--}}
{{--        <label for="moraTipo">Maximo dias Aplicar Mora</label>--}}
{{--        <input type="number" min="0" placeholder="Ingrese lo dias para aplicar mora" class="form-control" step="1" name="dias_mora">--}}
{{--    </div>--}}
{{--</div>--}}

<br>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="comentarios">Comentarios del Préstamo:</label>
            <textarea name="comentarios" id="comentarios" cols="30" rows="3" class="form-control"></textarea>
        </div>
    </div>
</div>
<br>


