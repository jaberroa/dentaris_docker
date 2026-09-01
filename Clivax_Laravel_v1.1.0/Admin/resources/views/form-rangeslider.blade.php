@extends('layouts.master')

@section('title')
    Slider
@endsection

@section('topbar-title')
    Forms
@endsection

@section('css')
    <!-- ION Slider -->
    <link href="{{ URL::asset('build/libs/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">ION Range slider</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Cool, comfortable, responsive and easily customizable range slider</p>
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Default</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_01">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Min-Max</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_02">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Prefix</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_03">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Range</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_04">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Step</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_05">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Custom Values</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_06">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Prettify Numbers</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_07">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Disabled</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_08">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Extra Example</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_09">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Use decorate_both option</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_10">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Postfixes</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_11">
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Hide</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" id="range_12">
                            </div>
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end grid -->
                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <!-- Ion Range Slider-->
    <script src="{{ URL::asset('build/libs/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>

    <!-- Range slider init js-->
    <script src="{{ URL::asset('build/js/pages/range-sliders.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
