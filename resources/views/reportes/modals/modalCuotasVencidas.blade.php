<!-- Modal Cuotas Vencidas -->
<div class="modal fade" id="modalCuotasVencidas" tabindex="-1" aria-labelledby="modalCuotasVencidasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCuotasVencidasLabel"><i class="fas fa-exclamation-triangle"></i> Cuotas Vencidas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.cuotasVencidas.html'))->id('formCuotasVencidas')->open()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-3 mb-3">
                        <label for="cv_desde"><strong>Fecha Desde:</strong></label>
                        <input type="date" name="desde" class="form-control" id="cv_desde">
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label for="cv_hasta"><strong>Fecha Hasta:</strong></label>
                        <input type="date" name="hasta" class="form-control" id="cv_hasta">
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label><strong>Cobrador:</strong></label>
                        <div class="border rounded p-2" style="max-height:160px;overflow-y:auto;">
                            @foreach(isset($listaCobradores) ? $listaCobradores : [] as $key => $nombre)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cobrador[]" value="{{$key}}" id="cv_cob_{{$loop->index}}">
                                <label class="form-check-label" for="cv_cob_{{$loop->index}}">{{$nombre}}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label for="cv_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [0=>'Todos'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('cv_cliente')->style('width: 100%')}}
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
