<div class="modal fade" id="modalColocacionRecuperacion" tabindex="-1" aria-labelledby="modalColocacionRecuperacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1f9cb5; color: white;">
                <h5 class="modal-title" id="modalColocacionRecuperacionLabel">
                    <i class="fas fa-balance-scale me-2"></i> Colocación vs Recuperación
                </h5>
                <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('reportes.colocacionVsRecuperacion.html') }}" method="GET" target="_blank">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Desde</label>
                            <input type="date" name="desde" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hasta</label>
                            <input type="date" name="hasta" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Agente (opcional)</label>
                            <div class="border rounded p-2" style="max-height:160px;overflow-y:auto;">
                                @foreach($listaCobradores as $key => $nombre)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="cobrador[]" value="{{$key}}" id="col_cob_{{$loop->index}}">
                                    <label class="form-check-label" for="col_cob_{{$loop->index}}">{{$nombre}}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-bar me-1"></i> Generar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalColocacion = document.getElementById('modalColocacionRecuperacion');
    modalColocacion.addEventListener('shown.bs.modal', function () {
        if ($('#selectAgenteColocacion').hasClass('select2-hidden-accessible')) {
            $('#selectAgenteColocacion').select2('destroy');
        }
        $('#selectAgenteColocacion').select2({
            dropdownParent: $('#modalColocacionRecuperacion'),
            width: '100%'
        });
    });
    modalColocacion.addEventListener('hidden.bs.modal', function () {
        if ($('#selectAgenteColocacion').hasClass('select2-hidden-accessible')) {
            $('#selectAgenteColocacion').select2('destroy');
        }
    });
});
</script>
