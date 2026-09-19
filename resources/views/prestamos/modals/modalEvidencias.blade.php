<div class="modal fade" id="modalEvidencias" data-bs-backdrop="static" role="dialog" aria-labelledby="modelTitleId"
     aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('prestamos.represtamo.store',$prestamo->id_enc))->open()}}
            <div class="modal-header">
                <h4 class="modal-title">Evidencias Desembolso</h4>
            </div>
            <div class="modal-body">
                <div class="col-md-12">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar Représtamo</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
