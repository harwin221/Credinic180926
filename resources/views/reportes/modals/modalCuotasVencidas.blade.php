<!-- Modal Cuotas Vencidas -->
<div class="modal fade" id="modalCuotasVencidas" tabindex="-1" aria-labelledby="modalCuotasVencidasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCuotasVencidasLabel"><i class="fas fa-exclamation-triangle"></i> Cuotas Vencidas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.cuotasVencidas.html'))->id('formCuotasVencidas')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-3 mb-3">
                        <label for="cv_desde"><strong>Fecha Desde:</strong></label>
                        <input type="date" name="desde" class="form-control" id="cv_desde">
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label for="cv_hasta"><strong>Fecha Hasta:</strong></label>
                        <input type="date" name="hasta" class="form-control" id="cv_hasta">
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label for="cv_cobrador"><strong>Cobrador:</strong></label>
                        {{html()->select('cobrador[]', (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2-multiple')->id('cv_cobrador')->multiple()->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label for="cv_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [0=>'Todos'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('cv_cliente')->style('width: 100%')}}
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
