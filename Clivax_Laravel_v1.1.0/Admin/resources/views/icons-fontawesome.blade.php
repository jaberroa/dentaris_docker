@extends('layouts.master')

@section('title')
    Font awesome
@endsection

@section('topbar-title')
    Icons
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Font awesome</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Get icons on your website with <strong>Font Awesome</strong>, the web's most
                        popular icon set and toolkit. For more info visit <a href="http://fontawesome.com"
                            target="_blank">Font Awesome's page</a>.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Solid</h3>
                </div>
                <div class="card-body">
                    <p class="card-title-desc mb-2">Use <code>&lt;i class="fas fa-ad"&gt;&lt;/i&gt;</code> <span
                            class="badge bg-success">v 5.13.0</span>.</p>
                    <div class="row icon-demo-content" id="solid">
                    </div>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Regular</h3>
                </div>
                <div class="card-body">
                    <p class="card-title-desc mb-2">Use <code>&lt;i class="far fa-address-book"&gt;&lt;/i&gt;</code> <span
                            class="badge bg-success">v 5.13.0</span>.</p>
                    <div class="row icon-demo-content" id="regular">
                    </div>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Brands</h3>
                </div>
                <div class="card-body">
                    <p class="card-title-desc mb-2">Use <code>&lt;i class="fab fa-500px"&gt;&lt;/i&gt;</code> <span
                            class="badge bg-success">v 5.13.0</span>.</p>
                    <div class="row icon-demo-content" id="brand">
                    </div>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection

@section('scripts')
    <!-- fontawesome icons init -->
    <script src="{{ URL::asset('build/js/pages/fontawesome.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
