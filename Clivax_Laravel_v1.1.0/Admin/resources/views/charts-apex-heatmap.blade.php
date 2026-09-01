@extends('layouts.master')

@section('title')
    Heatmap Charts
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
                    <h4 class="card-title mb-0">Basic Heatmap Chart</h4>
                </div>

                <div class="card-body">
                    <div class="live-preview">
                        <div id="basic_heatmap" data-colors='["--bs-success", "--bs-card-bg-custom"]' class="apex-charts"
                            dir="ltr"></div>
                    </div>
                </div>
                <!-- end card-body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Heatmap - Multiple Series</h4>
                </div>

                <div class="card-body">
                    <div id="multiple_heatmap"
                        data-colors='["--bs-primary", "--bs-secondary", "--bs-success", "--bs-info", "--bs-warning", "--bs-danger", "--bs-dark", "--bs-primary", "--bs-card-bg-custom"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
                <!-- end card-body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Heatmap Color Range</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="color_heatmap" data-colors='["--bs-info", "--bs-success", "--bs-primary", "--bs-warning"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Heatmap - Range Without Shades</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="shades_heatmap" data-colors='["--bs-info", "--bs-primary"]' class="apex-charts" dir="ltr">
                    </div>
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
    <!-- heatmapscharts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-heatmap.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
