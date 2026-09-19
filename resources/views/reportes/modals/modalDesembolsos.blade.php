<!-- Modal Desembolsos -->
<div class="modal fade" id="modalDesembolsos" tabindex="-1" aria-labelledby="modalDesembolsosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDesembolsosLabel"><i class="fas fa-hand-holding-usd"></i> Reporte de Desembolsos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.listaClientes.html'))->id('formDesembolsos')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="des_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [0=>'Todos'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('des_cliente')->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label><strong>Cobrador:</strong></label>
                        <div class="border rounded p-2" style="max-height:160px;overflow-y:auto;">
                            @foreach(isset($listaCobradores) ? $listaCobradores : [] as $key => $nombre)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cobrador[]" value="{{$key}}" id="des_cob_{{$loop->index}}">
                                <label class="form-check-label" for="des_cob_{{$loop->index}}">{{$nombre}}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="des_vendedor"><strong>Vendedor:</strong></label>
                        {{html()->select('vendedor', [0=>'Todos'] + (isset($listaVendedores) ? $listaVendedores : []), null)->class('form-control select2')->id('des_vendedor')->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="des_estado"><strong>Estado del Préstamo:</strong></label>
                        {{html()->select('estado', ['1'=>'Pendientes / Activos','2'=>'Cancelados','3'=>'Vencidos','4'=>'Anulados'], '1')->class('form-control')->id('des_estado')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label for="des_frecuencia"><strong>Frecuencia:</strong></label>
                        {{html()->select('frecuencia', ['0'=>'Todos','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'], '0')->class('form-control')->id('des_frecuencia')}}
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="des_fecha_inicio"><strong>Fecha Desde:</strong></label>
                        <input type="date" id="des_fecha_inicio" name="fecha_inicio" value="{{\Carbon\Carbon::now()->toDateString()}}" class="form-control">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="des_fecha_fin"><strong>Fecha Hasta:</strong></label>
                        <input type="date" id="des_fecha_fin" name="fecha_fin" value="{{\Carbon\Carbon::now()->toDateString()}}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" formtarget="_blank"><i class="fas fa-search"></i> Generar Reporte</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
