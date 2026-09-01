<!-- Start topbar -->
<header id="page-topbar">
    <div class="navbar-header">

        <!-- Logo -->

        <!-- Start Navbar-Brand -->
        <div class="navbar-logo-box">
            <a href="{{ route('dashboard') }}" class="logo logo-dark">
                <span class="logo-sm">
                    <i class="fas fa-tooth fs-20"></i>
                </span>
                <span class="logo-lg">
                    <i class="fas fa-tooth me-2"></i>
                    <span>Dentaris</span>
                </span>
            </a>

            <a href="{{ route('dashboard') }}" class="logo logo-light">
                <span class="logo-sm">
                    <i class="fas fa-tooth fs-20"></i>
                </span>
                <span class="logo-lg">
                    <i class="fas fa-tooth me-2"></i>
                    <span>Dentaris</span>
                </span>
            </a>

            <button type="button" class="btn btn-sm top-icon sidebar-btn" id="sidebar-btn">
                <i class="mdi mdi-menu-open align-middle fs-19"></i>
            </button>
        </div>
        <!-- End navbar brand -->

        <!-- Start menu -->
        <div class="d-flex justify-content-between menu-sm px-3 ms-auto">
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown d-none d-lg-block">
                    <button type="button" class="btn btn-primary btn-sm fs-14 d-inline" data-bs-toggle="dropdown">
                        Acciones
                        <i class="mdi mdi-chevron-down align-middle"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-start dropdown-menu-animated">
                        <a href="{{ route('patients.create') }}" class="dropdown-item">
                            <div class="dropdown-icon"><i class="fas fa-user-plus"></i></div>
                            <span class="dropdown-content">Nuevo Paciente</span>
                        </a>
                        <a href="{{ route('appointments.create') }}" class="dropdown-item">
                            <div class="dropdown-icon"><i class="fas fa-calendar-plus"></i></div>
                            <span class="dropdown-content">Nueva Cita</span>
                        </a>
                        <a href="{{ route('billing.create') }}" class="dropdown-item">
                            <div class="dropdown-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            <span class="dropdown-content">Nueva Factura</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('reports.index') }}" class="dropdown-item">
                            <div class="dropdown-icon"><i class="fas fa-chart-bar"></i></div>
                            <span class="dropdown-content">Reportes</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!--Start App Buscar-->
                <form class="app-search d-none d-lg-block">
                    <div class="position-relative">
                        <input type="text" class="form-control" placeholder="Buscar pacientes, citas...">
                        <span class="fab fa-sistrix fs-17 align-middle"></span>
                    </div>
                </form>
                <!--End App Buscar-->

                <!-- Start Notification -->
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn btn-sm top-icon" id="page-header-notifications-dropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell align-middle"></i>
                        <span class="btn-marker"><i class="marker marker-dot text-danger"></i><span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-md dropdown-menu-end p-0"
                        aria-labelledby="page-header-notifications-dropdown">
                        <div class="p-3 bg-info">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-white m-0"><i class="far fa-bell me-2"></i> Notificaciones </h6>
                                </div>
                                <div class="col-auto">
                                    <a href="#!" class="badge bg-info-subtle text-info"> 3+</a>
                                </div>
                            </div>
                        </div>
                        <div data-simplebar style="max-height: 230px;">
                            <a href="" class="text-reset notification-item">
                                <div class="d-flex">
                                    <div class="avatar avatar-xs avatar-label-primary me-3">
                                        <span class="rounded fs-16">
                                            <i class="fas fa-calendar-times"></i>
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h6 class="mb-1">Cita cancelada</h6>
                                        <div class="fs-12 text-muted">
                                            <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 3 min ago</p>
                                        </div>
                                    </div>
                                    <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                </div>
                            </a>
                            <a href="" class="text-reset notification-item">
                                <div class="d-flex">
                                    <div class="avatar avatar-xs avatar-label-success me-3">
                                        <span class="rounded fs-16">
                                            <i class="fas fa-dollar-sign"></i>
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h6 class="mb-1">Pago recibido</h6>
                                        <div class="fs-12 text-muted">
                                            <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 1 hour ago</p>
                                        </div>
                                    </div>
                                    <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                </div>
                            </a>
                            <a href="" class="text-reset notification-item">
                                <div class="d-flex">
                                    <div class="avatar avatar-xs avatar-label-danger me-3">
                                        <span class="rounded fs-16">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h6 class="mb-1">Productoo bajo stock</h6>
                                        <div class="fs-12 text-muted">
                                            <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 2 hours ago</p>
                                        </div>
                                    </div>
                                    <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                </div>
                            </a>
                        </div>
                        <div class="p-2 border-top">
                            <div class="d-grid">
                                <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                                    <i class="mdi mdi-arrow-right-circle me-1"></i> Ver todas...
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Notification -->

                <!-- Start Activities -->
                <div class="d-inline-block activities">
                    <button type="button" class="btn btn-sm top-icon" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvas-rightsidabar">
                        <i class="fas fa-table align-middle"></i>
                    </button>
                </div>
                <!-- End Activities -->

                <!-- Start Profile -->
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn btn-sm top-icon p-0" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="avatar avatar-2xs">
                            <span class="avatar-label-primary">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                    </button>
                    <div
                        class="dropdown-menu dropdown-menu-wide dropdown-menu-end dropdown-menu-animated overflow-hidden py-0">
                        <div class="card border-0">
                            <div class="card-header bg-primary rounded-0">
                                <div class="rich-list-item w-100 p-0">
                                    <div class="rich-list-prepend">
                                        <div class="avatar avatar-label-light avatar-circle">
                                            <div class="avatar-display">{{ substr(Auth::user()->name, 0, 1) }}</div>
                                        </div>
                                    </div>
                                    <div class="rich-list-content">
                                        <h3 class="rich-list-title text-white">{{ Auth::user()->name }}</h3>
                                        <span class="rich-list-subtitle text-white">{{ Auth::user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="grid-nav grid-nav-flush grid-nav-action grid-nav-no-rounded">
                                    <div class="grid-nav-row">
                                        <a href="{{ route('profile.edit') }}" class="grid-nav-item">
                                            <div class="grid-nav-icon"><i class="far fa-address-card"></i></div>
                                            <span class="grid-nav-content">Perfil</span>
                                        </a>
                                        <a href="#!" class="grid-nav-item">
                                            <div class="grid-nav-icon"><i class="far fa-cog"></i></div>
                                            <span class="grid-nav-content">Configuración</span>
                                        </a>
                                        <a href="#!" class="grid-nav-item">
                                            <div class="grid-nav-icon"><i class="far fa-bell"></i></div>
                                            <span class="grid-nav-content">Notificaciones</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer card-footer-bordered rounded-0">
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-label-danger w-100">Cerrar Sesión</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Profile -->
            </div>
        </div>
        <!-- End menu -->
    </div>
</header>
<!-- End topbar -->
