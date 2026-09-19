<!-- Modal Cobranza -->
<div class="modal fade" id="modalCobranza" tabindex="-1" aria-labelledby="modalCobranzaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCobranzaLabel"><i class="fas fa-coins"></i> Cobros sin abono</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.cobranza.html'))->id('formCobranza')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label for="cob_fecha_inicio"><strong>Fecha Inicio:</strong></label>
                        <input type="date" name="fecha_inicio" class="form-control" id="cob_fecha_inicio" required>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="cob_fecha_fin"><strong>Fecha Fin:</strong></label>
                        <input type="date" name="fecha_fin" class="form-control" id="cob_fecha_fin" required>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="cob_cobrador"><strong>Cobrador:</strong></label>
                        {{html()->select('cobrador', [0=>'Todos'] + (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2')->id('cob_cobrador')->style('width: 100%')}}
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
