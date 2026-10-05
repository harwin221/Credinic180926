<!-- Modal Cartera Vencida Histórica -->
<div class="modal fade" id="modalCarteraVencidaHistorica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history"></i> Cartera Vencida Histórica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            {{html()->form('GET', route('reportes.carteraVencidaHistorica.html'))->open()}}
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle text-primary"></i>
                    Créditos cuyo plazo ya venció con saldo pendiente, agrupados por <strong>vendedor original</strong>.
                    Saldo inicio / cobro del período / saldo final.
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label><strong>Período desde:</strong></label>
                        <input type="date" name="desde" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label><strong>Período hasta:</strong></label>
                        <input type="date" name="hasta" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label><strong>Vendedor (gestor original):</strong></label>
                        {{ html()->select('vendedor[]', ['' => 'Todos'] + (isset($listaCobradores) ? $listaCobradores : []), null)
                            ->class('form-control select2')->style('width:100%') }}
                    </div>
                    <div class="col-md-6">
                        <label><strong>Cobrador actual:</strong></label>
                        {{ html()->select('cobrador[]', ['' => 'Todos'] + (isset($listaCobradores) ? $listaCobradores : []), null)
                            ->class('form-control select2')->style('width:100%') }}
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
