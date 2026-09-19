<!-- Modal Cobros del Día -->
<div class="modal fade" id="modalCobrosDia" tabindex="-1" aria-labelledby="modalCobrosDiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCobrosDiaLabel"><i class="fas fa-calendar-day"></i> Cobros del Día</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.cobrosDia.html'))->id('formCobrosDia')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label for="cd_fecha"><strong>Fecha Desde:</strong></label>
                        <input type="date" value="{{\Carbon\Carbon::now()->toDateString()}}" id="cd_fecha" name="fecha" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="cd_fecha2"><strong>Fecha Hasta:</strong></label>
                        <input type="date" value="{{\Carbon\Carbon::now()->toDateString()}}" id="cd_fecha2" name="fecha2" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="cd_cobrador"><strong>Cobrador:</strong></label>
                        {{html()->select('cobrador', [0=>'Todos'] + (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2')->id('cd_cobrador')->style('width: 100%')}}
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
