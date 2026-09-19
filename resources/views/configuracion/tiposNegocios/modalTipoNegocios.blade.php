<div class="modal fade" id="modalTipoNegocio" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('configuracion.tiposNegocios.store'))->id('frmTipoNegocio')->open()}}
            @method('PUT')
            <div class="modal-header">
                <h4 class="modal-title" id="modelTitleId">Nuevo Tipo Negocio</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{html()->label('Nombre','nombre')->class('required')}}
                    {{html()->text('nombre')->id('nombre')->class(['form-control','text-uppercase'])->id('nombre')->required()}}
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
