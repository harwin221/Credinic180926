// Fix para limpiar backdrops fantasma
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Limpiar backdrops al cargar
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(b => b.remove());
        console.log('Backdrops limpiados:', backdrops.length);
        
        // Limpiar body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
})();