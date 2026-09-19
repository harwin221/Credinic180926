<div class="modal fade" id="modalPermisos" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('permisos.store'))->id('frmPermiso')->open()}}
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Permiso</h5>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name" class="required">Permiso:</label>
                        <input type="text" id="name" name="name" required class="form-control">
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="description" class="required">Descripción:</label>
                        <input type="text" id="description" name="description" required class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
