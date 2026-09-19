<div class="modal fade" id="modalNuevoNegocio" data-bs-backdrop="static" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Negocio del Cliente</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="modalNombreNegocio" class="required">Nombre:</label>
                    <input type="text" id="modalNombreNegocio" class="form-control form-control-sm" required>
                </div>
                <br>
                <div class="form-group">
                    <label for="modalDpMunNegocio" class="required">Departamento / Municipio:</label>
                    {{html()->select('municipio_id',[""=>'* Seleccione *']+departamento_municipios())->class('form-control')->id('modalDpMunNegocio')}}
                </div>
                <br>
                <div class="form-group">
                    <label for="modalUbicacionNegocio" class="required">Ubicación Geográfica:</label>
                    <input type="text" id="modalUbicacionNegocio" class="form-control form-control-sm" required>
                </div>
                <br>
                <div class="form-group">
                    <label for="modalTelefonoNegocio" class="required">Teléfono del Negocio:</label>
                    <input type="text" id="modalTelefonoNegocio" class="form-control form-control-sm" required>
                </div>
                <br>
                <div class="form-group">
                    <label for="modalDireccionNegocio" class="required">Dirección:</label>
                    <textarea class="form-control-sm form-control" id="modalDireccionNegocio"></textarea>
                </div>

                <div class="form-group">
                    <label for="modalComentarioNegocio" class="required">Comentarios:</label>
                    <textarea class="form-control-sm form-control" id="modalComentarioNegocio"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelButtonNegocio" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmarButtonNegocio" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </div>
</div>
