<style>
    /* Estilos personalizados para el toast de éxito */
    .toast {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        min-width: 240px;
        max-width: 280px;
    }
    
    .toast-header {
        border-radius: 0.5rem 0.5rem 0 0;
        padding: 0.6rem 0.8rem;
        background-color: #38c66c !important;
        background: #38c66c !important;
        background-image: none !important;
    }
    
    .toast-body {
        border-radius: 0 0 0.5rem 0.5rem;
        padding: 0.6rem 0.8rem;
    }
    
    .toast-container {
        animation: bounceIn 0.8s ease-out;
    }
    
    @keyframes bounceIn {
        0% {
            transform: scale(0.3) translateY(-50px);
            opacity: 0;
        }
        50% {
            transform: scale(1.05) translateY(0);
            opacity: 0.8;
        }
        70% {
            transform: scale(0.95) translateY(0);
            opacity: 0.9;
        }
        100% {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
    }
    
    .toast.fade-out {
        animation: fadeOut 0.5s ease-out forwards;
    }
    
    .toast.show {
        animation: none;
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.9);
        }
    }
    
    /* Responsive */
    @media (max-width: 576px) {
        .toast {
            min-width: 224px;
            max-width: 256px;
        }
        
        .toast-container {
            top: 1rem !important;
            right: 1rem !important;
        }
    }
</style>
