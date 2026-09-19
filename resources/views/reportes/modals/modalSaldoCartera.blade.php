<!-- Modal Saldo Cartera -->
<div class="modal fade" id="modalSaldoCartera" tabindex="-1" aria-labelledby="modalSaldoCarteraLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSaldoCarteraLabel"><i class="fas fa-wallet"></i> Saldo de Cartera</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.saldoCartera'))->id('formSaldoCartera')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="sc_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [0=>'Todos'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('sc_cliente')->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="sc_cobrador"><strong>Cobrador:</strong></label>
                        {{html()->select('cobrador[]', (isset($listaCobradores) ? $listaCobradores : []), null)->class('form-control select2-multiple')->id('sc_cobrador')->multiple()->style('width: 100%')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label for="sc_frecuencia"><strong>Frecuencia:</strong></label>
                        {{html()->select('frecuencia', ['0'=>'Todos','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'], '0')->class('form-control')->id('sc_frecuencia')}}
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="sc_estado"><strong>Estado del Préstamo:</strong></label>
                        {{html()->select('estado', ['0'=>'Todos','1'=>'Pendientes / Activos','2'=>'Cancelados','3'=>'Vencidos','4'=>'Anulados','5'=>'Saneados'], '0')->class('form-control')->id('sc_estado')}}
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label for="sc_fecha_fin"><strong>Fecha de Corte:</strong></label>
                        <input type="date" id="sc_fecha_fin" name="fin" required value="{{\Carbon\Carbon::now()->toDateString()}}" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 mb-2">
                        <label for="sc_tipo_vista"><strong>Tipo de Vista:</strong></label>
                        <select name="tipo_vista" id="sc_tipo_vista" class="form-control">
                            <option value="detallado">📋 Detallado — todos los préstamos por cobrador</option>
                            <option value="resumido">📊 Resumido — resumen por cobrador</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-2 d-flex align-items-end">
                        <div class="alert alert-info mb-0 w-100 py-2" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <small>No se recomienda usar intervalos de fechas muy grandes</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="html" value="html" class="btn btn-info" formtarget="_blank">
                    <i class="fas fa-table"></i> Ver Reporte HTML
                </button>
                <button type="submit" name="pdf" value="pdf" class="btn btn-primary" formtarget="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button type="submit" name="excel" value="excel" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
