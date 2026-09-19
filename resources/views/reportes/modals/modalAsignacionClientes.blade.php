<!-- Modal Asignación Clientes -->
<div class="modal fade" id="modalAsignacionClientes" tabindex="-1" aria-labelledby="modalAsignacionClientesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignacionClientesLabel"><i class="fas fa-exchange-alt"></i> Transferencia Masiva de Cartera</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('POST', route('reportes.asignacionClientes.transferirCartera'))->id('formTransferenciaCartera')->open()}}
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Esta opción te permite transferir préstamos activos de forma <strong>masiva</strong> (por cobrador) o <strong>individual</strong> (buscando un cliente específico).
                </div>

                <div class="row">
                    <div class="col-md-12 mb-4">
                        <label for="ac_cliente_individual" class="form-label font-weight-bold">
                            <i class="fas fa-user text-primary"></i> Cliente (Opcional para transferencia individual):
                        </label>
                        {{html()->select('cliente_id', [''=>'-- Transferencia masiva (Opcional: Seleccione cliente para mover solo uno) --'] + $listaClientes, null)->class('form-control select2')->id('ac_cliente_individual')->style(['width' => '100%'])}}
                        <small class="text-muted">Si selecciona un cliente, solo se transferirán sus préstamos activos. Deje vacío para transferencia masiva.</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ac_cobrador_origen" class="form-label font-weight-bold">
                            <i class="fas fa-user-minus text-danger"></i> Cobrador Origen (De):
                        </label>
                        {{html()->select('cobrador_origen', [''=>'-- Seleccione cobrador origen --'] + $listaCobradores, null)->class('form-control select2')->id('ac_cobrador_origen')->style(['width' => '100%'])}}
                        <small class="text-muted">Requerido solo para transferencias masivas.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ac_cobrador_destino" class="form-label font-weight-bold">
                            <i class="fas fa-user-plus text-success"></i> Cobrador Destino (Para):
                        </label>
                        {{html()->select('cobrador_destino', [''=>'-- Seleccione cobrador destino --'] + $listaCobradores, null)->class('form-control select2')->id('ac_cobrador_destino')->required()->style(['width' => '100%'])}}
                        <small class="text-muted">Cobrador que recibirá los préstamos</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" id="btnSubmitTransferencia" class="btn btn-danger rounded-pill px-4 shadow-sm">
                    <i class="fas fa-exchange-alt mr-1"></i> Transferir Cartera
                </button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Arreglar buscador Select2 en Modales de Bootstrap
    $('#modalAsignacionClientes').on('shown.bs.modal', function () {
        $('#ac_cliente_individual, #ac_cobrador_origen, #ac_cobrador_destino').select2({
            dropdownParent: $('#modalAsignacionClientes'),
            theme: 'bootstrap-5'
        });
    });

    // Mensaje de confirmación dinámico con SweetAlert2
    const form = document.getElementById('formTransferenciaCartera');
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Detener envío inicial

        const clienteId = document.getElementById('ac_cliente_individual').value;
        const cobradorDestino = document.getElementById('ac_cobrador_destino').options[document.getElementById('ac_cobrador_destino').selectedIndex].text;
        
        let titulo = "";
        let mensaje = "";
        let icono = "question";

        if (clienteId) {
            const clienteNombre = document.getElementById('ac_cliente_individual').options[document.getElementById('ac_cliente_individual').selectedIndex].text;
            titulo = "Confirmar transferencia individual";
            mensaje = `¿Está seguro de transferir los préstamos de <b>${clienteNombre}</b> hacia <b>${cobradorDestino}</b>?`;
        } else {
            const cobradorOrigen = document.getElementById('ac_cobrador_origen').options[document.getElementById('ac_cobrador_origen').selectedIndex].text;
            titulo = "¡Acción Masiva!";
            mensaje = `Está por transferir <b>TODA</b> la cartera de <b>${cobradorOrigen}</b> hacia <b>${cobradorDestino}</b>.<br><br>¿Está totalmente seguro?`;
            icono = "warning";
        }

        Swal.fire({
            title: titulo,
            html: mensaje,
            icon: icono,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, transferir',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Enviar formulario si confirma
            }
        });
    });
});
</script>
