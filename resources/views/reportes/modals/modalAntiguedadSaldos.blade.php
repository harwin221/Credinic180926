<!-- Modal Antigüedad de Saldos -->
<div class="modal fade" id="modalAntiguedadSaldos" tabindex="-1" aria-labelledby="modalAntiguedadSaldosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAntiguedadSaldosLabel"><i class="fas fa-hourglass-half"></i> Clasificación CONAMI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.antiguedad_saldos.html'))->id('formAntiguedadSaldos')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label for="as_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [0=>'Todos'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('as_cliente')->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="as_cobrador"><strong>Cobrador:</strong></label>
                        {{html()->select('cobrador', [0=>'Todos'] + (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2')->id('as_cobrador')->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="as_frecuencia"><strong>Frecuencia:</strong></label>
                        {{html()->select('frecuencia', ['0'=>'Todos','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'], '0')->class('form-control')->id('as_frecuencia')}}
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
