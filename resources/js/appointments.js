/**
 * Funciones comunes para el módulo de appointments
 * Autor: Sistema Dentaris
 * Versión: 1.0.0
 */

// Configuración global
const AppointmentsConfig = {
    select2: {
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            },
            inputTooShort: function() {
                return "Ingresa al menos 2 caracteres";
            }
        },
        minimumInputLength: 2,
        delay: 300,
        cache: true
    },
    toast: {
        duration: 3000,
        animationDuration: 500
    },
    calendar: {
        keyboardNavigation: true,
        tooltips: true
    }
};

/**
 * Inicializar Select2 para campos de pacientes
 */
function initializePatientSelect2(selector, options = {}) {
    const defaultOptions = {
        placeholder: 'Buscar paciente...',
        allowClear: true,
        language: AppointmentsConfig.select2.language,
        minimumInputLength: AppointmentsConfig.select2.minimumInputLength,
        ajax: {
            url: '/patients/search',
            dataType: 'json',
            delay: AppointmentsConfig.select2.delay,
            xhrFields: {
                withCredentials: true
            },
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                
                return {
                    results: data.data.map(function(patient) {
                        return {
                            id: patient.id,
                            text: patient.first_name + ' ' + patient.last_name + ' - ' + patient.display_code,
                            patient: patient
                        };
                    }),
                    pagination: {
                        more: data.current_page < data.last_page
                    }
                };
            },
            cache: AppointmentsConfig.select2.cache
        },
        templateResult: function(patient) {
            if (patient.loading) {
                return patient.text;
            }
            
            var $result = $(
                '<div class="d-flex align-items-center">' +
                    '<div class="avatar avatar-xs avatar-label-primary me-2">' +
                        '<i class="fas fa-user text-primary"></i>' +
                    '</div>' +
                    '<div>' +
                        '<div class="fw-semibold">' + patient.patient.first_name + ' ' + patient.patient.last_name + '</div>' +
                        '<small class="text-muted">' + patient.patient.display_code + ' • ' + patient.patient.phone + '</small>' +
                    '</div>' +
                '</div>'
            );
            
            return $result;
        },
        templateSelection: function(patient) {
            if (patient.id === '') {
                return patient.text;
            }
            
            return patient.patient ? 
                patient.patient.first_name + ' ' + patient.patient.last_name + ' - ' + patient.patient.display_code :
                patient.text;
        }
    };

    const finalOptions = Object.assign({}, defaultOptions, options);
    $(selector).select2(finalOptions);
}

/**
 * Inicializar Select2 para campos de personal médico
 */
function initializeStaffSelect2(selector, options = {}) {
    const defaultOptions = {
        placeholder: 'Buscar odontólogo...',
        allowClear: true,
        language: AppointmentsConfig.select2.language,
        minimumInputLength: AppointmentsConfig.select2.minimumInputLength,
        ajax: {
            url: '/appointments/search-staff',
            dataType: 'json',
            delay: AppointmentsConfig.select2.delay,
            xhrFields: {
                withCredentials: true
            },
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                
                return {
                    results: data.data.map(function(staff) {
                        return {
                            id: staff.id,
                            text: staff.display_name + ' - ' + staff.specialty,
                            staff: staff
                        };
                    }),
                    pagination: {
                        more: data.current_page < data.last_page
                    }
                };
            },
            cache: AppointmentsConfig.select2.cache
        },
        templateResult: function(staff) {
            if (staff.loading) {
                return staff.text;
            }
            
            var $result = $(
                '<div class="d-flex align-items-center">' +
                    '<div class="avatar avatar-xs avatar-label-success me-2">' +
                        '<i class="fas fa-user-md text-success"></i>' +
                    '</div>' +
                    '<div>' +
                        '<div class="fw-semibold">' + staff.staff.display_name + '</div>' +
                        '<small class="text-muted">' + staff.staff.specialty + ' • ' + staff.staff.email + '</small>' +
                    '</div>' +
                '</div>'
            );
            
            return $result;
        },
        templateSelection: function(staff) {
            if (staff.id === '') {
                return staff.text;
            }
            
            return staff.staff ? 
                staff.staff.display_name + ' - ' + staff.staff.specialty :
                staff.text;
        }
    };

    const finalOptions = Object.assign({}, defaultOptions, options);
    $(selector).select2(finalOptions);
}

/**
 * Auto-calcular hora de fin basada en la hora de inicio
 */
function initializeTimeCalculation(startTimeSelector, endTimeSelector, durationSelector, defaultDuration = 60) {
    const startTimeElement = document.querySelector(startTimeSelector);
    const endTimeElement = document.querySelector(endTimeSelector);
    const durationElement = document.querySelector(durationSelector);

    if (startTimeElement && endTimeElement) {
        startTimeElement.addEventListener('change', function() {
            const startTime = this.value;
            if (startTime) {
                const [hours, minutes] = startTime.split(':');
                const endTime = new Date();
                endTime.setHours(parseInt(hours) + Math.floor(defaultDuration / 60), 
                               parseInt(minutes) + (defaultDuration % 60));
                
                const endTimeString = endTime.getHours().toString().padStart(2, '0') + ':' + 
                                     endTime.getMinutes().toString().padStart(2, '0');
                
                endTimeElement.value = endTimeString;
                
                if (durationElement) {
                    durationElement.value = defaultDuration;
                }
            }
        });
    }
}

/**
 * Validación de fechas
 */
function initializeDateValidation(dateSelector) {
    const dateElement = document.querySelector(dateSelector);
    
    if (dateElement) {
        dateElement.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                showToast('No se pueden programar citas en fechas pasadas.', 'error');
                this.value = today.toISOString().split('T')[0];
            }
        });
    }
}

/**
 * Validación de horarios
 */
function initializeTimeValidation(startTimeSelector, endTimeSelector) {
    const startTimeElement = document.querySelector(startTimeSelector);
    const endTimeElement = document.querySelector(endTimeSelector);
    
    if (startTimeElement && endTimeElement) {
        endTimeElement.addEventListener('change', function() {
            const startTime = startTimeElement.value;
            const endTime = this.value;
            
            if (startTime && endTime && endTime <= startTime) {
                showToast('La hora de fin debe ser posterior a la hora de inicio.', 'error');
                this.value = '';
            }
        });
    }
}

/**
 * Mostrar toast de notificación
 */
function showToast(message, type = 'success', duration = null) {
    const toastDuration = duration || AppointmentsConfig.toast.duration;
    
    // Crear el toast dinámicamente
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const toastHTML = `
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="toast-header text-white border-0 bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'}">
                <div class="avatar avatar-xs avatar-label-light me-2">
                    <i class="fas fa-${getToastIcon(type)} fs-12"></i>
                </div>
                <strong class="me-auto">${getToastTitle(type)}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-light">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xs avatar-label-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'} me-2">
                        <i class="fas fa-calendar fs-12"></i>
                    </div>
                    <span class="text-muted">${message}</span>
                </div>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Inicializar el toast de Bootstrap
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: false,
        delay: 0
    });
    toast.show();
    
    // Auto-ocultar después del tiempo especificado
    setTimeout(() => {
        if (toastElement) {
            toastElement.classList.add('fade-out');
            setTimeout(() => toastElement.remove(), AppointmentsConfig.toast.animationDuration);
        }
    }, toastDuration);
}

/**
 * Obtener icono para toast según tipo
 */
function getToastIcon(type) {
    const icons = {
        'success': 'check',
        'error': 'exclamation-triangle',
        'warning': 'exclamation-circle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Obtener título para toast según tipo
 */
function getToastTitle(type) {
    const titles = {
        'success': '¡Éxito!',
        'error': 'Error',
        'warning': 'Advertencia',
        'info': 'Información'
    };
    return titles[type] || 'Información';
}

/**
 * Crear contenedor de toasts si no existe
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

/**
 * Actualizar estado de cita via AJAX
 */
function updateAppointmentStatus(appointmentId, statusId, statusName, statusColor, statusIcon, statusText) {
    // Obtener el dropdown para cerrarlo después
    const dropdown = event.target.closest('.dropdown');
    const dropdownButton = dropdown.querySelector('[data-bs-toggle="dropdown"]');
    
    // Mostrar indicador de carga
    const badge = dropdown.querySelector('.badge');
    const originalContent = badge.innerHTML;
    const originalClass = badge.className;
    
    badge.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...';
    badge.className = 'badge bg-secondary-subtle text-secondary';
    
    // Cerrar el dropdown inmediatamente
    const bootstrapDropdown = bootstrap.Dropdown.getInstance(dropdownButton);
    if (bootstrapDropdown) {
        bootstrapDropdown.hide();
    }
    
    fetch(`/appointments/${appointmentId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status_id: statusId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Actualizar el badge con el nuevo estado
            badge.innerHTML = `<i class="fas ${statusIcon} me-1"></i>${statusText}`;
            badge.className = `badge bg-${statusColor}-subtle text-${statusColor}`;
            
            // Mostrar mensaje de éxito
            showToast('Estado actualizado correctamente', 'success');
        } else {
            throw new Error(data.message || 'Error al actualizar el estado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Restaurar contenido original
        badge.innerHTML = originalContent;
        badge.className = originalClass;
        
        // Mostrar mensaje de error
        showToast('Error al actualizar el estado', 'error');
    });
}

/**
 * Cambiar registros por página
 */
function changePerPage(value) {
    console.log('changePerPage llamada con valor:', value);
    
    const currentParams = new URLSearchParams(window.location.search);
    console.log('Parámetros actuales:', currentParams.toString());
    
    currentParams.set('per_page', value);
    currentParams.delete('page'); // Resetear a la primera página
    
    const newUrl = window.location.pathname + '?' + currentParams.toString();
    console.log('Nueva URL:', newUrl);
    
    window.location.href = newUrl;
}

/**
 * Confirmar eliminación de cita
 */
function confirmEliminar(appointmentId) {
    const form = document.getElementById('appointmentDeleteForm');
    form.action = `/appointments/${appointmentId}`;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    deleteModal.show();
}

/**
 * Inicializar filtros collapse
 */
function initializeFiltersCollapse(moduleName) {
    const filterButton = document.querySelector(`[data-bs-target="#${moduleName}FiltersCollapse"]`);
    const filterText = filterButton.querySelector('.filter-text');
    
    if (filterButton && filterText) {
        filterButton.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            filterText.textContent = isExpanded ? 'Mostrar Filtros' : 'Ocultar Filtros';
        });
    }
}

/**
 * Inicializar tooltips de Bootstrap
 */
function initializeTooltips() {
    if (AppointmentsConfig.calendar.tooltips) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Inicializar navegación por teclado para calendarios
 */
function initializeKeyboardNavigation(navigationConfig) {
    if (AppointmentsConfig.calendar.keyboardNavigation) {
        document.addEventListener('keydown', function(e) {
            if (e.altKey && navigationConfig) {
                switch(e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        if (navigationConfig.previousUrl) {
                            window.location.href = navigationConfig.previousUrl;
                        }
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        if (navigationConfig.nextUrl) {
                            window.location.href = navigationConfig.nextUrl;
                        }
                        break;
                    case 't':
                        e.preventDefault();
                        if (navigationConfig.todayUrl) {
                            window.location.href = navigationConfig.todayUrl;
                        }
                        break;
                }
            }
        });
    }
}

/**
 * Inicializar dropdowns para evitar superposición
 */
function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.table .dropdown');
    dropdowns.forEach(dropdown => {
        const button = dropdown.querySelector('[data-bs-toggle="dropdown"]');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (button && menu) {
            // Asegurar que el dropdown se cierre al hacer clic fuera
            button.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Cerrar otros dropdowns cuando se abre uno nuevo
            button.addEventListener('show.bs.dropdown', function() {
                // Cerrar todos los otros dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        const otherButton = otherDropdown.querySelector('[data-bs-toggle="dropdown"]');
                        if (otherButton) {
                            const bsDropdown = bootstrap.Dropdown.getInstance(otherButton);
                            if (bsDropdown) {
                                bsDropdown.hide();
                            }
                        }
                    }
                });
            });
            
            // Cerrar dropdown solo al hacer clic fuera o en otro estado
            document.addEventListener('click', function(e) {
                // Si el clic es en otro dropdown de estado, cerrar el actual
                if (e.target.closest('.table .dropdown') && !dropdown.contains(e.target)) {
                    const bsDropdown = bootstrap.Dropdown.getInstance(button);
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }
                // Si el clic es completamente fuera de cualquier dropdown, cerrar todos
                else if (!e.target.closest('.table .dropdown')) {
                    const bsDropdown = bootstrap.Dropdown.getInstance(button);
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }
            });
        }
    });
}

/**
 * Inicializar toast de éxito existente
 */
function initializeSuccessToast(toastId) {
    const successToast = document.getElementById(toastId);
    if (successToast) {
        const toast = new bootstrap.Toast(successToast, {
            autohide: false, // Disable Bootstrap's autohide
            delay: 0
        });
        toast.show();
        
        setTimeout(function() {
            successToast.classList.add('fade-out'); // Agregar clase personalizada fade-out
            setTimeout(function() {
                successToast.remove(); // Remover elemento después de la animación
            }, AppointmentsConfig.toast.animationDuration); // Duración de la animación fadeOut
        }, AppointmentsConfig.toast.duration); // 3 segundos antes de iniciar fade-out
    }
}

/**
 * Inicialización completa del módulo
 */
function initializeAppointmentsModule(config = {}) {
    // Aplicar configuración personalizada
    Object.assign(AppointmentsConfig, config);
    
    // Inicializar componentes comunes
    initializeTooltips();
    initializeDropdowns();
    
    // Inicializar toast de éxito si existe
    const successToastId = document.querySelector('[id*="SuccessToast"]')?.id;
    if (successToastId) {
        initializeSuccessToast(successToastId);
    }
    
    console.log('Módulo de appointments inicializado correctamente');
}

// Exportar funciones para uso global
window.AppointmentsModule = {
    initialize: initializeAppointmentsModule,
    showToast: showToast,
    updateAppointmentStatus: updateAppointmentStatus,
    changePerPage: changePerPage,
    confirmEliminar: confirmEliminar,
    initializePatientSelect2: initializePatientSelect2,
    initializeStaffSelect2: initializeStaffSelect2,
    initializeTimeCalculation: initializeTimeCalculation,
    initializeDateValidation: initializeDateValidation,
    initializeTimeValidation: initializeTimeValidation,
    initializeKeyboardNavigation: initializeKeyboardNavigation,
    initializeFiltersCollapse: initializeFiltersCollapse,
    config: AppointmentsConfig
};

