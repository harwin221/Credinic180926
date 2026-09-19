<div class="modal fade" id="modalNuevoFiador" data-bs-backdrop="static" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Fiador del Cliente</h4>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modalNombreFiador" class="required">Nombres:</label>
                            <input type="text" id="modalNombreFiador" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modalApellidosFiador" class="required">Apellidos:</label>
                            <input type="text" id="modalApellidosFiador" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="modalCedulaFiador" class="required">Cédula:</label>
                            <input type="text" id="modalCedulaFiador" required class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modalTelefonoFiador">Teléfono 1:</label>
                            <input type="text" id="modalTelefono1Fiador" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modalTelefono2Fiador">Teléfono 2:</label>
                            <input type="text" id="modalTelefono2Fiador" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="modalDepartamentoFiador">Departamento / Municipio</label>
                            {{html()->select('municipio_id',[""=>'* Seleccione *']+departamento_municipios())->class('form-control')->id('modalDepartamentoFiador')}}
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="modalDireccionFiador">Dirección</label>
                            <textarea class="form-control-sm form-control" id="modalDireccionFiador"></textarea>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="modalObservacionesFiador">Observaciones</label>
                            <textarea class="form-control-sm form-control" id="modalObservacionesFiador"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelButtonFiador" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmarButtonFiador" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </div>
</div>
