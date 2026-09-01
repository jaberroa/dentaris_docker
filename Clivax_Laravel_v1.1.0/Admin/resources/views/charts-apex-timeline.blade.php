@extends('layouts.master')

@section('title')
    Timeline Charts
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
                    <h4 class="card-title mb-0">Basic TimeLine Charts</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="basic_timeline" data-colors='["--bs-primary"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Different Color For Each Bar</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="color_timeline"
                        data-colors='["--bs-primary", "--bs-danger", "--bs-success", "--bs-warning", "--bs-info"]'
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
                    <h4 class="card-title mb-0">Multi Series Timeline</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="multi_series" data-colors='["--bs-primary","--bs-success"]' class="apex-charts" dir="ltr">
                    </div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Advanced Timeline (Multiple Range)</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="advanced_timeline" data-colors='["--bs-primary", "--bs-success", "--bs-warning"]'
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
                    <h4 class="card-title mb-0">Multiple series � Group rows</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="multi_series_group"
                        data-colors='["--bs-primary", "--bs-success", "--bs-warning", "--bs-danger", "--bs-info","--bs-gray","--bs-pink","--bs-purple","--bs-secondary", "--bs-dark"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Dumbbell Timeline chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="dumbbell_timeline"
                        data-colors='["--bs-primary", "--bs-success", "--bs-warning", "--bs-danger", "--bs-info","--bs-gray","--bs-pink","--bs-purple","--bs-secondary", "--bs-dark"]'
                        class="apex-charts" dir="ltr"></div>
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
    <script src="{{ URL::asset('build/libs/moment/moment.js') }}"></script>
    <!-- timeline charts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-timeline.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
