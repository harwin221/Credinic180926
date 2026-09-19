<div class="modal fade" id="modalReprestamo" data-bs-backdrop="static" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('prestamos.represtamo.store',$prestamo->id_enc))->open()}}
            <div class="modal-header">
                <h4 class="modal-title">Représtamo (TOTAL PENDIENTE: {{number_format($prestamo->pendiente_abono,2)}})</h4>
            </div>
            <div class="modal-body">
                <div class="row text-center" style="background: lightgray;padding: 7px;border-radius: 5px">
                    <div class="col-md-12">
                        <b>Représtamo Total Pendiente:</b><br> {{$prestamo->moneda." ".number_format($prestamo->suma_cuotas - $prestamo->suma_abonos,2)}}
                        <br> <input type="radio" value="1" name="tipo_monto" checked class="rbTipoMonto">
                        <input type="hidden" value="{{$prestamo->suma_cuotas - $prestamo->suma_abonos}}" id="suma_cuotas">
                    </div>
{{--                    <div class="col-md-6">--}}
{{--                        <b>Représtamo Total Pendiente (Capital):</b><br> {{$prestamo->moneda." ".number_format($prestamo->suma_capital,2)}}--}}
{{--                        <br> <input type="radio" value="2" name="tipo_monto" class="rbTipoMonto">--}}
{{--                        <input type="hidden" value="{{$prestamo->suma_capital}}" id="suma_capital">--}}
{{--                    </div>--}}
                </div>
                <br>
                <div class="row">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="realizadoPor">Vendedor:</label>
                                {{html()->select('vendedor',[],null)->class('form-control select2')->style(['width'=>'100%'])->required()->id('selectVendedor')}}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="realizadoPor">Cobrador:</label>
                                {{html()->select('agente',[],null)->class('form-control select2')->style(['width'=>'100%'])->required()->id('selectAgente')}}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="desembolso">Desembolsado Por:</label>
                                {{html()->select('desembolso',[],null)->class('form-control select2')->style(['width'=>'100%'])->required()->id('selectDesembolso')}}
                            </div>
                        </div>
                    </div>
                    <br>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaPrestamo" class="required">Fecha del Préstamo:</label>
                                <input type="date" name="fechaPrestamo" min="{{date('Y-m-d')}}" required value="{{date('Y-m-d')}}" class="form-control" id="fechaPrestamo">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaDesembolso" class="required">Fecha Desembolso:</label>
                                <input type="date" name="fechaDesembolso" min="{{date('Y-m-d')}}" required value="{{date('Y-m-d')}}" class="form-control" id="fechaDesembolso">
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="montoFinanciar">Monto a Financiar:</label>
                                <div class="input-group mb-3">
                <span class="input-group-text">
                    {{html()->select('moneda_prestamo',['1'=>'C$','2'=>'U$'],$prestamo->moneda_prestamo)->id('moneda')->disabled()->required()}}
                    <input type="hidden" value="{{$prestamo->moneda_prestamo}}" name="moneda">
                </span>
                                    <input type="number" name="montoFinanciar" value="{{$prestamo->suma_cuotas - $prestamo->suma_abonos}}" readonly style="background: lightgray" min="1" step="0.001" class="form-control calcular" required aria-label="Monto" id="montoFinanciar">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="formaPago">Forma de Pago:</label>
                                {{html()->select('formaPago',[''=>'* Seleccione *','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal'],$prestamo->forma_pago_tipo)->class('form-control calcular')->id('formaPago')->required()}}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="plazoPago" class="required">Plazo de Pago (Meses):</label>
                                <input type="number" step="0.5" min="0" name="plazoPago" value="{{$prestamo->plazo_pago}}" required class="form-control calcular" id="plazoPago">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tasaInteres">Tasa de Interes: % (Mensual)</label>
                                <input type="number" min="0" step="0.01" required class="form-control calcular" value="{{$prestamo->tasa_prestamo}}" name="tasaInteres" id="tasaInteres">
                            </div>
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="montoTotalFinanciar" class="required">Monto Total a Financiar:</label>
                                <input type="number" name="montoTotalFinanciar" style="background: lightgray" required class="form-control calcular" id="montoTotalFinanciar" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="montoCuota" class="required">Monto de la Cuota</label>
                                <input type="number" name="montoCuota" required class="form-control" style="background: lightgray" id="montoCuota" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="interesPagar" class="required">Intereses a Pagar</label>
                                <input type="text" name="interesPagar" required class="form-control" style="background: lightgray" id="interesPagar" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="interesMes" class="required">Total de Intereses por Mes:</label>
                                <input type="text" name="interesMes" required class="form-control calcular" style="background: lightgray" id="interesMes" readonly>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="totalIntereses" class="required">Total de Intereses a Pagar:</label>
                                <input type="text" name="totalIntereses" required class="form-control calcular" style="background: lightgray" id="totalIntereses" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fechaPago">Fecha del Primer Pago:</label>
                                <input type="date" class="form-control" name="fechaPago" required id="fechaPago" min="{{date('Y-m-d')}}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fechaPago">Fecha del Ultimo Pago:</label>
                                <input type="text" disabled class="form-control" id="fechaUltimoPago">
                            </div>
                        </div>
                        
                        <div class="col-md-3" id="divDiasPreferidos" style="display: none;">
                            <div class="form-group">
                                <label for="diasPreferidos">Siguiente Dia Pago: <small><b>Para Pagos Quincenales</b></small></label>
                                {{html()->number('diasPago')->class('form-control')->id('diasPreferidos')->attributes(['min'=>1])}}
                            </div>
                        </div>
                        
                        <div class="col-md-3" id="divDiaSemanaPreferido" style="display: none;">
                            <div class="form-group">
                                <label for="diaSemanaPreferido" id="labelDiaSemana">Día de la Semana:</label>
                                {{html()->select('diaSemanaPreferido',[''=>'-- Seleccione --','1'=>'Lunes','2'=>'Martes','3'=>'Miércoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sábado'])->class('form-control')->id('diaSemanaPreferido')}}
                            </div>
                        </div>

                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="fechaPago">Mora:</label>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="moraTipo">Tipo Mora:</label>
                                <select required class="form-select" name="moraTipo" id="moraTipo">
                                    <option selected>*Seleccione*</option>
                                    <option value="1">Valor Fijo</option>
                                    <option value="2">Valor Porcentaje</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="moraTipo">Valor Aplicar:</label>
                            <input type="number" min="0" required placeholder="Ingrese el valor de la mora" class="form-control" step="1" name="monto_mora">
                        </div>
                        <div class="col-md-4">
                            <label for="moraTipo">Maximo dias Aplicar Mora</label>
                            <input type="number" min="0" required placeholder="Ingrese lo dias para aplicar mora" class="form-control" step="1" name="dias_mora">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="comentarios">Comentarios del Préstamo:</label>
                            <textarea name="comentarios" id="comentarios" cols="30" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <br>

                <div class="row">
                    <div class="col-md-12 text-center">
                        <a href="#" id="btnAmortizacion" class="btn btn-sm btn-warning w-100 p-2 bold">Ver tabla de amortización</a>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive" style="max-height: 400px;overflow: scroll">
                            <table class="table table-sm" id="tblAmortizacion">
                                <thead>
                                <tr class="table-success">
                                    <th>N° Cuota</th>
                                    <th>Fecha Cuota</th>
                                    <th>Saldo Inicial</th>
                                    <th>Cuota Fija</th>
                                    <th>Interes</th>
                                    <th>Abono a Capital</th>
                                    <th>Saldo Final</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
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
