<!-- Modal Estado Clientes -->
<div class="modal fade" id="modalEstadoClientes" tabindex="-1" aria-labelledby="modalEstadoClientesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstadoClientesLabel"><i class="fas fa-users"></i> Clientes Inactivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.estadoClientes.html'))->id('formEstadoClientes')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="ec_cobrador"><strong>Cobrador / Gestor:</strong></label>
                        {{html()->select('cobrador[]', (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2-multiple')->id('ec_cobrador')->multiple()->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="ec_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [0=>'Todos'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('ec_cliente')->style('width: 100%')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="ec_estado"><strong>Estado:</strong></label>
                        {{html()->select('estado', [0=>'Todos', 1=>'Con Préstamos Activos', 2=>'Sin Préstamos Activos'], 2)->class('form-control')->id('ec_estado')}}
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
