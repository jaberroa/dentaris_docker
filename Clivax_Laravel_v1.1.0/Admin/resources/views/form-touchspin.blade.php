@extends('layouts.master')

@section('title')
    Touchspin
@endsection

@section('topbar-title')
    Forms
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Touchspin</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <strong>Bootstrap Touchspin</strong> is mobile and touch friendly input spinner component for
                        Bootstrap. It supports the mousewheel and the up/down keys. Visit
                        <a href="http://www.virtuosoft.eu/code/bootstrap-touchspin" target="_blank">plugin's page</a> for
                        examples.
                    </p>
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Basic</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <input class="form-control" id="touchspin-1" type="text" value="55" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Steps</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <input class="form-control" id="touchspin-2" type="text" value="55" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Decimal</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <input class="form-control" id="touchspin-3" type="text" value="55" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Postfix</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <input class="form-control" id="touchspin-4" type="text" value="55" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Prefix</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <input class="form-control" id="touchspin-5" type="text" value="20" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Vertical</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <input class="form-control" id="touchspin-6" type="text" value="55" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Custom button class</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <div class="d-grid gap-3">
                                    <input class="form-control" id="touchspin-7" type="text" value="55" /> <input
                                        class="form-control" id="touchspin-8" type="text" value="55" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Custom icons</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <div class="d-grid gap-3">
                                    <input class="form-control" id="touchspin-9" type="text" value="55" /> <input
                                        class="form-control" id="touchspin-10" type="text" value="55" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label text-sm-end">Sizing</label>
                            </div>
                            <div class="col-sm-3 col-lg-6">
                                <div class="d-grid gap-3">
                                    <input class="form-control input-sm touchspin-11" type="text" value="55" />
                                    <input class="form-control touchspin-11" type="text" value="55" />
                                    <input class="form-control input-lg touchspin-11" type="text" value="55" />
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
    <!-- touchspin js -->
    <script src="{{ URL::asset('build/libs/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/form-touchspin.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
