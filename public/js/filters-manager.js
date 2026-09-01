// Gestor de filtros collapse para todas las vistas
function initializeFiltersCollapse(viewName) {
    document.addEventListener('DOMContentLoaded', function() {
        const collapseElement = document.getElementById('filtersCollapse');
        const filterButton = document.querySelector('[data-bs-target="#filtersCollapse"]');
        
        if (!collapseElement || !filterButton) return;
        
        const filterText = filterButton.querySelector('.filter-text');
        const storageKey = `filtersOpen-${viewName}`;
        
        // Solo verificar localStorage - si está null, usar filtros activos como fallback
        let shouldBeOpen = localStorage.getItem(storageKey);
        if (shouldBeOpen === null) {
            // Primera vez - abrir si hay filtros activos
            const hasActiveFilters = new URLSearchParams(window.location.search).toString().length > 0;
            shouldBeOpen = hasActiveFilters;
        } else {
            shouldBeOpen = shouldBeOpen === 'true';
        }
        
        if (shouldBeOpen) {
            collapseElement.classList.add('show');
            if (filterText) filterText.textContent = 'Ocultar Filtros';
        }
        
        // Event listeners - solo responden a interacción manual del usuario
        collapseElement.addEventListener('show.bs.collapse', function () {
            if (filterText) filterText.textContent = 'Ocultar Filtros';
            localStorage.setItem(storageKey, 'true');
        });
        
        collapseElement.addEventListener('hide.bs.collapse', function () {
            if (filterText) filterText.textContent = 'Mostrar Filtros';
            localStorage.setItem(storageKey, 'false');
        });
    });
}
