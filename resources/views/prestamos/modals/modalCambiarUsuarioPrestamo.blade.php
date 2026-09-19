<div class="modal fade" id="modalUsuario" data-bs-backdrop="static" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('prestamo.actualizar.usuario',$prestamo->id_enc))->open()}}
            <input type="hidden" value="vendedor" name="tipo" id="txtTipoUsuario">
            <div class="modal-header">
                <h4 class="modal-title">Cambiar Vendedor</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    {{html()->select('usuario',[''=>'*Seleccione*']+$vendedores)->class('form-control select2')->style('width:100%')->required()->id('selectUsuario')}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>


<div class="modal fade" id="modalUsuarioCobrador" data-bs-backdrop="static" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('prestamo.actualizar.usuario',$prestamo->id_enc))->open()}}
            <input type="hidden" value="cobrador" name="tipo" id="txtTipoUsuario">
            <div class="modal-header">
                <h4 class="modal-title">Cambiar Cobrador</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    {{html()->select('usuario',[''=>'*Seleccione*']+$cobradores)->class('form-control select2')->style('width:100%')->required()->id('selectUsuario')}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
