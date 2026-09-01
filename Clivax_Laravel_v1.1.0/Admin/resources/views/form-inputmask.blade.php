@extends('layouts.master')

@section('title')
    Inputmask
@endsection

@section('topbar-title')
    Forms
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Input mask</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <strong>JQuery Inputmask</strong> is a jQuery plugin which create an input mask. An input mask helps
                        the user with the input by ensuring a predefined format. This can be useful for dates,
                        numerics, phone numbers, etc. Look <a href="http://robinherbots.github.io/Inputmask"
                            target="_blank">plugin's page</a> for more references.
                    </p>
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Date format</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" id="inputmask-1" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Phone number</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" id="inputmask-2" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Expty format</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" id="inputmask-3" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Repeating mask</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" id="inputmask-4" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Currency</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" id="inputmask-5" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">IP address</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" id="inputmask-6" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Email address</label>
                            </div>
                            <div class="col-sm-5 col-lg-6">
                                <input type="text" class="form-control" id="inputmask-7" />
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
    <!-- form mask -->
    <script src="{{ URL::asset('build/libs/inputmask/dist/jquery.inputmask.min.js') }}"></script>

    <!-- form mask init -->
    <script src="{{ URL::asset('build/js/pages/form-mask.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
