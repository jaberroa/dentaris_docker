@extends('layouts.master')

@section('title')
    Range Charts
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
                    <h4 class="card-title mb-0">Basic Range Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="basic_range" data-colors='["--bs-success"]' class="apex-charts" dir="ltr"></div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Combo Chart</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="combo_range" data-colors='["--bs-danger", "--bs-success", "--bs-primary"]' class="apex-charts"
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
    <!-- radarcharts init -->
    <script src="{{ URL::asset('build/js/pages/apexcharts-range.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
