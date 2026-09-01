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
                    <h4 class="card-title mb-0">Basic Line Chart</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_basic" data-colors='["--bs-primary"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Realtimes Charts</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_realtime" data-colors='["--bs-info"]' class="apex-charts" dir="ltr"></div>
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
                    <h4 class="card-title mb-0">Zoomable Timeseries</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_zoomable" data-colors='["--bs-danger"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Line with Annotations</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_annotations" data-colors='["--bs-primary"]' class="apex-charts" dir="ltr">
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
                    <h4 class="card-title mb-0">Brush Charts</h4>
                </div>
                <div class="card-body">
                    <div>
                        <div id="brushchart_line2" data-colors='["--bs-success"]' class="apex-charts" dir="ltr"></div>
                        <div id="brushchart_line" data-colors='["--bs-info"]' class="apex-charts" dir="ltr"></div>
                    </div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Stepline Charts</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_stepline" data-colors='["--bs-warning"]' class="apex-charts" dir="ltr"></div>
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
                    <h4 class="card-title mb-0">Gradient Charts</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_gradient" data-colors='["--bs-primary"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Missing Data/ Null Value Charts</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_missing_data" data-colors='["--bs-primary", "--bs-danger", "--bs-warning"]'
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
                    <h4 class="card-title mb-0">Line with Data Labels</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_datalabel" data-colors='["--bs-primary", "--bs-info"]' class="apex-charts"
                        dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Dashed Line</h4>
                </div>
                <div class="card-body">
                    <div id="line_chart_dashed" data-colors='["--bs-primary", "--bs-danger", "--bs-success"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Syncing Charts</h4>
                </div>
                <div class="card-body">
                    <div>
                        <div id="chart-syncing-line" data-colors='["--bs-primary"]' class="apex-charts" dir="ltr">
                        </div>
                        <div id="chart-syncing-line2" data-colors='["--bs-warning"]' class="apex-charts" dir="ltr">
                        </div>
                        <div id="chart-syncing-area" data-colors='["--bs-secondary"]' class="apex-charts"
                            dir="ltr"></div>
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
    <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>
    <!-- linecharts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-line.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
