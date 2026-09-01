@extends('layouts.master')

@section('title')
    Mixed Charts
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
                    <h4 class="card-title mb-0">Line & Column Charts</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="line_column_chart" data-colors='["--bs-primary", "--bs-warning"]' class="apex-charts"
                        dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Multiple Y-Axis Charts</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="multi_chart" data-colors='["--bs-primary", "--bs-info", "--bs-success"]' class="apex-charts"
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
                    <h4 class="card-title mb-0">Line & Area Charts</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="line_area_chart" data-colors='["--bs-primary", "--bs-light"]' class="apex-charts"
                        dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Line, Column & Area Charts</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="line_area_charts" data-colors='["--bs-primary", "--bs-danger", "--bs-success"]'
                        class="apex-charts" dir="ltr"></div>
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
                    <h4 class="card-title mb-0">Line Scatter Charts</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="line_scatter_chart" data-colors='["--bs-primary", "--bs-info"]' class="apex-charts"
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
    <!-- mixed charts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-mixed.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
