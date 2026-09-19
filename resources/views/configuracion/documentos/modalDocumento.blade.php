<div class="modal fade" id="modalDocumento" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('configuracion.documentos.store'))->id('frmDocumento')->open()}}
            @method('PUT')
            <div class="modal-header">
                <h4 class="modal-title" id="modelTitleId">Nuevo Documento</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{html()->label('Nombre','nombre')->class('required')}}
                    {{html()->text('nombre')->id('nombre')->class(['form-control','text-uppercase'])->id('nombre')->required()}}
                </div>
                <br>
                <div class="form-group">
                    {{html()->label('Tipo','tipo')}}
                    {{html()->select('tipo',['1'=>'Foto','0'=>'Documento'])->class('form-control')->id('tipo')->required()}}
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
