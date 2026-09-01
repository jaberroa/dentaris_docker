@extends('layouts.master')

@section('title')
    Bar Charts
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
                    <h4 class="card-title mb-0">Basic Bar Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="bar_chart" data-colors='["--bs-success"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Custom DataLabels Bar</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="custom_datalabels_bar"
                        data-colors='["--bs-primary", "--bs-secondary", "--bs-success", "--bs-info", "--bs-warning", "--bs-danger", "--bs-dark", "--bs-primary", "--bs-success", "--bs-secondary"]'
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
                    <h4 class="card-title mb-0">Stacked Bar Charts</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="stacked_bar"
                        data-colors='["--bs-primary", "--bs-success", "--bs-warning", "--bs-danger", "--bs-info"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Stacked Bars 100</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="stacked_bar_100"
                        data-colors='["--bs-primary", "--bs-success", "--bs-warning", "--bs-danger", "--bs-info"]'
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
                    <h4 class="card-title mb-0">Bar with Negative Values</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="negative_bars" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts"
                        dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Bar with Markers</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="bar_markers" data-colors='["--bs-success", "--bs-primary"]' class="apex-charts" dir="ltr">
                    </div>
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
                    <h4 class="card-title mb-0">Reversed Bar Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="reversed_bars" data-colors='["--bs-info"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Patterned Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="patterned_bars" data-colors='["--bs-primary", "--bs-success", "--bs-warning", "--bs-danger"]'
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
                    <h4 class="card-title mb-0">Grouped Bar Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="grouped_bar" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts" dir="ltr">
                    </div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Bar with Images</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div class="live-preview">
                        <div id="bar_images" class="apex-charts" dir="ltr"></div>
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

    <!-- barcharts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-bar.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
