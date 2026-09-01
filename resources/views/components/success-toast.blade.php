@if(session('success'))
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="successToast">
            <div class="toast-header text-white border-0" style="background: #38c66c !important; background-color: #38c66c !important; background-image: none !important;">
                <div class="avatar avatar-xs avatar-label-light me-2">
                    <i class="fas fa-check fs-12"></i>
                </div>
                <strong class="me-auto">¡Éxito!</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-light">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xs avatar-label-success me-2">
                        <i class="fas fa-user-plus fs-12"></i>
                    </div>
                    <span class="text-muted">{{ session('success') }}</span>
                </div>
            </div>
        </div>
    </div>
@endif
