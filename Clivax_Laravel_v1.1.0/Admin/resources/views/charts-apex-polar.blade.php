@extends('layouts.master')

@section('title')
    Polararea Charts
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
                    <h4 class="card-title mb-0">Basic Polararea Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="basic_polar_area"
                        data-colors='["--bs-primary", "--bs-success", "--bs-warning","--bs-danger", "--bs-info", "--bs-success", "--bs-primary", "--bs-dark", "--bs-secondary"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">PolarArea Monochrome</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="monochrome_polar_area" class="apex-charts" dir="ltr"></div>
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
    <!-- polarareacharts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-polararea.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
