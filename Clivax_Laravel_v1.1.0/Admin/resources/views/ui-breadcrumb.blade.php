@extends('layouts.master')

@section('title')
    Breadcrumb
@endsection

@section('topbar-title')
    Elements
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Breadcrumb</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Use an ordered or unordered list with linked list items to create a minimally
                        styled breadcrumb. Use our utilities to add additional styles as desired.</p>
                    <div class="breadcrumb">
                        <a class="breadcrumb-item" href="#">
                            <span class="breadcrumb-icon">
                                <i class=" fas fa-home"></i>
                            </span>
                            <span class="breadcrumb-text">Home</span>
                        </a>
                        <a class="breadcrumb-item" href="#">
                            <span class="breadcrumb-icon">
                                <i class=" fas fa-bookmark"></i>
                            </span>
                            <span class="breadcrumb-text">Library</span>
                        </a>
                        <a class="breadcrumb-item active" href="#">
                            <span class="breadcrumb-text">Data</span>
                        </a>
                    </div>
                    <p class="text-muted">You can add <code>.breadcrumb-transparent</code> to remove background and remove
                        x-axis padding on the breadcrumb.</p>
                    <div class="breadcrumb breadcrumb-transparent">
                        <a class="breadcrumb-item" href="#">
                            <span class="breadcrumb-icon">
                                <i class=" fas fa-home"></i>
                            </span>
                            <span class="breadcrumb-text">Home</span>
                        </a>
                        <a class="breadcrumb-item" href="#">
                            <span class="breadcrumb-icon">
                                <i class=" fas fa-bookmark"></i>
                            </span>
                            <span class="breadcrumb-text">Library</span>
                        </a>
                        <a class="breadcrumb-item active" href="#">
                            <span class="breadcrumb-text">Data</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
