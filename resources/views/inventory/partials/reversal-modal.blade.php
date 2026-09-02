<div class="modal fade" id="reversalMovementModal" tabindex="-1" aria-labelledby="reversalMovementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="reversalMovementForm" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-light">
                <h5 class="modal-title text-danger" id="reversalMovementModalLabel">
                    <i class="fas fa-undo me-2"></i>Revertir movimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-2">Vas a revertir el siguiente movimiento:</p>
                <div id="reversalMovementLabel" class="fw-semibold text-dark mb-3"></div>
                <div class="alert alert-warning d-flex gap-2 mb-0" role="alert">
                    <i class="fas fa-info-circle mt-1"></i>
                    <div>El movimiento original se conservará como evidencia. Al confirmar, Dentaris creará un movimiento compensatorio para devolver el stock al estado anterior.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-undo me-1"></i>Confirmar reversión</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('reversalMovementModal')?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;

        document.getElementById('reversalMovementForm').action = trigger.dataset.reversalUrl;
        document.getElementById('reversalMovementLabel').textContent = trigger.dataset.reversalLabel;
    });
</script>
