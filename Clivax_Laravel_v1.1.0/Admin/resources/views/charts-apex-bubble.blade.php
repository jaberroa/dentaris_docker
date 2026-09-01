@extends('layouts.master')

@section('title')
    Bubble Charts
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
                    <h4 class="card-title mb-0">Basic Bubble Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="simple_bubble" data-colors='["--bs-primary", "--bs-info", "--bs-warning", "--bs-success"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">3D Bubble Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="bubble_chart" data-colors='["--bs-primary", "--bs-success", "--bs-warning", "--bs-danger"]'
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

    <!-- bubblecharts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-bubble.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
