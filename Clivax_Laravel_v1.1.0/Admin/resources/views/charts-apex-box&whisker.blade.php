@extends('layouts.master')

@section('title')
    Boxplot Charts
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
                    <h4 class="card-title mb-0">Basic Box Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="basic_box" data-colors='["--bs-primary", "--bs-info"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Boxplot with Scatter Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="box_plot" data-colors='["--bs-danger", "--bs-info", "--bs-danger", "--bs-primary"]'
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
                    <h4 class="card-title mb-0">Horizontal BoxPlot</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="box_plot_hori" data-colors='["--bs-light", "--bs-white"]' class="apex-charts" dir="ltr">
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

    <!-- boxplotcharts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-boxplot.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
