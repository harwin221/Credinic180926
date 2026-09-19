<!-- Modal Estado Cuenta Cliente -->
<div class="modal fade" id="modalEstadoCuenta" tabindex="-1" aria-labelledby="modalEstadoCuentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstadoCuentaLabel"><i class="fas fa-file-alt"></i> Estado de Cuenta Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('reportes.estadoCuentaCliente'))->id('formEstadoCuenta')->open()}}
            <input type="hidden" name="exportar" value="1">
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="ecc_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [''=>'-- Seleccione --'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('ecc_cliente')->required()->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="ecc_prestamos"><strong>Préstamo:</strong></label>
                        <select name="prestamos" class="form-control select2" id="ecc_prestamos" required style="width: 100%">
                            <option value="">-- Seleccione primero un cliente --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGenerarEstadoCuenta"><i class="fas fa-file-pdf"></i> Generar Reporte</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>

<style>
/* Asegurar que el dropdown de Select2 aparezca encima del modal */
.select2-container--open {
    z-index: 9999 !important;
}
.select2-dropdown {
    z-index: 9999 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que el modal se muestre para inicializar Select2
    $('#modalEstadoCuenta').on('shown.bs.modal', function () {
        // Inicializar Select2 en ambos selects solo si no están inicializados
        if (!$('#ecc_cliente').hasClass("select2-hidden-accessible")) {
            $('#ecc_cliente').select2({
                dropdownParent: $('#modalEstadoCuenta'),
                width: '100%',
                placeholder: '-- Seleccione --'
            });
        }
        
        if (!$('#ecc_prestamos').hasClass("select2-hidden-accessible")) {
            $('#ecc_prestamos').select2({
                dropdownParent: $('#modalEstadoCuenta'),
                width: '100%',
                placeholder: '-- Seleccione primero un cliente --'
            });
        }
    });
    
    $('#ecc_cliente').on('change', function() {
        const clienteId = $(this).val();
        if (clienteId) {
            // Mostrar loading
            const select = $('#ecc_prestamos');
            select.select2('destroy');
            select.empty();
            select.append('<option value="">Cargando préstamos...</option>');
            select.select2({
                dropdownParent: $('#modalEstadoCuenta'),
                width: '100%',
                placeholder: 'Cargando...'
            });
            
            // Cargar préstamos del cliente seleccionado
            fetch(`/reportes/plan-pago/prestamos/${clienteId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    const select = $('#ecc_prestamos');
                    
                    // Destruir Select2 antes de modificar opciones
                    select.select2('destroy');
                    
                    // Limpiar y agregar nuevas opciones
                    select.empty();
                    
                    if (Object.keys(data).length === 0) {
                        select.append('<option value="">-- Este cliente no tiene préstamos --</option>');
                    } else {
                        select.append('<option value="">-- Seleccione --</option>');
                        Object.entries(data).forEach(([id, nombre]) => {
                            select.append(`<option value="${id}">${nombre}</option>`);
                        });
                    }
                    
                    // Reinicializar Select2
                    select.select2({
                        dropdownParent: $('#modalEstadoCuenta'),
                        width: '100%',
                        placeholder: '-- Seleccione --'
                    });
                })
                .catch(error => {
                    console.error('Error al cargar préstamos:', error);
                    const select = $('#ecc_prestamos');
                    select.select2('destroy');
                    select.empty();
                    select.append('<option value="">-- Error al cargar préstamos --</option>');
                    select.select2({
                        dropdownParent: $('#modalEstadoCuenta'),
                        width: '100%'
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los préstamos del cliente'
                    });
                });
        } else {
            // Si no hay cliente seleccionado, limpiar préstamos
            const select = $('#ecc_prestamos');
            select.select2('destroy');
            select.empty();
            select.append('<option value="">-- Seleccione primero un cliente --</option>');
            select.select2({
                dropdownParent: $('#modalEstadoCuenta'),
                width: '100%',
                placeholder: '-- Seleccione primero un cliente --'
            });
        }
    });
    
    // Manejar el submit del formulario
    $('#formEstadoCuenta').on('submit', function(e) {
        e.preventDefault();
        
        const cliente = $('#ecc_cliente').val();
        const prestamo = $('#ecc_prestamos').val();
        
        if (!cliente || !prestamo) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor seleccione un cliente y un préstamo'
            });
            return false;
        }
        
        // Construir URL con parámetros
        const url = `{{ route('reportes.estadoCuentaCliente') }}?cliente=${cliente}&prestamos=${prestamo}&exportar=1`;
        
        // Abrir en nueva pestaña
        window.open(url, '_blank');
        
        // Cerrar modal
        const modalEstadoCuenta = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEstadoCuenta'));
        modalEstadoCuenta.hide();
        
        return false;
    });
});
</script>
