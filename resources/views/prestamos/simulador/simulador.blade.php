@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Simulador
@endsection
@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="montoFinanciar">Monto a Financiar:</label>
                <div class="input-group mb-3">
                <span class="input-group-text">
                    {{html()->select('moneda',['1'=>'C$','2'=>'U$'])->id('moneda')->required()}}
                </span>
                    <input type="number" name="montoFinanciar" min="1" step="0.001" class="form-control calcular"
                           aria-label="Monto" id="montoFinanciar">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="formaPago">Forma de Pago:</label>
                {{html()->select('formaPago',[''=>'* Seleccione *','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'])->class('form-control calcular')->id('formaPago')->required()}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="plazoPago" class="required">Plazo de Pago (Meses):</label>
                <input type="number" step="0.5" min="0" name="plazoPago" required class="form-control calcular"
                       id="plazoPago">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="tasaInteres">Tasa de Interes: % (Mensual)</label>
                <input type="number" min="0" step="0.01" required class="form-control calcular" name="tasaInteres"
                       id="tasaInteres">
            </div>
        </div>


    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="montoTotalFinanciar" class="required">Monto Total a Financiar:</label>
                <input type="number" name="montoTotalFinanciar" style="background: lightgray" required
                       class="form-control calcular" id="montoTotalFinanciar" readonly step="0.01">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="montoCuota" class="required">Monto de la Cuota</label>
                <input type="number" name="montoCuota" required class="form-control" style="background: lightgray"
                       id="montoCuota" readonly step="0.01">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="interesPagar" class="required">Intereses a Pagar</label>
                <input type="text" name="interesPagar" required class="form-control" style="background: lightgray"
                       id="interesPagar" readonly>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="interesMes" class="required">Total de Intereses por Mes:</label>
                <input type="text" name="interesMes" required class="form-control calcular"
                       style="background: lightgray" id="interesMes" readonly>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="totalIntereses" class="required">Total de Intereses a Pagar:</label>
                <input type="text" name="totalIntereses" required class="form-control calcular"
                       style="background: lightgray" id="totalIntereses" readonly>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="fechaPago">Fecha del Primer Pago:</label>
                <input type="date" class="form-control" name="fechaPago" id="fechaPago" min="{{date('Y-m-d')}}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="fechaPago">Fecha del Ultimo Pago:</label>
                <input type="text" disabled class="form-control" id="fechaUltimoPago">
            </div>
        </div>
        <div class="col-md-3">
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
        <div class="col-md-12">
            <a href="javascript:void(0);" id="btnTablaAmortizacion" class="btn btn-lg btn-warning w-100">Generar Tabla de Amortización</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
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
@endsection
@section('script')
    <script>
        document.getElementById('btnTablaAmortizacion').addEventListener('click',generarTablaAmortizacion)

        document.querySelectorAll('.calcular').forEach((input) => {
            input.addEventListener('change', calcularValores);
        })


        function calcularValores() {

            document.getElementById('fechaUltimoPago').value = ""
            const formaPago = document.getElementById('formaPago').value

            const montoFinanciar = document.getElementById('montoFinanciar').value
            const tasaInteres = document.getElementById('tasaInteres').value
            const plazoPago = document.getElementById('plazoPago').value

            const txtMontoFinanciarTotal = document.getElementById('montoTotalFinanciar');
            const txtMontoCuota = document.getElementById('montoCuota');
            const txtInteresesPagar = document.getElementById('interesPagar');
            const txtInteresesPorMes = document.getElementById('interesMes');
            const txtTotalIntereses = document.getElementById('totalIntereses');

            let subTotalFinanciamiento = 0;
            let montoCuotaMes = 0;

            let interesCuota = 0;
            let interesesMes = 0;
            let interesesPagar = 0;
            let formapagovalor = 0;


            if (esNumeroValido(montoFinanciar) && esNumeroValido(tasaInteres) && esNumeroValido(plazoPago) && formaPago !== '') {
                if (parseFloat(montoFinanciar) > 0 && parseFloat(tasaInteres) > 0 && parseFloat(plazoPago) > 0) {
                    formapagovalor = getFormaPagoValores()
                    let resPlazo = 0;
                    if(["5","6"].includes(formaPago))//trimestrales,bimestrales
                        resPlazo = parseFloat(plazoPago) / formapagovalor
                    else
                        resPlazo = parseFloat(plazoPago) * formapagovalor

                    if (resPlazo - Math.floor(resPlazo) > 0)
                        Swal.fire('Advertencia', 'El número de cuotas según los parámetros ingresados generan valores decimales [' + resPlazo + "]", 'warning')

                    interesesMes = parseFloat(montoFinanciar) * (parseFloat(tasaInteres) / 100);
                    interesesPagar = interesesMes * parseFloat(plazoPago);
                    subTotalFinanciamiento = parseFloat(montoFinanciar) + interesesPagar;

                    let montoCuotaCalculado = subTotalFinanciamiento / resPlazo;
                    let montoCuotaRounded = parseFloat(montoCuotaCalculado.toFixed(2));
                    
                    // La última cuota se ajustará automáticamente para cuadrar el total exacto
                    interesCuota = interesesPagar / resPlazo;
                    montoCuotaMes = montoCuotaRounded;

                }
            }

            txtMontoFinanciarTotal.value = subTotalFinanciamiento.toFixed(2);
            txtMontoCuota.value = montoCuotaMes.toFixed(2);
            txtInteresesPagar.value = interesCuota.toFixed(2);
            txtInteresesPorMes.value = interesesMes.toFixed(2);
            txtTotalIntereses.value = interesesPagar.toFixed(2);
        }

        function getFormaPagoValores() {
            const formaPago = document.getElementById('formaPago').value
            let formapagovalor = 0;
            if (formaPago !== '') {
                switch (formaPago) {
                    case "1":
                        formapagovalor = 20
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "2":
                        formapagovalor = 4;
                        $("#divDiaSemanaPreferido").show()
                        $("#labelDiaSemana").html('Día de la Semana: <small><b>Para pagos semanales</b></small>')
                        break
                    case "3":
                        formapagovalor = 2;
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "4":
                        formapagovalor = 1;
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "5":
                        formapagovalor = 3;//PLAZO TOTALES DEBE DIVIDIRSE CON ESTE VALOR
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "6":
                        formapagovalor = 2;//PLAZO TOTALES DEBE DIVIDIRSE CON ESTE VALOR
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "7":
                        formapagovalor = 2;//PLAZO TOTALES DEBE DIVIDIRSE CON ESTE VALOR
                        $("#divDiaSemanaPreferido").show()
                        $("#labelDiaSemana").html('Día de la Semana: <small><b>Para pagos catorcenales</b></small>')
                        break

                }
            }
            return formapagovalor
        }

        function getValorFechaAumentar() {
            const formaPago = document.getElementById('formaPago').value
            let valorAumentar = 0;
            if (formaPago !== '') {
                switch (formaPago) {
                    case "1":
                        valorAumentar = 1
                        break
                    case "2":
                        valorAumentar = 7;
                        break
                    case "3":
                        valorAumentar = 15;
                        break
                    case "4":
                        valorAumentar = 30;
                        break
                    case "5":
                        valorAumentar = 90;
                        break
                    case "6":
                        valorAumentar = 60;
                        break
                     case "7":
                        valorAumentar = 14;
                        break
                }
            }
            return valorAumentar
        }

        function procesarNumeroDecimal(numero) {
            return numero;
        }


        async function generarTablaAmortizacion() {

            $("#tblAmortizacion tbody").empty()
            let diasFeriados = await getDiasFeriados();
            const formaPago = document.getElementById('formaPago').value

            const fechaPrimerPago = document.getElementById('fechaPago').value
            let montoFinanciar = document.getElementById('montoFinanciar');
            let plazoPago = document.getElementById('plazoPago');

            let montoTotalFinanciar = document.getElementById('montoTotalFinanciar');
            let montoIntereses = document.getElementById('interesPagar');
            let montoCuota = document.getElementById('montoCuota');

            let formapagovalor = getFormaPagoValores();

            if (fechaPrimerPago === '') {
                Swal.fire('Advertencia', 'Para ver la tabla de amortización debe ingresar la fecha del primer pago', 'warning')
                return
            }

            if (montoIntereses.value !== '' && montoTotalFinanciar.value !== '' && montoCuota.value !== '') {

                let montoActual = parseFloat(montoFinanciar.value)

                let sumaCuotas = 0;
                let sumaIntereses = 0;
                let sumaCapital = 0;

                let plazo = procesarNumeroDecimal(parseFloat(plazoPago.value));

                let resPlazo = 0;
                if (["5", "6"].includes(formaPago))//trimestrales,bimestrales
                    resPlazo = parseFloat(plazoPago.value) / formapagovalor
                else
                    resPlazo = parseFloat(plazoPago.value) * formapagovalor

                if (resPlazo - Math.floor(resPlazo) > 0)
                    Swal.fire('Advertencia', 'El número de cuotas según los parámetros ingresados generan valores decimales [' + resPlazo + "]", 'warning')

                let anyo = fechaPrimerPago.split('-')[0]
                let mes = parseInt(fechaPrimerPago.split('-')[1]) - 1
                let dia = fechaPrimerPago.split('-')[2]

                const fechaInicial = new Date(anyo, mes, dia); 
                let fechaCopia = fechaInicial
                const banderaAumentar = getValorFechaAumentar(); 
                
                // Ajustar la primera fecha si cae en feriado o domingo (o sábado para diarios)
                let fechaPrimeraAjustada = new Date(anyo, mes, dia);
                if (formaPago === "1") {
                    // Para diarios, usar función especial
                    fechaPrimeraAjustada = calcularFechaFinalDiario(fechaPrimeraAjustada, 0, diasFeriados);
                } else {
                    // Para otros, usar función normal
                    fechaPrimeraAjustada = calcularFechaFinal(fechaPrimeraAjustada, 0, diasFeriados);
                }
                let fechaCuotaFormateada = fechaPrimeraAjustada.toISOString().split('T')[0];
                
                let ultimaFecha = ""
                let diasPreferidos = document.getElementById('diasPreferidos').value
                let diaEvaluar = dia
                let diaPreferidoEvaluar = diasPreferidos

               if (diasPreferidos !== '' && (diasPreferidos > 31 || diasPreferidos <= 0 || diasPreferidos % 1 > 0)) {
                    Swal.fire('Error', 'El siguiente de dia de pago es incorrecto', 'error')
                    return
                }

                let arrFechasMes = [];
                let diaSemanaPactado = null;
                let diaSemanaPreferido = document.getElementById('diaSemanaPreferido').value;

                for (let i = 0; i <= resPlazo; i++) {
                    if (i === 0) {
                        let fila = `<tr>
                            <td> ${i} </td>
                            <td> - </td>
                            <td> - </td>
                            <td> - </td>
                            <td> - </td>
                            <td> - </td>
                            <td> ${montoActual.toFixed(2)} </td>
                          </tr>`
                        $("#tblAmortizacion tbody").append(fila)
                        
                        // Guardar el día de la semana para SEMANAL y CATORCENAL
                        if (formaPago === "2" || formaPago === "7") {
                            if (diaSemanaPreferido) {
                                diaSemanaPactado = parseInt(diaSemanaPreferido);
                            } else {
                                diaSemanaPactado = fechaPrimeraAjustada.getDay();
                            }
                        }
                    } else {

                        let montoCuotaRow = parseFloat(montoCuota.value);
                        let abonoCapital = 0;
                        let interesesRow = parseFloat(montoIntereses.value);

                        // Ajuste Senior: Si es la última cuota, el abono a capital debe ser el saldo restante
                        if (i === Math.floor(resPlazo)) {
                            abonoCapital = montoActual;
                            montoCuotaRow = abonoCapital + interesesRow;
                        } else {
                            abonoCapital = parseFloat(montoCuota.value) - interesesRow;
                        }

                        sumaCapital += abonoCapital
                        sumaIntereses += interesesRow
                        sumaCuotas += montoCuotaRow

                        let saldoFinalRow = (montoActual - abonoCapital).toFixed(2);

                        let fila = `<tr>
                            <td> ${i} </td>
                            <td> ${fechaCuotaFormateada} </td>
                            <td> ${montoActual.toFixed(2)} </td>
                            <td> ${montoCuotaRow.toFixed(2)} </td>
                            <td> ${interesesRow.toFixed(2)} </td>
                            <td> ${abonoCapital.toFixed(2)} </td>
                            <td> ${saldoFinalRow} </td>
                          </tr>`

                        montoActual = parseFloat(saldoFinalRow);
                        $("#tblAmortizacion tbody").append(fila)

                        let diaCopia = parseInt(fechaCopia.toLocaleString().split('/')[0])
                        let mesCopia = parseInt(fechaCopia.toLocaleString().split('/')[1]) - 1
                        let anyoCopia = parseInt(fechaCopia.toLocaleString().split('/')[2])

                        let fechaCuota = new Date(anyoCopia, mesCopia, diaCopia);
                        ultimaFecha = fechaCuotaFormateada

                        if (formaPago === "3" && diasPreferidos > 0) {
                            // QUINCENAL CON DÍA PREFERIDO: alternar entre dia inicial y dia preferido
                            if (diaEvaluar === dia)
                                diaEvaluar = diaPreferidoEvaluar
                            else
                                diaEvaluar = dia

                            if (parseInt(dia) > parseInt(diaEvaluar) && i === 1 || arrFechasMes.filter(fecha => fecha === mesCopia + "-" + anyoCopia).length > 1)
                                fechaCopia.setMonth(fechaCopia.getMonth() + 1, 1)

                            diaCopia = parseInt(diaEvaluar, 10)
                            mesCopia = parseInt(fechaCopia.toLocaleString().split('/')[1]) - 1
                            anyoCopia = parseInt(fechaCopia.toLocaleString().split('/')[2])
                            fechaCuota = new Date(anyoCopia, mesCopia, diaCopia);

                            // Ajustar por domingos y feriados
                            do {
                                if (fechaCuota.getDay() === 0) {
                                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                                }
                                let fechaVerificar = fechaCuota.toISOString().split('T')[0];
                                if (diasFeriados.includes(fechaVerificar)) {
                                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                                }
                            } while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaCuota.toISOString().split('T')[0]));

                        } else {
                            // OTROS TIPOS: sumar días normalmente
                            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar)
                            
                            // Para SEMANAL y CATORCENAL: Forzar al día de la semana pactado
                            if ((formaPago === "2" || formaPago === "7") && diaSemanaPactado !== null) {
                                let diferenciaDias = diaSemanaPactado - fechaCuota.getDay();
                                if (diferenciaDias < 0) diferenciaDias += 7;
                                if (diferenciaDias > 0 && diferenciaDias < 7) {
                                    fechaCuota.setDate(fechaCuota.getDate() + diferenciaDias);
                                }
                            }
                            
                            // Ajustar si cae en feriado o domingo
                            if (formaPago === "1") {
                                fechaCuota = calcularFechaFinalDiario(fechaCuota, 0, diasFeriados);
                            } else {
                                let fechaVerificar = fechaCuota.toISOString().split('T')[0];
                                while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaVerificar)) {
                                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                                    fechaVerificar = fechaCuota.toISOString().split('T')[0];
                                }
                            }
                        }

                        arrFechasMes.push(mesCopia + "-" + anyoCopia)

                        fechaCuotaFormateada = fechaCuota.toISOString().split('T')[0];

                        let diaFormateada = parseInt(fechaCuota.toLocaleString().split('/')[0])
                        let mesFormateada = parseInt(fechaCuota.toLocaleString().split('/')[1]) - 1
                        let anyoFormateada = parseInt(fechaCuota.toLocaleString().split('/')[2])

                        fechaCopia = new Date(anyoFormateada, mesFormateada, diaFormateada);
                    }
                }
                document.getElementById('fechaUltimoPago').value = ultimaFecha

                let filaTotales = `<tr style="font-weight: bold;background: gray">
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>${sumaCuotas.toFixed(2)}</td>
                                            <td>${sumaIntereses.toFixed(2)}</td>
                                            <td>${sumaCapital.toFixed(2)}</td>
                                            <td>-</td>
                                            </tr>`
                $("#tblAmortizacion tbody").append(filaTotales)

                // openModal('modalAmortizacion')
            } else {
                Swal.fire('Advertencia♠', 'Se deben de completar todos los campos para poder ver la tabla de amortización', 'warning')
            }
        }

        // Función para ajustar fechas en préstamos DIARIOS
        // Solo lunes a viernes son válidos (NO sábado, NO domingo, NO feriados)
        function calcularFechaFinalDiario(fechaInicial, banderaAumentar, diasFeriados) {
            let fechaCuota = new Date(fechaInicial);
            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);

            while (true) {
                let fechaVerificar = fechaCuota.toISOString().split('T')[0];
                
                // Si es sábado o domingo, avanzar
                if (fechaCuota.getDay() === 0 || fechaCuota.getDay() === 6) {
                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                    continue;
                }
                
                // Si es feriado, avanzar
                if (diasFeriados.includes(fechaVerificar)) {
                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                    continue;
                }
                
                // Si llegamos aquí, es un día válido (lunes a viernes, no feriado)
                break;
            }

            return fechaCuota;
        }

        function calcularFechaFinal(fechaInicial, banderaAumentar,diasFeriados) {
            let fechaCuota = new Date(fechaInicial);
            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);

            let fechaVerificar = fechaCuota.toISOString().split('T')[0];
            // Solo rechaza DOMINGOS (0) y FERIADOS, NO sábados (6)
            while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaVerificar)) {
                fechaCuota.setDate(fechaCuota.getDate() + 1);
                fechaVerificar = fechaCuota.toISOString().split('T')[0];
            }

            return fechaCuota;
        }

        function esFinDeSemana(fecha) {
            const diaSemana = fecha.getDay();
            return diaSemana === 0 || diaSemana === 6;
        }

        async function getDiasFeriados(){
            let {data} = await axios.get('{{route('configuracion.feriados.getFeriados')}}')
            return data
        }
    </script>
@endsection
