@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.homeAgentes')])
    Nuevo Abono
@endsection
@section('content')

    <div class="modal fade" id="clientesModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Listado de clientes</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="buscar"><b>Buscar Cliente:</b></label>
                                <input type="text" id="buscar" placeholder="Buscar Cliente" name="buscar" class="form-control">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div id="divClientesModal">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <a href="#" class="btn btn-sm btn-primary" id="btnBuscarCliente">Buscar Cliente <i class="fa fa-search"></i> </a>
            @if(isset($prestamo))
                <span class="badge bg-success">Préstamo pre-cargado</span>
            @endif
        </div>
    </div>
    <br>
    <br>
    <div class="row">
        <div class="col-md-12">
            <h5>Información del Cliente:</h5>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <div class="my-auto">
                <div class="form-group text-center">
                    <img src="{{ (isset($prestamo) && $prestamo->cliente) ? $prestamo->cliente->user_image : (isset($cliente) ? $cliente->user_image : asset('assets/img/clientesFotos/no-photo.jpg'))}}" class="img-thumbnail" alt="" id="imgCliente">
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="nombreCliente" class="fw-bold">Nombres:</label>
                        <span class="form-control bg-secondary-light" id="nombreCliente">{{ (isset($prestamo) && $prestamo->cliente) ? $prestamo->cliente->nombres : (isset($cliente) ? $cliente->nombres : '-') }}</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="apellidoCliente" class="fw-bold">Apellidos:</label>
                        <span class="form-control bg-secondary-light" id="apellidoCliente">{{ (isset($prestamo) && $prestamo->cliente) ? $prestamo->cliente->apellidos : (isset($cliente) ? $cliente->apellidos : '-') }}</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cedulaCliente" class="fw-bold">Cédula:</label>
                        <span class="form-control bg-secondary-light" id="cedulaCliente">{{ (isset($prestamo) && $prestamo->cliente) ? $prestamo->cliente->cedula : (isset($cliente) ? $cliente->cedula : '-') }}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="direccionCliente" class="fw-bold">Dirección:</label>
                        <span class="form-control bg-secondary-light" id="direccionCliente">{{ (isset($prestamo) && $prestamo->cliente) ? $prestamo->cliente->direccion : (isset($cliente) ? $cliente->direccion : '-') }}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="telefonoCliente" class="fw-bold">Teléfono:</label>
                        <span class="form-control bg-secondary-light" id="telefonoCliente">{{ (isset($prestamo) && $prestamo->cliente) ? $prestamo->cliente->telefono1 : (isset($cliente) ? $cliente->telefono1 : '-') }}</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="celularCliente" class="fw-bold">Celular:</label>
                        <span class="form-control bg-secondary-light" id="celularCliente">{{ (isset($prestamo) && $prestamo->cliente) ? $prestamo->cliente->telefono2 : (isset($cliente) ? $cliente->telefono2 : '-') }}</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="departamentoCliente" class="fw-bold">Departamento:</label>
                        <span class="form-control bg-secondary-light" id="departamentoCliente">-</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="municipioCliente" class="fw-bold">Municipio:</label>
                        <span class="form-control bg-secondary-light" id="municipioCliente">-</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="form-group">
                    <label for="nombreCliente" class="fw-bold">Préstamos Activos</label>
                    {{html()->select('prestamo',[])->class('form-control select2')->style('width:100%')->id('selectPrestamos')}}
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="tblCuotas">

            </div>
        </div>
    </div>
    <br>
    @include('agentesViews.abonos.modals.modalAbono')
@endsection
@section('script')
    <script>
        const btnBuscarCliente = document.getElementById('btnBuscarCliente');
        
        // Variables para saber si viene pre-cargado
        const prestamoPrecargado = @json(isset($prestamo) ? $prestamo : null);

        document.addEventListener('DOMContentLoaded',function (){
            getListClientes(1,'');
            
            // Si viene un préstamo pre-cargado, cargarlo automáticamente
            if (prestamoPrecargado) {
                cargarPrestamoPrecargado();
            }
        })
        
        async function cargarPrestamoPrecargado() {
            try {
                console.log('Cargando préstamo pre-cargado:', prestamoPrecargado);
                
                // Cargar información del cliente
                document.getElementById('nombreCliente').innerText = prestamoPrecargado.cliente.nombres || '-';
                document.getElementById('apellidoCliente').innerText = prestamoPrecargado.cliente.apellidos || '-';
                document.getElementById('cedulaCliente').innerText = prestamoPrecargado.cliente.cedula || '-';
                document.getElementById('direccionCliente').innerText = prestamoPrecargado.cliente.direccion || '-';
                document.getElementById('telefonoCliente').innerText = prestamoPrecargado.cliente.telefono1 || '-';
                document.getElementById('celularCliente').innerText = prestamoPrecargado.cliente.telefono2 || '-';
                
                // Cargar departamento y municipio si existen
                if (prestamoPrecargado.cliente.departamento_municipio) {
                    document.getElementById('departamentoCliente').innerText = prestamoPrecargado.cliente.departamento_municipio.departamento?.nombre || '-';
                    document.getElementById('municipioCliente').innerText = prestamoPrecargado.cliente.departamento_municipio.nombre || '-';
                } else {
                    document.getElementById('departamentoCliente').innerText = '-';
                    document.getElementById('municipioCliente').innerText = '-';
                }
                
                // Cargar imagen del cliente
                if (prestamoPrecargado.cliente.user_image) {
                    document.getElementById('imgCliente').src = prestamoPrecargado.cliente.user_image;
                }
                
                // Cargar el select de préstamos con el préstamo pre-cargado
                $("#selectPrestamos").empty();
                let opcion = `<option value="${prestamoPrecargado.id}" selected>N° ${prestamoPrecargado.consecutivo} - Monto: ${prestamoPrecargado.monto_financiado}</option>`;
                $("#selectPrestamos").append(opcion);
                
                // Trigger change para cargar las cuotas
                $("#selectPrestamos").trigger('change');
                
                console.log('Préstamo pre-cargado exitosamente');
            } catch (error) {
                console.error('Error al cargar préstamo pre-cargado:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el préstamo pre-cargado'
                });
            }
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

            document.querySelectorAll('.btnSelCliente').forEach((button) => {
                button.addEventListener('click',handleClickCliente)
            })

            document.querySelectorAll('.btnSelCliente').forEach((button) => {
                button.addEventListener('click',handleClickCliente)
            })

            modal.show()
        }

        function limpiarModalAbono()
        {
            document.getElementById('dtFechaCuota').value = ''
            document.getElementById('rbCuota').checked = false
            // document.getElementById('rbIntereses').checked = false
            document.getElementById('rbOtroMonto').checked = false

            document.getElementById('txtPagoCuota').value = ""
            document.getElementById('txtOtroMonto').value = ""
            // document.getElementById('txtPagoInteres').value = ""

            document.getElementById('efectivoMonto').value = ""
            // document.getElementById('tarjetaMonto').value = ""
            // document.getElementById('transferenciaMonto').value = ""
            // document.getElementById('chequeMonto').value = ""
            document.getElementById('txtObservaciones').value = ""

            document.getElementById('totalPagar').innerHTML = ""

            document.getElementById('rbEfectivo').checked = false
            // document.getElementById('rbTransferencia').checked = false
            // document.getElementById('rbCheque').checked = false
            // document.getElementById('rbTarjeta').checked = false

            document.getElementById('efectivoMonto').setAttribute('disabled','disabled')
            // document.getElementById('tarjetaMonto').setAttribute('disabled','disabled')
            // document.getElementById('transferenciaMonto').setAttribute('disabled','disabled')
            // document.getElementById('chequeMonto').setAttribute('disabled','disabled')
        }

        $(document).on('click', '.pagination a', function(e){
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            getListClientes(page,'')
        });

        document.getElementById('buscar').addEventListener('keyup', function () {
            getListClientes(1, this.value)
        })

        btnBuscarCliente.addEventListener('click',function (){
            openModal('clientesModal')
        })

        async function getListClientes(page,buscar)
        {
            let url = '{{route('agentes.user.clientes.getListClientes')}}'+'?page='+page+'&buscar='+buscar
            let {data} = await axios.get(url)
            document.getElementById('divClientesModal').innerHTML = data

            document.querySelectorAll('.btnSelCliente').forEach((button) => {
                button.addEventListener('click',handleClickCliente)
            })

            document.querySelectorAll('.btnSelCliente').forEach((button) => {
                button.addEventListener('click',handleClickCliente)
            })
        }

        const handleClickCliente = async (event)=>{
            let {data} = await axios.post('{{route('agentes.abonos.getInfoCliente')}}',
                {
                    'clienteId': event.target.closest('a', '.btnBuscarCliente').dataset.id
                }
            )

            document.getElementById('nombreCliente').innerText = data.userData.nombres?data.userData.nombres:'-'
            document.getElementById('apellidoCliente').innerText = data.userData.apellidos?data.userData.apellidos:'-'
            document.getElementById('cedulaCliente').innerText = data.userData.cedula?data.userData.cedula:'-'
            document.getElementById('direccionCliente').innerText = data.userData.direccion?data.userData.direccion:'-'
            document.getElementById('telefonoCliente').innerText = data.userData.telefono1?data.userData.telefono1:'-'
            document.getElementById('celularCliente').innerText = data.userData.telefono2?data.userData.telefono2:'-'
            document.getElementById('departamentoCliente').innerText = data.userData.dep?data.userData.dep:'-'
            document.getElementById('municipioCliente').innerText = data.userData.mun?data.userData.mun:'-'

            $("#selectPrestamos").empty()


            let opcion = `<option value=""> *Seleccione* </option>`
            $("#selectPrestamos").append(opcion)

            if (data.prestamoActivos.length > 0) {
                data.prestamoActivos.forEach(function (value) {
                    let opcion = `<option value="${value.id}"> N° ${value.consecutivo} - Monto: ${value.monto_financiado} </option>`
                    $("#selectPrestamos").append(opcion)
                })
            }

            closeModal('clientesModal')
        }

        async function getPrestamosCliente(clienteId)
        {
            let url = '{{route('agentes.user.clientes.getListClientes')}}'+'?page='+page+'&buscar='+buscar
            let {data} = await axios.get(url)
            document.getElementById('divClientesModal').innerHTML = data
        }

        $("#selectPrestamos").on('change', async function (event) {
            let {data} = await axios.post('{{route('agentes.abonos.getCuotasPrestamo')}}',
                {
                    'prestamoId': $(this).val()
                }
            )
            document.getElementById('tblCuotas').innerHTML = data

        })

        const handleChangeEventDelegation = async (event)=>{
            const element = event.target

            if (element.id === 'chkSelAll') {
                document.querySelectorAll(".chkCuota").forEach((el) => {
                    el.checked = element.checked
                })
            }

            if(element.id==="btnAbonarCuota"){
                document.getElementById('txtAbonoId').value = ""
                let checkboxes = document.querySelectorAll('input[name="chkCuotas"]:checked');
                let seleccionados = []
                if (checkboxes.length > 0) {
                    checkboxes.forEach(function (checkbox) {
                        seleccionados.push(checkbox.value);
                    });

                    limpiarModalAbono()
                    let {data} = await axios.post('{{route('agentes.abonos.getDetalleCuota')}}',{
                        'cuotas':seleccionados,
                    })
                    document.getElementById('txtPagoCuota').value = data.moneda +" "+ data.total_pendiente.toFixed(2)
                    document.getElementById('txtOtroMonto').max = data.total_pendiente
                    document.getElementById('txtAbonoId').value = JSON.stringify(seleccionados)
                    openModal('modalAbono')

                }else{
                    Swal.fire('Advertencia','Debe seleccionar al menos una cuota','warning')
                }
            }

            if(element.id === "btnAbonarOtroMonto"){
                document.getElementById('txtAbonoId').value = ""
                limpiarModalAbono()
                let {data} = await axios.post('{{route('agentes.abonos.getDetalleCuota')}}',{
                    'prestamoId': $("#selectPrestamos").val()
                })
                document.getElementById('txtPagoCuota').value = data.moneda +" "+ data.total_pendiente.toFixed(2)
                document.getElementById('txtOtroMonto').max = data.total_pendiente
                document.getElementById('txtAbonoId').value = $("#selectPrestamos").val()
                openModal('modalAbono')
            }
        }

        document.getElementById('tblCuotas').addEventListener('click',handleChangeEventDelegation)

        document.getElementById('btnRealizarAbono').addEventListener('click',async function (event){
            if(validarCamposAbono()){
                document.getElementById('btnRealizarAbono').disabled = true

                const frmAbono = document.getElementById('frmAbono')

                let frmData = new FormData(frmAbono);
                let queryString = new URLSearchParams(frmData).toString()

                try {
                    const response =    await axios.post(frmAbono.action, {
                        cuotaId: document.getElementById('txtAbonoId').value,
                        values:queryString
                    })
                    // Swal.fire("success","Abono Creado correctamente","success")

                    Swal.fire({
                        title: 'Abono creado',
                        text: "El abono ha sido creado correctamente. ¿Desea imprimir el recibo?",
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonColor: '#3085d6',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Imprimir',
                        cancelButtonText: 'No Imprimir'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const btnRecibo = document.createElement('a');
                            btnRecibo.setAttribute('href', response.data);
                            btnRecibo.setAttribute('target', "_blank");
                            btnRecibo.click()
                        }
                    })
                    limpiarModalAbono()
                    $("#selectPrestamos").trigger('change')
                    document.getElementById('btnRealizarAbono').disabled = false
                    setTimeout(()=>{
                        closeModal("modalAbono")
                    },500)
                }catch (ex)
                {
                    Swal.fire("warning","Ha ocurrido un error al intentar guardar el abono: "+ex.response.data,"warning")
                    document.getElementById('btnRealizarAbono').disabled = false
                }
            }
        })

        function validarCamposAbono(){
            const rbCuota = document.getElementById('rbCuota')
            // const rbIntereses = document.getElementById('rbIntereses')
            // const rbCancelar = document.getElementById('rbCancelar')
            const rbOtroMonto = document.getElementById('rbOtroMonto')

            const rbEfectivo = document.getElementById('rbEfectivo')
            // const rbTarjeta = document.getElementById('rbTarjeta')
            // const rbTransferencia = document.getElementById('rbTransferencia')
            // const rbCheque = document.getElementById('rbCheque')
            const txtOtroMonto = document.getElementById('txtOtroMonto')

            // if(!rbCuota.checked && !rbIntereses.checked && !rbOtroMonto.checked){
            //     Swal.fire('Advertencia','Debe seleccionar el tipo abono','warning')
            //     return false
            // }
            if(!rbEfectivo.checked && !rbTarjeta.checked && !rbTransferencia.checked && !rbCheque.checked)
            {
                Swal.fire('Advertencia','Debe seleccionar al menos una forma de pago','warning')
                return false
            }

            let montoLimpio = document.getElementById('txtPagoCuota').value.replace('C$','')
            montoLimpio = montoLimpio.replace('U$','')

            if(rbOtroMonto.checked && parseFloat(txtOtroMonto.value) > parseFloat(montoLimpio) ){
                Swal.fire('Advertencia','El monto a abonar no debe superar el valor de la cuota seleccionada','warning')
                return false
            }

            let montoLimpio2 = document.getElementById('totalPagar').innerHTML.replace('C$','')
            montoLimpio2 = montoLimpio2.replace('U$','')


            let txtEfectivo = 0
            let txtTransferencia = 0
            let txtCheque = 0
            let txtTarjeta = 0

            let sumaMontos = 0

            if (!isNaN(parseFloat(document.getElementById('efectivoMonto').value))) {
                txtEfectivo = parseFloat(document.getElementById('efectivoMonto').value)
            }
            //
            // if (!isNaN(parseFloat(document.getElementById('chequeMonto').value))) {
            //     txtCheque = parseFloat(document.getElementById('chequeMonto').value)
            // }
            //
            // if (!isNaN(parseFloat(document.getElementById('tarjetaMonto').value))) {
            //     txtTarjeta = parseFloat(document.getElementById('tarjetaMonto').value)
            // }
            //
            // if (!isNaN(parseFloat(document.getElementById('transferenciaMonto').value))) {
            //     txtTransferencia = parseFloat(document.getElementById('transferenciaMonto').value)
            // }
            sumaMontos = txtEfectivo + txtTransferencia + txtCheque + txtTarjeta

            montoLimpio2 = montoLimpio2.replace(',','');

            // console.log(sumaMontos,montoLimpio2,parseFloat(sumaMontos))
            if(sumaMontos !== parseFloat(montoLimpio2) ){
                Swal.fire('Advertencia','Los saldos ingresados no coinciden','warning')
                return false
            }

            return true

        }


        //MODAL ABONO
        document.getElementById('rbEfectivo').addEventListener('change',function (event){
            const txtMonto = document.getElementById('efectivoMonto')
            if (event.target.checked) {
                txtMonto.removeAttribute('disabled')
            } else {
                txtMonto.value = ""
                txtMonto.setAttribute('disabled', 'disabled')
            }
        })

        // document.getElementById('rbTarjeta').addEventListener('change',function (event){
        //     const txtMonto = document.getElementById('tarjetaMonto')
        //     if (event.target.checked) {
        //         txtMonto.removeAttribute('disabled')
        //     } else {
        //         txtMonto.value = ""
        //         txtMonto.setAttribute('disabled', 'disabled')
        //     }
        // })
        //
        // document.getElementById('rbTransferencia').addEventListener('change',function (event){
        //     const txtMonto = document.getElementById('transferenciaMonto')
        //     if (event.target.checked) {
        //         txtMonto.removeAttribute('disabled')
        //     } else {
        //         txtMonto.value = ""
        //         txtMonto.setAttribute('disabled', 'disabled')
        //     }
        // })
        //
        // document.getElementById('rbCheque').addEventListener('change',function (event){
        //     const txtMonto = document.getElementById('chequeMonto')
        //     if (event.target.checked) {
        //         txtMonto.removeAttribute('disabled')
        //     } else {
        //         txtMonto.value = ""
        //         txtMonto.setAttribute('disabled', 'disabled')
        //     }
        // })

        document.getElementById('rbCuota').addEventListener('change',function (){
            const totalPagar = document.getElementById('totalPagar')
            const montoCuota = document.getElementById('txtPagoCuota')
            const otroMonto = document.getElementById('txtOtroMonto')
            if(this.checked){
                totalPagar.innerHTML = montoCuota.value
                otroMonto.setAttribute('disabled','disabled')
            }
        })

        document.getElementById('rbOtroMonto').addEventListener('change',function (){
            const totalPagar = document.getElementById('totalPagar')
            const montoCuota = document.getElementById('txtOtroMonto')
            if(this.checked){
                totalPagar.innerHTML = montoCuota.value
                montoCuota.removeAttribute('disabled')
            }
        })

        document.getElementById('txtOtroMonto').addEventListener('keyup',function (){
            const totalPagar = document.getElementById('totalPagar')
            const txtOtroMonto = parseFloat(this.value)

            totalPagar.innerHTML = txtOtroMonto.toLocaleString('es-MX')
        })

    </script>
@endsection
