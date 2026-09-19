<!-- Modal Cartera Diaria -->
<div class="modal fade" id="modalCarteraDiaria" tabindex="-1" aria-labelledby="modalCarteraDiariaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCarteraDiariaLabel"><i class="fas fa-briefcase"></i> Cartera Diaria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.cartera_diaria.html'))->id('formCarteraDiaria')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="ctd_cobrador"><strong>Cobrador:</strong></label>
                        {{html()->select('cobrador', (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2')->id('ctd_cobrador')->required()->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="ctd_fecha"><strong>Fecha:</strong></label>
                        <input type="date" name="fecha" class="form-control" id="ctd_fecha" required>
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
