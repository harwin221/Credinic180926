<!-- Modal Impacto de Feriados -->
<div class="modal fade" id="modalImpactoFeriados" tabindex="-1" aria-labelledby="modalImpactoFeriadosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImpactoFeriadosLabel"><i class="fas fa-calendar-check"></i> Impacto de Feriados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.impactoFeriados.html'))->id('formImpactoFeriados')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="if_cobrador"><strong>Cobrador:</strong></label>
                        {{html()->select('cobrador', [0=>'Todos'] + (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2')->id('if_cobrador')->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="if_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [0=>'Todos'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('if_cliente')->style('width: 100%')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="if_estado"><strong>Estado del Préstamo:</strong></label>
                        {{html()->select('estado', [
                            0  => 'Todos',
                            '1' => 'Activo',
                            '2' => 'Cancelado',
                            '3' => 'Vencido'
                        ], '1')->class('form-control')->id('if_estado')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="if_frecuencia"><strong>Frecuencia:</strong></label>
                        {{html()->select('frecuencia', [
                            0  => 'Todas',
                            '1' => 'Diario',
                            '2' => 'Semanal',
                            '3' => 'Quincenal',
                            '4' => 'Mensual',
                            '7' => 'Catorcenal'
                        ], null)->class('form-control')->id('if_frecuencia')}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" formtarget="_blank">
                    <i class="fas fa-search"></i> Generar Reporte
                </button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
