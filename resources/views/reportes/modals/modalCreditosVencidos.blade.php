<div class="modal fade" id="modalCreditosVencidos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: #00acc1; color: white;">
                <h5 class="modal-title" id="exampleModalLabel text-white">
                    <i class="fas fa-calendar-times mr-2"></i> Reporte de Créditos Vencidos (Total)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.creditosVencidos.html'))->open()}}
            <div class="modal-body p-4">
                <p class="text-muted mb-4 small">Este reporte identifica créditos que han llegado a su fecha de vencimiento final según el plan de pago.</p>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="desde" class="form-label font-weight-bold">Vencimiento desde:</label>
                        <input type="date" name="desde" class="form-control rounded-pill" id="desde">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="hasta" class="form-label font-weight-bold">Vencimiento hasta:</label>
                        <input type="date" name="hasta" class="form-control rounded-pill" value="{{date('Y-m-d')}}" id="hasta">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="cobrador" class="form-label font-weight-bold">Cobrador:</label>
                        {{html()->select('cobrador[]', $listaCobradores, null)->class('form-control select2-multiple redonde-pill')->multiple()->style(['width' => '100%'])}}
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: #00acc1; border: none;" formtarget="_blank">
                    <i class="fas fa-eye mr-1"></i> Generar HTML
                </button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
