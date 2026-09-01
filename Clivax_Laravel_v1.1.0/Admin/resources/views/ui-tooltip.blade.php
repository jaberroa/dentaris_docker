@extends('layouts.master')

@section('title')
    Tooltip
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
                    <h3 class="card-title">Tooltip examples</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Basic tooltip</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2">
                                    <button class="btn btn-primary" data-bs-toggle="tooltip"
                                        data-bs-original-title="Tooltip content">Hover me</button>
                                </div>
                                <p class="text-muted mb-0">Add <code>data-bs-toggle="tooltip"</code> attribute to initialize
                                    tooltip and put the content in <code>title</code> attribute</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Placements</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2">
                                    <button class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-original-title="Top tooltip">Top</button>
                                    <button class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        data-bs-original-title="Bottom tooltip">Bottom</button>
                                    <button class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="Right tooltip">Right</button>
                                    <button class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                                        data-bs-original-title="Left tooltip">Left</button>
                                </div>
                                <p class="text-muted mb-0">Set <code>data-bs-placement</code> attribute with
                                    <code>top|bottom|right|left</code> to change tooltip placement
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">HTML Content</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2">
                                    <button class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-html="true"
                                        data-bs-original-title="content <b>bold</b> and <em>italic</em>">Hover me</button>
                                </div>
                                <p class="text-muted mb-0">Enable html content on tooltip by setting
                                    <code>data-bs-html="true"</code></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Trigger on click</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2">
                                    <button class="btn btn-primary" data-bs-trigger="focus" data-bs-toggle="tooltip"
                                        data-bs-original-title="Tooltip content">Click me</button>
                                </div>
                                <p class="text-muted mb-0">Change tooltip trigger to focus</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
