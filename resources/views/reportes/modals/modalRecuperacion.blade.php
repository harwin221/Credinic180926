<!-- Modal Recuperación -->
<div class="modal fade" id="modalRecuperacion" tabindex="-1" aria-labelledby="modalRecuperacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRecuperacionLabel"><i class="fas fa-chart-line"></i> Detalle de Recuperación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.detalleColocacion.html'))->id('formRecuperacion')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-3 mb-3">
                        <label for="rec_desde"><strong>Fecha Desde:</strong></label>
                        <input type="date" name="desde" class="form-control" id="rec_desde" required>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label for="rec_hasta"><strong>Fecha Hasta:</strong></label>
                        <input type="date" name="hasta" class="form-control" id="rec_hasta" required>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label><strong>Cobrador:</strong></label>
                        <div class="border rounded p-2" style="max-height:160px;overflow-y:auto;">
                            @foreach(isset($listaCobradores) ? $listaCobradores : [] as $key => $nombre)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cobrador[]" value="{{$key}}" id="rec_cob_{{$loop->index}}">
                                <label class="form-check-label" for="rec_cob_{{$loop->index}}">{{$nombre}}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label for="rec_tipo"><strong>Tipo:</strong></label>
                        {{html()->select('tipo', [''=>'Todos','0'=>'Ordinarios','1'=>'Deducciones','2'=>'Dispensas'], '')->class('form-control')->id('rec_tipo')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label><strong>Tipo de Vista:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_vista" id="rec_vista_detallada" value="detallado" checked>
                            <label class="form-check-label" for="rec_vista_detallada">Detallado</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_vista" id="rec_vista_resumida" value="resumido">
                            <label class="form-check-label" for="rec_vista_resumida">Resumido</label>
                        </div>
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
