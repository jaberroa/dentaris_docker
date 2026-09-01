<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar toast de éxito
    const successToast = document.getElementById('successToast');
    if (successToast) {
        const toast = new bootstrap.Toast(successToast, {
            autohide: false,
            delay: 0
        });
        toast.show();
        
        // Aplicar transición de desvanecimiento después de 3 segundos
        setTimeout(function() {
            successToast.classList.add('fade-out');
            
            // Remover el elemento después de la animación
            setTimeout(function() {
                successToast.remove();
            }, 500); // Duración de la animación fadeOut
        }, 3000);
    }
});
</script>
