/**
 * Mobile fixes:
 * Cuando el teclado virtual del móvil aparece y el foco está
 * en un input dentro de un modal, hace scroll para que el campo
 * quede visible sobre el teclado.
 */
(function () {

    // Scroll al input activo cuando el teclado sube en móvil
    document.addEventListener('focusin', function (e) {
        const el = e.target;
        if (!el.matches('input, textarea, select')) return;

        const modal = el.closest('.modal.show');
        if (!modal) return;

        setTimeout(function () {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    });

})();
