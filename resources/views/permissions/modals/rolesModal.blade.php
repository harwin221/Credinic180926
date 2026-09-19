<div class="modal fade" id="modalRole" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('roles.store'))->id('frmRol')->open()}}
            @method('POST')
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Rol</h5>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="" class="required">Rol:</label>
                        <input type="text" id="name" class="form-control" name="name" required>
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
