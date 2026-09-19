<!-- Modal Abonos -->
<div class="modal fade" id="modalAbonos" tabindex="-1" aria-labelledby="modalAbonosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAbonosLabel"><i class="fas fa-money-bill-wave"></i> Reporte de Abonos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.listaCuotas.html'))->id('formAbonos')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label for="fecha_desde"><strong>Fecha Desde:</strong></label>
                        <input type="date" value="{{\Carbon\Carbon::now()->startOfMonth()->toDateString()}}" id="fecha_desde" name="desde" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="fecha_hasta"><strong>Fecha Hasta:</strong></label>
                        <input type="date" value="{{\Carbon\Carbon::now()->toDateString()}}" id="fecha_hasta" name="hasta" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="cobrador_abono"><strong>Cobrador:</strong></label>
                        {{html()->select('cobrador', [0=>'Todos'] + (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2')->id('cobrador_abono')->style('width: 100%')}}
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
