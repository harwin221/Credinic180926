<!-- Modal Plan de Pago -->
<div class="modal fade" id="modalPlanPago" tabindex="-1" aria-labelledby="modalPlanPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPlanPagoLabel"><i class="fas fa-clipboard-list"></i> Plan de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.planPago'))->id('formPlanPago')->open()}}
            <input type="hidden" name="exportar" value="1">
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="pp_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [''=>'-- Seleccione --'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('pp_cliente')->required()->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="pp_prestamos"><strong>Préstamo:</strong></label>
                        <select name="prestamos" class="form-control select2" id="pp_prestamos" required style="width: 100%">
                            <option value="">-- Seleccione primero un cliente --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGenerarPlanPago"><i class="fas fa-file-pdf"></i> Generar Reporte</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Select2 en ambos selects
    $('#pp_cliente').select2({
        dropdownParent: $('#modalPlanPago'),
        width: '100%',
        placeholder: '-- Seleccione --'
    });
    
    $('#pp_prestamos').select2({
        dropdownParent: $('#modalPlanPago'),
        width: '100%',
        placeholder: '-- Seleccione primero un cliente --'
    });
    
    $('#pp_cliente').on('change', function() {
        const clienteId = $(this).val();
        if (clienteId) {
            // Cargar préstamos del cliente seleccionado
            fetch(`/reportes/plan-pago/prestamos/${clienteId}`)
                .then(response => response.json())
                .then(data => {
                    const select = $('#pp_prestamos');
                    
                    // Destruir Select2 antes de modificar opciones
                    select.select2('destroy');
                    
                    // Limpiar y agregar nuevas opciones
                    select.empty();
                    select.append('<option value="">-- Seleccione --</option>');
                    Object.entries(data).forEach(([id, nombre]) => {
                        select.append(`<option value="${id}">${nombre}</option>`);
                    });
                    
                    // Reinicializar Select2
                    select.select2({
                        dropdownParent: $('#modalPlanPago'),
                        width: '100%',
                        placeholder: '-- Seleccione --'
                    });
                })
                .catch(error => {
                    console.error('Error al cargar préstamos:', error);
                });
        } else {
            // Si no hay cliente seleccionado, limpiar préstamos
            const select = $('#pp_prestamos');
            select.select2('destroy');
            select.empty();
            select.append('<option value="">-- Seleccione primero un cliente --</option>');
            select.select2({
                dropdownParent: $('#modalPlanPago'),
                width: '100%',
                placeholder: '-- Seleccione primero un cliente --'
            });
        }
    });
    
    // Manejar el submit del formulario
    $('#formPlanPago').on('submit', function(e) {
        e.preventDefault();
        
        const cliente = $('#pp_cliente').val();
        const prestamo = $('#pp_prestamos').val();
        
        if (!cliente || !prestamo) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor seleccione un cliente y un préstamo'
            });
            return false;
        }
        
        // Construir URL con parámetros
        const url = `{{ route('reportes.planPago') }}?cliente=${cliente}&prestamos=${prestamo}&exportar=1`;
        
        // Abrir en nueva pestaña
        window.open(url, '_blank');
        
        // Cerrar modal
        const modalPlanPago = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPlanPago'));
        modalPlanPago.hide();
        
        return false;
    });
});
</script>
