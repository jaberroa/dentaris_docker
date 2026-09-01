@extends('layouts.master')

@section('title')
    Scatter Charts
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
                    <h4 class="card-title mb-0">Basic Scatter Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="basic_scatter" data-colors='["--bs-primary", "--bs-success", "--bs-warning"]' class="apex-charts"
                        dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Scatter - Datetime Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="datetime_scatter"
                        data-colors='["--bs-primary", "--bs-success", "--bs-warning", "--bs-warning", "--bs-info"]'
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
                    <h4 class="card-title mb-0">Scatter Images Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="images_scatter" data-colors='["--bs-primary", "--bs-danger"]' class="apex-charts"
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
    <!-- scatter charts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-scatter.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
