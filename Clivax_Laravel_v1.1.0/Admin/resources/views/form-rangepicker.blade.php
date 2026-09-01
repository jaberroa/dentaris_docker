@extends('layouts.master')

@section('title')
    Rangepicker
@endsection

@section('topbar-title')
    Forms
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Date range picker</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <strong>Date Range Picker</strong> is javascript component for choosing date ranges, dates and
                        times. For more info please visit the plugin's
                        <a href="http://www.daterangepicker.com" target="_blank">homepage</a>.
                    </p>
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Basic demo</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input class="form-control" type="text" id="daterangepicker-1"
                                    value="02/06/2020 - 02/20/2020" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Date and time picker</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input class="form-control" type="text" id="daterangepicker-2"
                                    value="02/06/2020 - 02/20/2020" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Single picker</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input class="form-control" type="text" id="daterangepicker-3" value="02/15/2020" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Predefined picker</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <div class="mb-2">
                                    <input class="form-control" type="text" id="daterangepicker-4"
                                        value="02/06/2020 - 02/20/2020" />
                                </div>
                                <p class="mb-0">Replace default calendar with predefined time list</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Disabled date</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input class="form-control" type="text" id="daterangepicker-5" autocomplete="off" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Complex form</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input class="form-control" type="text" id="daterangepicker-6"
                                    value="02/06/2020 - 02/20/2020" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Local</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input class="form-control" type="text" id="daterangepicker-7"
                                    value="02/06/2020 - 02/20/2020" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <!-- Bootstrap datepicker -->
    <script src="{{ URL::asset('build/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/daterangepicker/daterangepicker.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/form-rangepicker.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
