@extends('layouts.master')

@section('title', 'Notificaciones')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Notificaciones</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Panel de Control</a></li>
                        <li class="breadcrumb-item active">Notificaciones</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">Centro de Notificaciones</h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                                <i class="ri-check-double-line me-1"></i> Marcar todas como leídas
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm active" data-filter="all">
                                    Todas
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-filter="unread">
                                    No leídas
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-filter="read">
                                    Leídas
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <div class="search-box">
                                    <input type="text" class="form-control" placeholder="Buscar notificaciones..." id="search-notifications">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de notificaciones -->
                    <div class="notifications-list">
                        <!-- Notificación de ejemplo -->
                        <div class="notification-item border rounded p-3 mb-3" data-status="unread">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                            <i class="ri-calendar-check-line"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="mb-1">Nueva cita programada</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary-subtle text-primary">Nueva</span>
                                            <small class="text-muted">Hace 2 horas</small>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-2">Se ha programado una nueva cita para el paciente Juan Pérez el 25 de septiembre a las 10:00 AM.</p>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="markAsRead(this)">
                                            <i class="ri-check-line me-1"></i> Marcar como leída
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNotification(this)">
                                            <i class="ri-delete-bin-line me-1"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notificación de ejemplo 2 -->
                        <div class="notification-item border rounded p-3 mb-3" data-status="read">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-success-subtle text-success rounded-circle">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="mb-1">Pago recibido</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success-subtle text-success">Leída</span>
                                            <small class="text-muted">Hace 1 día</small>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-2">Se ha recibido un pago de $150.00 del paciente María García por el tratamiento de limpieza dental.</p>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNotification(this)">
                                            <i class="ri-delete-bin-line me-1"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notificación de ejemplo 3 -->
                        <div class="notification-item border rounded p-3 mb-3" data-status="unread">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                            <i class="ri-alarm-warning-line"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="mb-1">Stock bajo</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-warning-subtle text-warning">Nueva</span>
                                            <small class="text-muted">Hace 3 horas</small>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-2">El producto "Anestesia Lidocaína 2%" tiene stock bajo. Quedan solo 5 unidades disponibles.</p>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="markAsRead(this)">
                                            <i class="ri-check-line me-1"></i> Marcar como leída
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNotification(this)">
                                            <i class="ri-delete-bin-line me-1"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje cuando no hay notificaciones -->
                        <div class="text-center py-5 d-none" id="no-notifications">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-light text-muted rounded-circle">
                                    <i class="ri-notification-off-line fs-24"></i>
                                </div>
                            </div>
                            <h5 class="text-muted">No hay notificaciones</h5>
                            <p class="text-muted">No tienes notificaciones pendientes en este momento.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones para manejar notificaciones
function markAsRead(button) {
    const notificationItem = button.closest('.notification-item');
    const badge = notificationItem.querySelector('.badge');
    
    // Cambiar estado visual
    notificationItem.setAttribute('data-status', 'read');
    badge.className = 'badge bg-success-subtle text-success';
    badge.textContent = 'Leída';
    
    // Ocultar botón de marcar como leída
    button.style.display = 'none';
    
    // Aquí se haría la llamada AJAX al servidor
    console.log('Marcando notificación como leída...');
}

function markAllAsRead() {
    const unreadNotificaciones = document.querySelectorAll('.notification-item[data-status="unread"]');
    
    unreadNotificaciones.forEach(item => {
        const badge = item.querySelector('.badge');
        const markReadBtn = item.querySelector('button[onclick*="markAsRead"]');
        
        item.setAttribute('data-status', 'read');
        badge.className = 'badge bg-success-subtle text-success';
        badge.textContent = 'Leída';
        
        if (markReadBtn) {
            markReadBtn.style.display = 'none';
        }
    });
    
    // Aquí se haría la llamada AJAX al servidor
    console.log('Marcando todas las notificaciones como leídas...');
}

function deleteNotification(button) {
    const notificationItem = button.closest('.notification-item');
    
    if (confirm('¿Estás seguro de que quieres eliminar esta notificación?')) {
        notificationItem.remove();
        
        // Verificar si quedan notificaciones
        const remainingNotificaciones = document.querySelectorAll('.notification-item');
        if (remainingNotificaciones.length === 0) {
            document.getElementById('no-notifications').classList.remove('d-none');
        }
        
        // Aquí se haría la llamada AJAX al servidor
        console.log('Eliminando notificación...');
    }
}

// Filtros de notificaciones
document.querySelectorAll('[data-filter]').forEach(button => {
    button.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        
        // Actualizar botones activos
        document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        
        // Filtrar notificaciones
        const notifications = document.querySelectorAll('.notification-item');
        notifications.forEach(notification => {
            const status = notification.getAttribute('data-status');
            
            if (filter === 'all' || status === filter) {
                notification.style.display = 'block';
            } else {
                notification.style.display = 'none';
            }
        });
    });
});

// Búsqueda de notificaciones
document.getElementById('search-notifications').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const notifications = document.querySelectorAll('.notification-item');
    
    notifications.forEach(notification => {
        const text = notification.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            notification.style.display = 'block';
        } else {
            notification.style.display = 'none';
        }
    });
});
</script>
@endsection





