@extends('layouts.master')

@section('title')
    Datetime Picker
@endsection

@section('topbar-title')
    Forms
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/bootstrap-datetime-picker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Date time picker</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Bootstrap form component to handle date and time data. For more info please visit the plugin's <a
                            href="http://www.malot.fr/bootstrap-datetimepicker/demo.php" target="_blank">demo page</a> or
                        <a href="http://github.com/smalot/bootstrap-datetimepicker" target="_blank">Github repository</a>.
                    </p>
                    <hr />
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Basic demo</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" placeholder="Select date"
                                    id="datetimepicker-1" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Helper buttons</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" placeholder="Select date"
                                    id="datetimepicker-2" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Meridian format</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" placeholder="Select date"
                                    id="datetimepicker-3" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Week disabled</label>
                            </div>
                            <div class="col-sm-5 col-lg-4">
                                <div class="mb-2"><input type="text" class="form-control" placeholder="Select date"
                                        id="datetimepicker-4" /></div>
                                <p class="mb-0">Disable selected week columns</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Date only</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" placeholder="Select date"
                                    id="datetimepicker-5" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Time only</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" placeholder="Select date"
                                    id="datetimepicker-6" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Locale</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" placeholder="Select date"
                                    id="datetimepicker-7" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Inline</label>
                            </div>
                            <div class="col-sm-5 col-lg-4">
                                <div class="d-grid gap-3">
                                    <div id="datetimepicker-8"></div>
                                    <div id="datetimepicker-9"></div>
                                </div>
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
    <script src="{{ URL::asset('build/libs/bootstrap-datetime-picker/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-datetimepicker.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
