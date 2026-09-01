<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Dentaris - Sistema de Gestión Clínica Dental</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de Gestión Clínica Dental" name="description" />
    <meta content="Dentaris" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- include head css -->
    @include('layouts.head-css')
    
    <!-- Filters Manager JS -->
    <script src="{{ asset('js/filters-manager.js') }}"></script>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <!-- topbar -->
        @include('layouts.topbar')
        <!-- sidebar components -->
        @include('layouts.sidebar')

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    {{-- <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h3 class="mb-sm-0">@yield('title')</h3>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">@yield('topbar-title')</a>
                                        </li>
                                        <li class="breadcrumb-item active">@yield('title')</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div> --}}
                    <!-- end page title -->

                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <!-- footer -->
            @include('layouts.footer')

        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- customizer -->
    @include('layouts.right-sidebar')

    <!-- vendor-scripts -->
    @include('layouts.vendor-scripts')

</body>

</html>