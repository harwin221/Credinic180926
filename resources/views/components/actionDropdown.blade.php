{{-- Componente de Dropdown de Acciones con Tres Puntos --}}
<div class="dropup action-dropdown-container">
    <button class="btn-action-dropdown dropdown-toggle-no-caret" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
        <i class="fas fa-ellipsis-h"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-actions dropdown-menu-end">
        {{ $slot }}
    </ul>
</div>
