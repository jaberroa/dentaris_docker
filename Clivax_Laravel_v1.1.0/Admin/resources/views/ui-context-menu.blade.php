@extends('layouts.master')

@section('title')
    Context-menu
@endsection

@section('topbar-title')
    Elements
@endsection

@section('css')
    <!-- contextMenu css -->
    <link rel="stylesheet" href="{{ URL::asset('build/libs/jquery-contextmenu-js/dist/jquery.contextMenu.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Context menu</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted"><strong>JQuery Context Menu</strong> was designed for web applications in need of
                        menus on a possibly large amount of objects. Check more details by visiting <a
                            href="http://swisnl.github.io/jQuery-contextMenu" target="_blank">the documentation</a>.</p>
                    <hr>
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Basic setup</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <button class="btn btn-primary" id="context-menu-1">Right click me</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Custom icon</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2"><button class="btn btn-primary" id="context-menu-2">Right click
                                        me</button></div>
                                <p class="text-muted mb-0">You can customize the icon with fontawesome</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Accesskeys</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <button class="btn btn-primary" id="context-menu-3">Right click me</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Disabled command</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <button class="btn btn-primary" id="context-menu-4">Right click me</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Keeping context menu open</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <button class="btn btn-primary" id="context-menu-5">Right click me</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Submenu</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <button class="btn btn-primary" id="context-menu-6">Right click me</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Custom triggers</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2"><button class="btn btn-primary" id="context-menu-7">Click</button>
                                    <button class="btn btn-primary" id="context-menu-8">Hover</button>
                                </div>
                                <p class="text-muted mb-0">Reset the trigger by setting <code>trigger</code> property</p>
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
    <!-- contextMenu js-->
    <script src="{{ URL::asset('build/libs/jquery-contextmenu-js/dist/jquery.contextMenu.min.js') }}"></script>

    <!-- contextMenu init js-->
    <script src="{{ URL::asset('build/js/pages/contextMenu.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
