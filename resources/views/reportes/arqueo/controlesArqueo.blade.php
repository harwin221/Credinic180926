<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table">
                <tr class="table-success text-center">
                    <th>Denominación C$</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Denominación U$</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>

                <tr>
                    <th class="text-center">0.50c C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->moneda_c_050:null}}" min="0" tabindex="1" step="1" name="moneda_c_050" data-valor="0.5" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="moneda_c_050" class="form-control resultado cordoba" disabled></th>

                    <th class="text-center">1 U$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_d_1:null}}" min="0" tabindex="11" step="1" name="billete_d_1" data-valor="1" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_d_1" class="form-control resultado dolar" disabled></th>
                </tr>

                <tr>
                    <th class="text-center">1 C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->moneda_c_1:null}}" min="0" tabindex="2" step="1" name="moneda_c_1" data-valor="1" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="moneda_c_1" class="form-control resultado cordoba" disabled></th>

                    <th class="text-center">2 U$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_d_2:null}}" min="0" tabindex="12" step="1" name="billete_d_2" data-valor="2" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_d_2" class="form-control resultado dolar" disabled></th>
                </tr>

                <tr>
                    <th class="text-center">5 C$ (Mon)</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->moneda_c_5:null}}" min="0" tabindex="3" step="1" name="moneda_c_5" data-valor="5" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="moneda_c_5" class="form-control resultado cordoba" disabled></th>

                    <th class="text-center">5 U$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_d_5:null}}" min="0" tabindex="13" step="1" name="billete_d_5" data-valor="5" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_d_5" class="form-control resultado dolar" disabled></th>
                </tr>

                <tr>
                    <th class="text-center">5 C$ (Bill)</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_c_5:null}}" min="0" tabindex="3" step="1" name="billete_c_5" data-valor="5" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_c_5" class="form-control resultado cordoba" disabled></th>

                    <th class="text-center">10 U$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_d_10:null}}" min="0" tabindex="14" step="1" name="billete_d_10" data-valor="10" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_d_10" class="form-control resultado dolar" disabled></th>
                </tr>

                <tr>
                    <th class="text-center">10 C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_c_10:null}}" min="0" tabindex="4" step="1" name="billete_c_10" data-valor="10" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_c_10" class="form-control resultado cordoba" disabled></th>

                    <th class="text-center">20 U$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_d_20:null}}" min="0" tabindex="15" step="1" name="billete_d_20" data-valor="20" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_d_20" class="form-control resultado dolar" disabled></th>
                </tr>

                <tr>
                    <th class="text-center">20 C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_c_20:null}}" min="0" tabindex="5" step="1" name="billete_c_20" data-valor="20" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_c_20" class="form-control resultado cordoba" disabled></th>

                    <th class="text-center">50 U$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_d_50:null}}" min="0" tabindex="16" step="1" name="billete_d_50" data-valor="50" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_d_50" class="form-control resultado dolar" disabled></th>
                </tr>

                <tr>
                    <th class="text-center">50 C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_c_50:null}}" min="0" tabindex="6" step="1" name="billete_c_50" data-valor="50" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_c_50" class="form-control resultado cordoba" disabled></th>

                    <th class="text-center">100 U$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_d_100:null}}" min="0" tabindex="17" step="1" name="billete_d_100" data-valor="100" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_d_100" class="form-control resultado dolar" disabled></th>
                </tr>

                <tr>
                    <th class="text-center">100 C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_c_100:null}}" min="0" tabindex="7" step="1" name="billete_c_100" data-valor="100" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_c_100" class="form-control resultado cordoba" disabled></th>

                    <th></th>
                    <th></th>
                    <th></th>
                </tr>

                <tr>
                    <th class="text-center">200 C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_c_200:null}}" min="0" tabindex="8" step="1" name="billete_c_200" data-valor="200" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_c_200" class="form-control resultado cordoba" disabled></th>


                </tr>

                <tr>
                    <th class="text-center">500 C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_c_500:null}}" min="0" tabindex="9" step="1" name="billete_c_500" data-valor="500" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_c_500" class="form-control resultado cordoba" disabled></th>

                    <th></th>
                    <th></th>
                    <th></th>
                </tr>

                <tr>
                    <th class="text-center">1,000 C$</th>
                    <th><input type="number" value="{{($arqueo)?$arqueo->billete_c_1000:null}}" min="0" tabindex="10" step="1" name="billete_c_1000" data-valor="1000" class="form-control text-center valor"></th>
                    <th><input type="number" min="0" step="1" data-name="billete_c_1000" class="form-control resultado cordoba" disabled></th>

                    <th></th>
                    <th></th>
                    <th></th>
                </tr>

                <tr class="table-light">
                    <th class="text-end" colspan="2">Total C$:</th>
                    <th><input type="number"  id="total_c" min="0" step="1" name="total_c" class="form-control" disabled></th>

                    <th class="text-end" colspan="2">Total U$:</th>
                    <th><input type="number" id="total_d" min="0" step="1" name="total_d" class="form-control" disabled></th>
                </tr>
                <tr class="table-light">
                    <th class="text-end" colspan="2">Subtotal C$ + U$:</th>
                    <th><input type="number" min="0" step="1" name="subtotal" class="form-control" disabled id="subtotal"></th>

                    <th class="text-end" colspan="2">Conversión $US - C$</th>
                    <th><input type="number" min="0" step="1" name="conversion" class="form-control" disabled id="conversion"></th>
                </tr>

                <tr class="table-warning">
                    <th class="text-end" colspan="2">Desembolsos C$:</th>
                    <th>
                        <input type="number" min="0" step="0.01" name="desembolsos" id="desembolsos"
                               value="{{($arqueo && $arqueo->desembolsos && $arqueo->desembolsos > 0) ? $arqueo->desembolsos : ''}}"
                               class="form-control text-center"
                               placeholder="">
                    </th>
                    <th colspan="3" class="text-muted" style="font-size:12px; vertical-align: middle;">
                        <i class="fas fa-info-circle"></i> Monto desembolsado en efectivo durante el día
                    </th>
                </tr>

                <tr class="table-light">
                    <th class="text-end" colspan="2">Diferencia:</th>
                    <th><input type="number" min="0" step="1" name="neto" class="form-control" disabled id="neto"></th>
                </tr>

            </table>
        </div>

    </div>
</div>
