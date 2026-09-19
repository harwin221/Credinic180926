<div class="modal fade" id="modalotroMontoAbono" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('abonos.abonarPrestamo'))->id('frmAbonoAbonoPrestamo')->open()}}
            <div class="modal-header">
                <h5 class="modal-title">Abonar al Préstamo</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group text-center col-md-6">
                        <label for="" class="fw-bold">Fecha Realizado:</label>
                        <input type="text" class="form-control text-center" readonly value="{{date('Y-m-d')}}">
                    </div>
                </div>
                <br>
                <div class="input-group">
                    <div class="input-group mb-3">
                        <div class="input-group-text">
                            <input class="form-radio-input mt-0" id="rbCuotaAbonoPrestamo" name="tipoPago" type="radio" value="1" aria-label="Checkbox for following text input">
                        </div>
                        <label for="rbCuotaAbonoPrestamo" class="ms-2 form-control">Abonar Total Monto Pendiente:</label>
                        <input type="text" name="rbCuota" disabled id="txtPagoCuotaAbonoPrestamo" class="form-control text-center fw-bold">
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-group mb-3">
                        <div class="input-group-text">
                            <input class="form-radio-input mt-0" id="rbOtroMontoAbonoPrestamo" name="tipoPago" type="radio" value="3" aria-label="Checkbox for following text input">
                        </div>
                        <label for="rbOtroMontoAbonoPrestamo" class="ms-2 form-control">Abonar Otro Monto:</label>
                        <input type="number" name="rbOtroMonto" id="txtOtroMontoAbonoPrestamo" disabled min="0" step="0.01" class="form-control text-center fw-bold">
                    </div>
                </div>
                <div class="form-control">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <b>Total a pagar:</b><br>
                            <span id="totalPagarAbonoPrestamo" style="font-size: 18px"></span>
                        </div>
                    </div>
                </div>
                <br>
                <div class="form-control text-center">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="fw-bold">Forma de Pago:</label>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col text-center">
                            <div class="input-group-text">
                                <input class="form-radio-input mt-0" id="rbEfectivoAbonoPrestamo" name="formaPago" type="checkbox" value="1" aria-label="Checkbox for following text input">
                                <label for="rbEfectivoAbonoPrestamo" class="ms-2">EFECTIVO</label>
                            </div>
                        </div>

                        <div class="col text-center">
                            <div class="input-group-text">
                                <input class="form-radio-input mt-0" id="rbTarjetaAbonoPrestamo" name="formaPago" type="checkbox" value="2" aria-label="Checkbox for following text input">
                                <label for="rbTarjetaAbonoPrestamo" class="ms-2">TARJETA</label>
                            </div>
                        </div>

                        <div class="col text-center">
                            <div class="input-group-text">
                                <input class="form-radio-input mt-0" id="rbTransferenciaAbonoPrestamo" name="formaPago" type="checkbox" value="3" aria-label="Checkbox for following text input">
                                <label for="rbTransferenciaAbonoPrestamo" class="ms-2">TRANSFERENCIA</label>
                            </div>
                        </div>

                        <div class="col text-center">
                            <div class="input-group-text">
                                <input class="form-radio-input mt-0" id="rbChequeAbonoPrestamo" name="formaPago" type="checkbox" value="4" aria-label="Checkbox for following text input">
                                <label for="rbChequeAbonoPrestamo" class="ms-2">CHEQUE</label>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col text-center">
                            <input class="form-control" data-name="rbEfectivoAbonoPrestamo" id="efectivoMontoAbonoPrestamo" name="efectivoMontoAbonoPrestamo" type="number" disabled min="1">
                        </div>

                        <div class="col text-center">
                            <input class="form-control" data-name="rbTarjetaAbonoPrestamo" id="tarjetaMontoAbonoPrestamo" name="tarjetaMontoAbonoPrestamo" type="number" disabled min="1">
                        </div>

                        <div class="col text-center">
                            <input class="form-control" data-name="rbTransferenciaAbonoPrestamo" id="transferenciaMontoAbonoPrestamo" name="transferenciaMontoAbonoPrestamo" type="number" disabled min="1">
                        </div>

                        <div class="col text-center">
                            <input class="form-control" data-name="rbChequeAbonoPrestamo" id="chequeMontoAbonoPrestamo" name="chequeMontoAbonoPrestamo" type="number" disabled min="1">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="observaciones" class="fw-bold">Observaciones:</label>
                            <textarea class="form-control" name="observacionesAbonoPrestamo" rows="2" placeholder="Observaciones del abono" id="txtObservacionesAbonoPrestamo"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnRealizarAbonoAbonoPrestamo">Abonar</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
