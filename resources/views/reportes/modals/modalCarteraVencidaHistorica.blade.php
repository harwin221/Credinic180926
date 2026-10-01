<!-- Modal Cartera Vencida Histórica -->
<div class="modal fade" id="modalCarteraVencidaHistorica" tabindex="-1" aria-labelledby="modalCarteraVencidaHistoricaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCarteraVencidaHistoricaLabel">
                    <i class="fas fa-history"></i> Cartera Vencida Histórica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.carteraVencidaHistorica.html'))->id('formCarteraVencidaHistorica')->open()}}
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle text-primary"></i>
                    Muestra créditos vencidos con el <strong>gestor original</strong> que los tenía asignados,
                    saldo al momento del vencimiento/reasignación y saldo actual. Ideal para cálculo de comisiones.
                </p>
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label><strong>Vencimiento desde:</strong></label>
                        <input type="date" name="desde" class="form-control">
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label><strong>Vencimiento hasta:</strong></label>
                        <input type="date" name="hasta" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label><strong>Gestor (original):</strong></label>
                        {{ html()->select('cobrador[]', ['' => 'Todos los gestores'] + (isset($listaCobradores) ? $listaCobradores : []), null)
                            ->class('form-control select2')
                            ->style('width: 100%') }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="excel" value="1" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button type="submit" class="btn btn-primary" formtarget="_blank">
                    <i class="fas fa-eye"></i> Ver Reporte
                </button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
