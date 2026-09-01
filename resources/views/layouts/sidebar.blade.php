<!-- ========== Left Sidebar Start ========== -->
<div class="sidebar-left">

    <div data-simplebar class="h-100">

        <!--- Sidebar-menu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="left-menu list-unstyled" id="side-menu">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-desktop"></i>
                        <span>Panel de Control</span>
                    </a>
                </li>

                <li class="menu-title">Gestión</li>

                <li>
                    <a href="{{ route('patients.index') }}" class="{{ request()->routeIs('patients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Pacientes</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Citas</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.index') ? 'active' : '' }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Lista</a></li>
                        <li><a href="{{ route('appointments.weekly') }}" class="{{ request()->routeIs('appointments.weekly') ? 'active' : '' }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Vista Semanal</a></li>
                        <li><a href="{{ route('appointments.monthly') }}" class="{{ request()->routeIs('appointments.monthly') ? 'active' : '' }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Vista Mensual</a></li>
                        <li><a href="{{ route('appointments.yearly') }}" class="{{ request()->routeIs('appointments.yearly') ? 'active' : '' }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Vista Anual</a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">
                        <i class="fas fa-user-md"></i>
                        <span>Personal</span>
                    </a>
                </li>

                <li class="menu-title">Clínica</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('treatment-plans.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Tratamientos</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('treatment-plans.index') }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Planes de Tratamiento</a></li>
                        <li><a href="{{ route('treatments.index') }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Servicios</a></li>
                        <li><a href="{{ route('dental-plans.index') }}" class="{{ request()->routeIs('dental-plans.*') ? 'active' : '' }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Planes Odontológicos</a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('lab-works.index') }}" class="{{ request()->routeIs('lab-works.*') ? 'active' : '' }}">
                        <i class="fas fa-flask"></i>
                        <span>Laboratorio</span>
                    </a>
                </li>

                <li class="menu-title">Finanzas</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('billing.*') || request()->routeIs('quotes.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Facturación</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('billing.index') }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Facturas</a></li>
                        <li><a href="{{ route('quotes.index') }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Cotizaciones</a></li>
                        <li><a href="{{ route('payments.index') }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Pagos</a></li>
                    </ul>
                </li>

                <li class="menu-title">Inventario</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('inventory.*') || request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-boxes"></i>
                        <span>Inventario</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('inventory.index') }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Productos</a></li>
                        <li><a href="{{ route('suppliers.index') }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Proveedores</a></li>
                        <li><a href="{{ route('purchases.index') }}"><i class="mdi mdi-checkbox-blank-circle align-middle"></i>Compras</a></li>
                    </ul>
                </li>

                <li class="menu-title">Reportes</li>

                <li>
                    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <i class="fas fa-bell"></i>
                        <span>Notificaciones</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
