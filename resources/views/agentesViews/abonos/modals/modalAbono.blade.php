<div class="modal fade" id="modalAbono" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('agentes.abonos.storeAbono'))->id('frmAbono')->open()}}
            <input type="hidden" name="cuotas[]" id="txtAbonoId">
            <div class="modal-header">
                <h5 class="modal-title">Realizar Abono</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group text-center col-md-6">
                        <label for="" class="fw-bold">Fecha Cuota:</label>
                        <input type="text" class="form-control text-center" readonly id="dtFechaCuota">
                    </div>

                    <div class="form-group text-center col-md-6">
                        <label for="" class="fw-bold">Fecha Realizado:</label>
                        <input type="text" class="form-control text-center" readonly value="{{date('Y-m-d')}}">
                    </div>
                </div>
                <br>
                <div class="input-group">
                    <div class="input-group mb-3">
                        <div class="input-group-text">
                            <input class="form-radio-input mt-0" id="rbCuota" name="tipoPago" type="radio" value="1" aria-label="Checkbox for following text input">
                        </div>
                        <label for="rbCuota" class="ms-2 form-control">Pago Cuota:</label>
                        <input type="text" name="rbCuota" disabled id="txtPagoCuota" class="form-control text-center fw-bold">
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-group mb-3">
                        <div class="input-group-text">
                            <input class="form-radio-input mt-0" id="rbOtroMonto" name="tipoPago" type="radio" value="3" aria-label="Checkbox for following text input">
                        </div>
                        <label for="rbOtroMonto" class="ms-2 form-control">Otro Monto:</label>
                        <input type="number" name="rbOtroMonto" id="txtOtroMonto" disabled min="0" step="0.01" class="form-control text-center fw-bold">
                    </div>
                </div>
                <div class="form-control">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <b>Total a pagar:</b><br>
                            <span id="totalPagar" style="font-size: 18px;color: blue;font-weight: bold"></span>
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
                    <div class="row justify-content-center">
                        <div class="col-12 col-sm-4 text-center">
                            <div class="input-group-text">
                                <input class="form-radio-input mt-0" id="rbEfectivo" name="formaPago" type="checkbox" value="1" aria-label="Checkbox for following text input">
                                <label for="rbEfectivo" class="ms-2">EFECTIVO</label>
                            </div>
                        </div>

{{--                        <div class="col-12 col-sm-3 text-center">--}}
{{--                            <div class="input-group-text">--}}
{{--                                <input class="form-radio-input mt-0" id="rbTarjeta" name="formaPago" type="checkbox" value="2" aria-label="Checkbox for following text input">--}}
{{--                                <label for="rbTarjeta" class="ms-2">TARJETA</label>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <div class="col-12 col-sm-3 text-center">--}}
{{--                            <div class="input-group-text">--}}
{{--                                <input class="form-radio-input mt-0" id="rbTransferencia" name="formaPago" type="checkbox" value="3" aria-label="Checkbox for following text input">--}}
{{--                                <label for="rbTransferencia" class="ms-2">TRANSFERENCIA</label>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <div class="col-12 col-sm-3 text-center">--}}
{{--                            <div class="input-group-text">--}}
{{--                                <input class="form-radio-input mt-0" id="rbCheque" name="formaPago" type="checkbox" value="4" aria-label="Checkbox for following text input">--}}
{{--                                <label for="rbCheque" class="ms-2">CHEQUE</label>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>
                    <br>
                    <div class="row justify-content-center">
                        <div class="col-12 col-sm-4 text-center">
                            <input class="form-control" data-name="rbEfectivo" placeholder="Ingrese el monto en efectivo" id="efectivoMonto" name="efectivoMonto" type="number" disabled min="1">
                        </div>

{{--                        <div class="col-12 col-sm-3 text-center">--}}
{{--                            <input class="form-control" data-name="rbTarjeta" placeholder="Ingrese el monto de tarjeta" id="tarjetaMonto" name="tarjetaMonto" type="number" disabled min="1">--}}
{{--                        </div>--}}

{{--                        <div class="col-12 col-sm-3 text-center">--}}
{{--                            <input class="form-control" data-name="rbTransferencia" placeholder="Ingrese el monto de transferencia" id="transferenciaMonto" name="transferenciaMonto" type="number" disabled min="1">--}}
{{--                        </div>--}}

{{--                        <div class="col-12 col-sm-3 text-center">--}}
{{--                            <input class="form-control" data-name="rbCheque" placeholder="Ingrese el monto del cheque" id="chequeMonto" name="chequeMonto" type="number" disabled min="1">--}}
{{--                        </div>--}}
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="observaciones" class="fw-bold">Observaciones:</label>
                            <textarea class="form-control" name="observaciones" rows="2" placeholder="Observaciones del abono" id="txtObservaciones"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnRealizarAbono">Abonar</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
