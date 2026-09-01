@extends('layouts.master')

@section('title')
    Treemap Charts
@endsection

@section('topbar-title')
    Apexcharts
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Basic Treemap Charts</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="basic_treemap" data-colors='["--bs-success"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Multi-Dimensional Treemap Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="multi_treemap" data-colors='["--bs-primary","--bs-success"]' class="apex-charts"
                        dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Distributed Treemap Chart (Different Color for each Cell)</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="distributed_treemap"
                        data-colors='["--bs-primary", "--bs-secondary", "--bs-success", "--bs-info","--bs-warning", "--bs-danger"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Treemap Chart with Color Ranges</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="color_range_treemap" data-colors='["--bs-info","--bs-danger"]' class="apex-charts"
                        dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/dist/apexcharts.min.js') }}"></script>

    <!-- treecharts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-treemap.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
