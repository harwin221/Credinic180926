<button type="button" class="btn btn-sm btn-primary btn-back-action" data-url="{{isset($url)?$url:"#"}}" onclick="cerrarPestanaONavegar(this)">
    <i class="fa fa-backward"></i> Atrás
</button>

<script>
function cerrarPestanaONavegar(btn) {
    const url = btn.getAttribute('data-url');
    
    // Intentar cerrar la pestaña
    window.close();
    
    // Si después de un momento la ventana sigue abierta, navegar
    setTimeout(function() {
        if (!window.closed) {
            window.location.href = url;
        }
    }, 100);
}
</script>
