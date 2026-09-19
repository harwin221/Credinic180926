@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('prestamos.index')])
    Nuevo Desembolso
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {{html()->form()->method('POST')->action(route('prestamos.store'))->id('frmPrestamo')->open()}}
                    <div class="row">
                        <h5 class="card-title">Datos del Cliente</h5>
                    </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    @component('utlisComponents.stepper')
                                        @slot('headers')
                                            <div class="step" data-target="#datosClienteStep">
                                                <button type="button" class="step-trigger" role="tab" aria-controls="datosClienteStep" id="datosClienteId">
                                                    <span class="bs-stepper-circle">1</span>
                                                    <span class="bs-stepper-label">Datos del Cliente</span>
                                                </button>
                                            </div>
                                            <div class="line"></div>
                                            <div class="step" data-target="#datosNegocioStep">
                                                <button type="button" class="step-trigger" role="tab" aria-controls="datosNegocioStep" id="datosNegocioId">
                                                    <span class="bs-stepper-circle">2</span>
                                                    <span class="bs-stepper-label">Datos del Negocio</span>
                                                </button>
                                            </div>
                                            <div class="line"></div>
                                            <div class="step" data-target="#datosFiadorStep">
                                                <button type="button" class="step-trigger" role="tab" aria-controls="datosFiadorStep" id="datosFiadorId">
                                                    <span class="bs-stepper-circle">3</span>
                                                    <span class="bs-stepper-label">Datos del Fiador</span>
                                                </button>
                                            </div>
                                            <div class="line"></div>
                                            <div class="step" data-target="#datosPrestamoStep">
                                                <button type="button" class="step-trigger" role="tab" aria-controls="datosPrestamoStep" id="datosPrestamoId">
                                                    <span class="bs-stepper-circle">4</span>
                                                    <span class="bs-stepper-label">Datos del Préstamo</span>
                                                </button>
                                            </div>

                                            <div class="line"></div>
                                            <div class="step" data-target="#resumenPrestamoStep">
                                                <button type="button" class="step-trigger" role="tab" aria-controls="resumenPrestamoStep" id="resumenPrestamoStepId">
                                                    <span class="bs-stepper-circle">5</span>
                                                    <span class="bs-stepper-label">Finalizar Préstamo</span>
                                                </button>
                                            </div>
                                        @endslot

                                        @slot('steps')
                                            {{--************************************--}}
                                            <div id="datosClienteStep" class="content" role="tabpanel" aria-labelledby="datosClienteId">
                                                {{--                                                <input type="hidden" name="clienteSeleccionado" id="clienteSel" value="{{request('cliente')}}" readonly>--}}
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="cliente"><b>Seleccionar Cliente</b></label>
                                                        {{html()->select('cliente',[],request('cliente'))->class('form-control text-center select2')->style(['width'=>'100%'])->id('selectClientes')->required()}}
                                                    </div>
                                                </div>
                                                <br>
                                                    @include('prestamos.stepper.stepperDatosCliente')
                                                <hr>
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <button type="button" class="btn btn-sm btn-primary btn-siguiente" data-next="2">Siguiente</button>
                                                    </div>
                                                </div>
                                            </div>
                                            {{----------------------------------------}}

                                            {{--************************************--}}
                                            <div id="datosNegocioStep" class="content" role="tabpanel" aria-labelledby="datosNegocioId">
                                                <hr>
                                                <div class="d-flex justify-content-center">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" checked style="font-size: 20px" type="checkbox" id="chkAsalariado">
                                                        <label class="form-check-label fw-bold text-uppercase" style="font-size: 20px;color: blue" for="chkAsalariado">El cliente es asalariado</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label for="negocio"><b>Negocios del cliente</b></label>
                                                        {{html()->select('negocio',[],null)->class('form-control select2')->style(['width'=>'100%'])->id('selectNegocios')}}
                                                    </div>
                                                    <div class="col-md-2">
                                                        <br>
                                                        @can('Crear Negocios')
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalNuevoNegocio" class="btn btn-sm btn-primary">Nuevo Negocio <i class="fa fa-plus"></i></a>
                                                        @endcan
                                                    </div>
                                                    @include('prestamos.modals.modalNuevoNegocio')

                                                </div>
                                                <hr>
                                                @include('prestamos.stepper.stepperDatosNegocio')
                                                <br>
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <button type="button" class="btn btn-primary btn-sm btn-anterior" data-previous="1">Anterior</button>
                                                        <button type="button" class="btn btn-primary btn-sm btn-siguiente" data-next="3">Siguiente</button>
                                                    </div>
                                                </div>
                                            </div>
                                            {{----------------------------------------}}

                                            {{--************************************--}}
                                            <div id="datosFiadorStep" class="content" role="tabpanel" aria-labelledby="datosFiadorId">
                                                <hr>
                                                    <div class="d-flex justify-content-center">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" checked style="font-size: 20px" type="checkbox" id="chkFiador">
                                                            <label class="form-check-label fw-bold text-uppercase" style="font-size: 20px;color: blue" for="chkFiador">El préstamo no requiere fiador</label>
                                                        </div>
                                                    </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label for="selectFiadores"><b>Fiadores del cliente</b></label>
                                                        {{html()->select('fiador',[],null)->class('form-control select2')->style(['width'=>'100%'])->id('selectFiadores')}}
                                                    </div>
                                                    <div class="col-md-2">
                                                        <br>
                                                        @can('Crear Fiadores')
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalNuevoFiador" class="btn btn-sm btn-primary">Nuevo Fiador <i class="fa fa-plus"></i></a>
                                                        @endcan
                                                    </div>
                                                    @include('prestamos.modals.modalNuevoFiador')

                                                </div>
                                                <hr>
                                                @include('prestamos.stepper.stepperDatosFiador')
                                                <br>

                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <button type="button" class="btn btn-primary btn-sm btn-anterior" data-previous="2">Anterior</button>
                                                        <button type="button" class="btn btn-primary btn-sm btn-siguiente" data-next="4">Siguiente</button>
                                                    </div>
                                                </div>
                                            </div>
                                            {{----------------------------------------}}

                                            {{--************************************--}}
                                            <div id="datosPrestamoStep" class="content" role="tabpanel" aria-labelledby="datosPrestamoId">
                                                <hr>
                                                @include('prestamos.stepper.stepperDatosPréstamo')
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <a href="#" id="btnAmortizacion" class="btn btn-sm btn-warning w-100 p-2 bold text-uppercase"><b>Ver tabla de amortización</b></a>
                                                    </div>
                                                </div>
                                                <br>
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
                                                        {{--                                                        @include('prestamos.tablaAmortizacionPrestamo')--}}
                                                    </div>
                                                </div>

                                                <br>
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <button type="button" class="btn btn-primary btn-sm btn-anterior" data-previous="3">Anterior</button>
                                                        <button type="button" class="btn btn-primary btn-sm btn-siguiente" data-next="5">Siguiente</button>
{{--                                                        <button type="button" class="btn btn-success btn-sm btn-siguiente" onclick="savePrestamo()">Guardar Préstamo</button>--}}
                                                    </div>
                                                </div>
                                            </div>
                                            {{----------------------------------------}}

                                            <div id="resumenPrestamoStep" class="content" role="tabpanel" aria-labelledby="resumenPrestamoStepId">
                                                @include('prestamos.stepper.stepperResumenPrestamo')
                                                <br>
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <button type="button" class="btn btn-primary btn-sm btn-anterior" data-previous="4">Anterior</button>
                                                        @can('Crear Desembolsos')
                                                            <button type="button" class="btn btn-success btn-sm btn-siguiente" id="btnSavePrestamo">Guardar Préstamo</button>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        @endslot
                                    @endcomponent
                                </div>
                            </div>
                        </div>
                    {{html()->form()->close()}}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let stepper

        document.addEventListener('DOMContentLoaded', function () {
            stepper = new Stepper(document.querySelector('.bs-stepper'))
            getListClientes()
        })

        function limpiarDatosNegocio()
        {
            document.getElementById('nombreNegocio').value = ''
            document.getElementById('negocioDireccion').value = ''
            document.getElementById('negocioComentarios').value = ''
            document.getElementById('negocioDireccion').value = ''
            document.getElementById('depNegocio').value = ''
            document.getElementById('munNegocio').value = ''
            document.getElementById('ubicacionNegocio').value = ''
            document.getElementById('telefonoNegocio').value = ''
        }

        function limpiarDatosCliente()
        {
            document.getElementById('nombres').value = ''
            document.getElementById('apellidos').value = ''
            document.getElementById('cedula').value = ''
        }

        function limpiarDatosFiador()
        {
            document.getElementById('nombreFiador').value = ''
            document.getElementById('apellidoFiador').value = ''
            document.getElementById('cedulaFiador').value = ''
            document.getElementById('telefono1Fiador').value = ''
            document.getElementById('telefono2Fiador').value = ''
            document.getElementById('direccionFiador').value = ''
            document.getElementById('observacionesFiador').value = ''
            document.getElementById('departamentoMunFiador').value = ''
        }

        function limpiarDatosModalNegocios(){
            document.getElementById('modalNombreNegocio').value = ''
            document.getElementById('modalDireccionNegocio').value = ''
            document.getElementById('modalDpMunNegocio').value = ''
            document.getElementById('modalUbicacionNegocio').value = ''
            document.getElementById('modalTelefonoNegocio').value = ''
            document.getElementById('modalComentarioNegocio').value = ''
        }

        function limpiarModalFiador(){
            document.getElementById('modalNombreFiador').value = ''
            document.getElementById('modalApellidosFiador').value = ''
            document.getElementById('modalCedulaFiador').value = ''
            document.getElementById('modalTelefono1Fiador').value = ''
            document.getElementById('modalTelefono2Fiador').value = ''
            document.getElementById('modalDireccionFiador').value = ''
            document.getElementById('modalObservacionesFiador').value = ''
            document.getElementById('modalDepartamentoFiador').value = ''
        }

        function closeModal(modalId){
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.hide();
        }

        function openModal(modalId){
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                keyboard: false
            });
            modal.show();
        }

        function validarCamposVaciosModalNegocio(){
            return (document.getElementById('modalNombreNegocio').value === '' ||
                $("#modalTipoNegocio").val() === '' ||
                document.getElementById('modalDireccionNegocio').value === '')
        }

        function validarCamposVaciosModalFiador(){
            return (document.getElementById('modalNombreFiador').value === '' ||
                document.getElementById('modalApellidosFiador').value === '' ||
                document.getElementById('modalDepartamentoFiador').value === '' ||
                document.getElementById('modalCedulaFiador').value === '')
        }

        function validarCamposPrestamo(){
            return (
                document.getElementById('fechaPrestamo').value === '' ||
                document.getElementById('plazoPago').value === '' ||
                document.getElementById('tasaInteres').value === '' ||
                document.getElementById('formaPago').value === '' ||
                document.getElementById('fechaPago').value === '' ||
                document.getElementById('fechaDesembolso').value === '' ||
                $("#selectVendedor").val() === '' ||
                $("#selectDesembolso").val() === '' ||
                $("#selectAgente").val() === '' ||
                $("#selTipoPrestamo").val() === ''
            )
        }

        document.querySelectorAll('.btn-siguiente').forEach((button) => {
            button.addEventListener('click', (event) => {

                let next = 1
                if (event.target.classList.contains('btn-siguiente'))
                    next = event.target.dataset.next
                else
                    next = event.target.closest('button', '.btn-siguiente').dataset.next

                if (stepper['_currentIndex'] === 0) { //STEP CLIENTE
                    //stepper.to(next)
                    if ($("#selectClientes").val()!==null && $("#selectClientes").val() !== '')
                        stepper.to(event.target.dataset.next)
                    else
                        Swal.fire('Advertencia', 'Para avanzar al siguiente paso debe de seleccionar un cliente', 'warning')
                }

                if (stepper['_currentIndex'] === 1) {//STEP NEGOCIO
                    //stepper.to(next)

                    if (document.getElementById('chkAsalariado').checked || $("#selectNegocios").val()!==null && $("#selectNegocios").val() !== '')
                        stepper.to(event.target.dataset.next)
                    else
                        Swal.fire('Advertencia', 'Para avanzar al siguiente paso debe de seleccionar un negocio del cliente', 'warning')
                }

                if (stepper['_currentIndex'] === 2) {//STEP FIADOR
                    //stepper.to(next)
                    if (document.getElementById('chkFiador').checked || ($("#selectFiadores").val() !== '') && $("#selectFiadores").val()!==null)
                        stepper.to(event.target.dataset.next)
                    else
                        Swal.fire('Advertencia', 'Para avanzar al siguiente paso debe de seleccionar un fiador del cliente', 'warning')
                }

                if (stepper['_currentIndex'] === 3) {//STEP PRESTAMO
                    //stepper.to(next)
                    if (!validarCamposPrestamo()){
                        resumenPrestamo()
                        stepper.to(next)
                    }
                    else
                        Swal.fire('Advertencia', 'Para avanzar al siguiente paso debe de completar los campos del prestamo', 'warning')
                }
            })
        });

        document.querySelectorAll('.btn-anterior').forEach((button) => {
            button.addEventListener('click', (event) => {

                let previous = 1
                if (event.target.classList.contains('btn-btn-anterior'))
                    previous = event.target.dataset.next
                else
                    previous = event.target.closest('button', '.btn-btn-anterior').dataset.previous

                stepper.to(previous)
            })
        });

        document.getElementById('chkFiador').addEventListener('change',sinFiador)
        document.getElementById('chkAsalariado').addEventListener('change',clienteAsalariado)
        document.querySelectorAll('.calcular').forEach((input) => {
            input.addEventListener('change', calcularValores);
        })

        document.getElementById('btnSavePrestamo').addEventListener('click',savePrestamo)
        document.getElementById('btnAmortizacion').addEventListener('click',generarTablaAmortizacion)

        $("#selectClientes").on('change', function () {
            if($(this).val()!=='') {
                getDatosCliente($(this).val())
                getListNegociosCliente($(this).val())
                getTiposNegocios()
                getFiadoresUser($(this).val())
                getAgentes()
                getVendedores()
            }
            else
                limpiarDatosCliente()
        })

        $("#selectNegocios").on('change', function () {
            if ($(this).val() !== ''){
                getDatosNegocio($(this).val())
                document.getElementById('chkAsalariado').checked=false;
            }
            else
                limpiarDatosNegocio()
        })

        $("#selectFiadores").on('change', function () {
            if ($(this).val() !== '') {
                getDatosFiador($(this).val())
                document.getElementById('chkFiador').checked=false;
            } else
                limpiarDatosFiador()
        })

        async function getListClientes() {
            $("#selectClientes").empty();
            let url = '{{route('user.clientes.getListClientes')}}'
            const {data} = await axios.get(url)

            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`

                $("#selectClientes").append(opcion)
            }
        }

        async function getListNegociosCliente(clienteID) {

            $("#selectNegocios").empty();
            let url = '{{route('negocio.getListNegociosCliente','?')}}'
            url = url.replace('?', clienteID)

            const {data} = await axios.get(url)

            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`

                $("#selectNegocios").append(opcion)
            }
        }

        async function getDatosCliente(clienteID) {
            let url = '{{route('user.clientes.getDatosCliente','?')}}'
            url = url.replace('?', clienteID)
            const {data} = await axios.get(url)

            // if (data.prestamoActivo === 1) {
            //     Swal.fire('Advertencia', 'El cliente tiene un préstamo activo, no se puede realizar otro préstamo', 'warning')
            //     $("#selectClientes").val('').trigger('change')
            //     return;
            // }

            document.getElementById('nombres').value = data.nombres
            document.getElementById('apellidos').value = data.apellidos
            document.getElementById('cedula').value = data.cedula
            document.getElementById('telefono1').value = data.telefono1
            document.getElementById('telefono2').value = data.telefono2
            document.getElementById('direccion').value = data.direccion
            document.getElementById('sexo').value = data.sexo
            document.getElementById('estado_civil').value = data.estado_civil
            document.getElementById('departamento').value = data.dep
            document.getElementById('municipio').value = data.mun
        }

        async function getDatosNegocio(negocioID) {
            let negocio_nombre = document.getElementById('nombreNegocio')
            let negocio_direccion = document.getElementById('negocioDireccion')
            let depNegocio = document.getElementById('depNegocio')
            let munNegocio = document.getElementById('munNegocio')
            let ubicacionNegocio = document.getElementById('ubicacionNegocio')
            let telefonoNegocio = document.getElementById('telefonoNegocio')
            let negocioComentarios = document.getElementById('negocioComentarios')

            let url = '{{route('negocio.getInfoNegocio','?')}}'
            url = url.replace('?', negocioID)
            const {data} = await axios.get(url)

            negocio_nombre.value = data.nombre;
            negocio_direccion.value = data.direccion;
            depNegocio.value = data.dep
            munNegocio.value = data.mun
            ubicacionNegocio.value = data.punto_geografico
            telefonoNegocio.value = data.telefono_negocio
            negocioComentarios.value = data.comentarios
        }

        document.getElementById('confirmarButtonNegocio').addEventListener('click',function (){

            if(!validarCamposVaciosModalNegocio())
            {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea guardar el negocio al usuario?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#d33',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Guardar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        saveClienteNegocio()
                    }
                })
            }
            else
                Swal.fire('Advertencia', 'Se deben de llenar todos los campos del negocio', 'warning')

        })

        document.getElementById('confirmarButtonFiador').addEventListener('click',function (){

            if(!validarCamposVaciosModalFiador())
            {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea guardar el negocio al usuario?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#d33',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Guardar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        saveFiadorNegocio()
                    }
                })
            }
            else
                Swal.fire('Advertencia', 'Se deben de llenar todos los campos del negocio', 'warning')

        })

        async function saveClienteNegocio(){
            if ($("#selectClientes").val() == '') {
                Swal.fire('Advertencia', 'Se debe seleccionar un cliente', 'warning')
                return
            }
            let clienteId = $("#selectClientes").val()
            let url = '{{route('negocio.storeNegocioCliente','?')}}'
            url = url.replace('?', clienteId)

            let negocio_nombre = document.getElementById('modalNombreNegocio')
            let negocio_direccion = document.getElementById('modalDireccionNegocio')
            let modalDpMunNegocio = document.getElementById('modalDpMunNegocio')
            let modalUbicacionNegocio = document.getElementById('modalUbicacionNegocio')
            let modalTelefonoNegocio = document.getElementById('modalTelefonoNegocio')
            let modalComentarioNegocio = document.getElementById('modalComentarioNegocio')

            const {data} = await axios.post(url,{
                nombre:negocio_nombre.value,
                direccion:negocio_direccion.value,
                municipio_id:modalDpMunNegocio.value,
                punto_geografico:modalUbicacionNegocio.value,
                telefono_negocio:modalTelefonoNegocio.value,
                comentarios:modalComentarioNegocio.value,
            })
            await getListNegociosCliente(clienteId)
            $("#selectNegocios").val(data)
            $("#selectNegocios").trigger('change')

            limpiarDatosModalNegocios()
            closeModal('modalNuevoNegocio')

        }

        async function getTiposNegocios() {
            const {data} = await axios.get('{{route('configuracion.tiposNegocios.getListTiposNegocios')}}')
            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`
                $("#modalTipoNegocio").append(opcion)
            }

            $('#modalTipoNegocio').select2({
                dropdownParent: $('#modalNuevoNegocio')
            });
        }

        async function getFiadoresUser(clienteId) {
            $("#selectFiadores").empty();
            let url = '{{route('fiador.getListFiadoresUser','?')}}'
            url = url.replace('?', clienteId)

            const {data} = await axios.get(url)
            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`

                $("#selectFiadores").append(opcion)
            }
        }

        async function getDatosFiador(fiadorID) {
            let fiador_nombres = document.getElementById('nombreFiador')
            let fiador_apellidos = document.getElementById('apellidoFiador')
            let fiador_cedula = document.getElementById('cedulaFiador')
            let fiador_telefono1 = document.getElementById('telefono1Fiador')
            let fiador_telefono2 = document.getElementById('telefono2Fiador')
            let fiador_dirreccion = document.getElementById('direccionFiador')
            let fiador_observaciones = document.getElementById('observacionesFiador')
            let fiador_dep_mun = document.getElementById('departamentoMunFiador')

            let url = '{{route('fiador.getDatos','?')}}'
            url = url.replace('?', fiadorID)
            const {data} = await axios.get(url)

            fiador_nombres.value = data.nombres;
            fiador_apellidos.value = data.apellidos;
            fiador_cedula.value = data.cedula;
            fiador_telefono1.value = data.telefono1;
            fiador_telefono2.value = data.telefono2;
            fiador_dirreccion.value = data.direccion;
            fiador_dirreccion.value = data.direccion;
            fiador_dep_mun.value = data.dep_mun_fiador;


            if(data.datos_fiador)
                fiador_observaciones.value = data.datos_fiador.observaciones;
        }

        async function saveFiadorNegocio(){
            let clienteId = $("#selectClientes").val()

            let url = '{{route('fiador.storeFiador','?')}}'
            url = url.replace('?', clienteId)

            nombresF = document.getElementById('modalNombreFiador').value
            apellidosF = document.getElementById('modalApellidosFiador').value
            cedulaF = document.getElementById('modalCedulaFiador').value
            telefono1F = document.getElementById('modalTelefono1Fiador').value
            telefono2F = document.getElementById('modalTelefono2Fiador').value
            direcciónF = document.getElementById('modalDireccionFiador').value
            observacionesF = document.getElementById('modalObservacionesFiador').value
            depMunF = document.getElementById('modalDepartamentoFiador').value

            if ($("#selectClientes").val() == '') {
                Swal.fire('Advertencia', 'Se debe seleccionar un cliente', 'warning')
                return
            }
            try{
                const {data} = await axios.post(url,{
                    nombres:nombresF,
                    apellidos:apellidosF,
                    cedula:cedulaF,
                    telefono1:telefono1F,
                    telefono2:telefono2F,
                    direccion:direcciónF,
                    observaciones:observacionesF,
                    dep_mun:depMunF
                })
                await getFiadoresUser(clienteId)
                $("#selectFiadores").val(data)
                $("#selectFiadores").trigger('change')


                limpiarModalFiador()
                closeModal('modalNuevoFiador')
            }catch (e) {
                Swal.fire('Error '+e.request.response)
            }
        }

        function sinFiador(event){
            if(event.target.checked && $("#selectFiadores").val()!==null){
                $("#selectFiadores").val('')
                $("#selectFiadores").trigger('change')
            }
        }

        function clienteAsalariado(event){
            if(event.target.checked && $("#selectNegocios").val()!==null){
                $("#selectNegocios").val('')
                $("#selectNegocios").trigger('change')
            }
        }

        async function getAgentes() {
            $("#selectAgente").empty();
            let url = '{{route('user.agentes.getAgentes')}}'

            const {data} = await axios.get(url)
            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`

                $("#selectAgente").append(opcion)
            }
        }

        async function getVendedores() {
            $("#selectVendedor").empty();
            let url = '{{route('user.getListAdministrativos')}}'
            const {data} = await axios.get(url)
            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`

                $("#selectVendedor").append(opcion)
                $("#selectDesembolso").append(opcion)
            }
        }

        //PRESTAMOS
        function calcularValores(){

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

            if(esNumeroValido(montoFinanciar) && esNumeroValido(tasaInteres) && esNumeroValido(plazoPago) && formaPago!=='')
            {
                if(parseFloat(montoFinanciar) > 0 && parseFloat(tasaInteres) > 0 && parseFloat(plazoPago) > 0)
                {
                    formapagovalor = getFormaPagoValores()
                    let resPlazo = 0;
                    if (["5", "6"].includes(formaPago))//trimestrales,bimestrales
                        resPlazo = parseFloat(plazoPago) / formapagovalor
                    else
                        resPlazo = parseFloat(plazoPago) * formapagovalor

                    if( resPlazo - Math.floor(resPlazo) > 0 )
                        Swal.fire('Advertencia','El número de cuotas según los parámetros ingresados generan valores decimales ['+resPlazo+"]",'warning')

                    interesesMes = parseFloat(montoFinanciar) * (parseFloat(tasaInteres)/100);
                    interesesPagar = interesesMes * parseFloat(plazoPago);
                    subTotalFinanciamiento = parseFloat(montoFinanciar) + interesesPagar;

                    let montoCuotaCalculado = subTotalFinanciamiento / resPlazo;
                    let montoCuotaRounded = parseFloat(montoCuotaCalculado.toFixed(2));
                    
                    // La última cuota se ajustará automáticamente en el backend para cuadrar el total exacto
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

        // Función para ajustar fechas en préstamos NO DIARIOS (semanal, quincenal, catorcenal)
        // Sábado es válido, solo rechaza domingo y feriados
        function calcularFechaFinal(fechaInicial, banderaAumentar, diasFeriados) {
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


        function getFormaPagoValores() {
            const formaPago = document.getElementById('formaPago').value
            let formapagovalor = 0;
            if (formaPago !== '') {
                switch (formaPago) {
                    case "1":
                        formapagovalor = 20
                        $("#divDiasPreferidos").hide()
                        $("#dia_pago_preferido").val('')
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "2":
                        formapagovalor = 4;
                        $("#divDiasPreferidos").hide()
                        $("#dia_pago_preferido").val('')
                        $("#divDiaSemanaPreferido").show()
                        $("#labelDiaSemana").html('Día de la Semana: <small><b>Para pagos semanales</b></small>')
                        break
                    case "3":
                        formapagovalor = 2;
                        $("#divDiasPreferidos").show()
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "4":
                        formapagovalor = 1;
                        $("#divDiasPreferidos").hide()
                        $("#dia_pago_preferido").val('')
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "5":
                        formapagovalor = 3;
                        $("#divDiasPreferidos").hide()
                        $("#dia_pago_preferido").val('')
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "6":
                        formapagovalor = 2;
                        $("#divDiasPreferidos").hide()
                        $("#dia_pago_preferido").val('')
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "7":
                        formapagovalor = 2; // Catorcenal: 2 cuotas por mes
                        $("#divDiasPreferidos").hide()
                        $("#dia_pago_preferido").val('')
                        $("#divDiaSemanaPreferido").show()
                        $("#labelDiaSemana").html('Día de la Semana: <small><b>Para pagos catorcenales</b></small>')
                        break
                }
            }
            return formapagovalor
        }

        function getValorFechaAumentar(){
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

        async function generarTablaAmortizacion(searchString, position) {

            $("#tblAmortizacion tbody").empty()
            const formaPago = document.getElementById('formaPago').value

            let diasFeriados = await getDiasFeriados();
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

                if( resPlazo - Math.floor(resPlazo) > 0 )
                    Swal.fire('Advertencia','El número de cuotas según los parámetros ingresados generan valores decimales ['+resPlazo+"]",'warning')

                let anyo = fechaPrimerPago.split('-')[0]
                let mes = parseInt(fechaPrimerPago.split('-')[1])-1
                let dia = fechaPrimerPago.split('-')[2]


                const fechaInicial = new Date(anyo,mes,dia); // Fecha actual como ejemplo
                let fechaCopia = fechaInicial
                const banderaAumentar = getValorFechaAumentar(); // Número de días a agregar
                
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
                let diasPreferidos = document.getElementById('dia_pago_preferido').value
                let diaEvaluar = dia
                let diaPreferidoEvaluar = diasPreferidos
                if (diasPreferidos !== '' && (diasPreferidos > 31 || diasPreferidos <= 0 || diasPreferidos % 1 > 0)) {
                    Swal.fire('Error', 'El siguiente de dia de pago es incorrecto', 'error')
                    return
                }

                //*************+AGREGAR COMPROBACIÓN CUANDO LA PRIMERA FECHA SEA SABADO O DOMINGO*************//

                let arrFechasMes = [];
                let diaSemanaPactado = null; // Guardar el día de la semana pactado (0=domingo, 1=lunes, etc.)
                let diaSemanaPreferido = document.getElementById('diaSemanaPreferido').value; // Para SEMANAL y CATORCENAL
                
                for (let i=0;i <= resPlazo ;i++)
                {
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
                        
                        // Guardar el día de la semana de la primera cuota para SEMANAL y CATORCENAL
                        if (formaPago === "2" || formaPago === "7") {
                            // Si hay día preferido seleccionado para SEMANAL o CATORCENAL, usarlo
                            if ((formaPago === "2" || formaPago === "7") && diaSemanaPreferido) {
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
                            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);

                            // Para SEMANAL y CATORCENAL: Forzar al día de la semana pactado
                            if ((formaPago === "2" || formaPago === "7") && diaSemanaPactado !== null) {
                                let diferenciaDias = diaSemanaPactado - fechaCuota.getDay();
                                if (diferenciaDias < 0) diferenciaDias += 7;
                                if (diferenciaDias > 0 && diferenciaDias < 7) {
                                    fechaCuota.setDate(fechaCuota.getDate() + diferenciaDias);
                                }
                            }

                            // Ajustar si cae en feriado o domingo (una sola vez)
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

        function esFinDeSemana(fecha) {
            const diaSemana = fecha.getDay();
            return diaSemana === 0 || diaSemana === 6;
        }
        //

        function resumenPrestamo(){
            document.getElementById('resumenNombres').innerText = document.getElementById('nombres').value +" "+document.getElementById('apellidos').value
            document.getElementById('resumenCedula').innerText = document.getElementById('cedula').value
            document.getElementById('resumenTelefonos').innerText = document.getElementById('telefono1').value + " / " + document.getElementById('telefono2').value
            document.getElementById('resumenDireccion').innerText = document.getElementById('direccion').value
            document.getElementById('resumenSexo').innerText = document.getElementById('sexo').value
            document.getElementById('resumenDepartamento').innerText = document.getElementById('departamento').value + " / " + document.getElementById('municipio').value


            if(!document.getElementById('chkAsalariado').checked){
                document.getElementById('resumenNombreNegocio').innerText = document.getElementById('nombreNegocio').value
                document.getElementById('resumenDireccionNegocio').innerText = document.getElementById('negocioDireccion').value
                document.getElementById('resumenTelefonoNegocio').innerText = document.getElementById('telefonoNegocio').value
                document.getElementById('resumenUbicacionNegocio').innerText = document.getElementById('ubicacionNegocio').value
                document.getElementById('resumenDepartamentoNegocio').innerText = document.getElementById('depNegocio').value + " / " +document.getElementById('munNegocio').value
            }
            else{
                document.getElementById('resumenNombreNegocio').innerText = 'ASALARIADO'
                document.getElementById('resumenDireccionNegocio').innerText = 'ASALARIADO'
                document.getElementById('modalTelefonoNegocio').innerText = 'ASALARIADO'
                document.getElementById('resumenDepartamentoNegocio').innerText = 'ASALARIADO'
            }


            if (!document.getElementById('chkFiador').checked) {
                document.getElementById('resumenNombresFiador').innerText = document.getElementById('nombreFiador').value + " " + document.getElementById('apellidoFiador').value
                document.getElementById('resumenCedulaFiador').innerText = document.getElementById('cedulaFiador').value
                document.getElementById('resumenTelefonosFiador').innerText = document.getElementById('telefono1Fiador').value + " / " + document.getElementById('telefono2Fiador').value
                document.getElementById('resumenObservacionesFiador').innerText = document.getElementById('observacionesFiador').value
            } else {
                document.getElementById('resumenNombresFiador').innerText = 'SIN FIADOR'
                document.getElementById('resumenCedulaFiador').innerText = 'SIN FIADOR'
                document.getElementById('resumenTelefonosFiador').innerText = 'SIN FIADOR'
                document.getElementById('resumenObservacionesFiador').innerText = 'SIN FIADOR'
            }

            document.getElementById('resumenVendedor').innerText = $("#selectClientes").find(':selected').text()
            document.getElementById('resumenCobrador').innerText = $("#selectAgente").find(':selected').text()
            document.getElementById('resumenDesembolso').innerText = $("#selectDesembolso").find(':selected').text()
            document.getElementById('resumenFechaPrestamo').innerText = document.getElementById('fechaPrestamo').value
            document.getElementById('resumenFechaDesembolso').innerText = document.getElementById('fechaDesembolso').value
            document.getElementById('resumenMontoFinanciar').innerText = document.getElementById('montoFinanciar').value
            document.getElementById('resumenPlazoPrestamo').innerText = document.getElementById('plazoPago').value
            document.getElementById('resumenTasaInteres').innerText = document.getElementById('tasaInteres').value
            document.getElementById('resumenFormaPago').innerText = document.getElementById('formaPago').options[document.getElementById('formaPago').selectedIndex].text
            document.getElementById('resumenMontoTotalFinanciar').innerText = document.getElementById('montoTotalFinanciar').value
            document.getElementById('resumenMontoCuota').innerText = document.getElementById('montoCuota').value
            document.getElementById('resumenInteresesPagar').innerText = document.getElementById('interesPagar').value
            document.getElementById('resumenInteresMes').innerText = document.getElementById('interesMes').value
            document.getElementById('resumenTotalInteresesPagar').innerText = document.getElementById('totalIntereses').value

            document.getElementById('resumenFechaPrimerPago').innerText = document.getElementById('fechaPago').value
            document.getElementById('resumenObservacionesPrestamo').innerText = document.getElementById('comentarios').value
            document.getElementById('resumenFechaUltimaCuota').innerText = document.getElementById('fechaUltimoPago').value
        }


        async function savePrestamo() {
            if (!validarCamposPrestamo()) {
                try {
                    const frmData = $("#frmPrestamo").serialize()

                    const {data} = await axios.post('{{route('prestamos.store')}}', frmData)
                    Swal.fire({
                        title: 'Estado',
                        icon: 'success',
                        text: data.message,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    })
                    setTimeout(() => {
                        window.location = '{{route('prestamos.index')}}'
                    }, 2000)
                } catch (error) {
                    console.error('Error completo:', error);
                    let errorMessage = 'Ha ocurrido un error al guardar el préstamo';
                    
                    if (error.response) {
                        // El servidor respondió con un código de error
                        console.error('Error del servidor:', error.response.data);
                        console.error('Status:', error.response.status);
                        
                        if (error.response.data && error.response.data.message) {
                            errorMessage = error.response.data.message;
                        } else if (error.response.data && typeof error.response.data === 'string') {
                            errorMessage = error.response.data;
                        }
                    } else if (error.request) {
                        // La petición se hizo pero no hubo respuesta
                        console.error('No hubo respuesta del servidor');
                        errorMessage = 'No se pudo conectar con el servidor';
                    } else {
                        // Algo pasó al configurar la petición
                        console.error('Error:', error.message);
                        errorMessage = error.message;
                    }
                    
                    Swal.fire({
                        title: 'Error',
                        icon: 'error',
                        text: errorMessage,
                        confirmButtonText: 'Aceptar'
                    });
                }
            } else
                Swal.fire('Error', 'Deben ingresarse todos los campos', 'error')
        }

        async function getDiasFeriados(){
            let {data} = await axios.get('{{route('configuracion.feriados.getFeriados')}}')
            return data
        }

    </script>
@endsection
