@extends('layouts.master')

@section('title')
    Popover
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
                    <h3 class="card-title">Popover examples</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Basic popover</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2">
                                    <button class="btn btn-primary" data-bs-toggle="popover"
                                        data-bs-content="And here's some amazing content. It's very engaging. Right?"
                                        data-bs-original-title="Popover title">Click me</button>
                                </div>
                                <p class="mb-0">Add <code>data-bs-toggle="popover"</code> attribute to initialize popover
                                    and put the content in <code>title</code> and <code>data-bs-content</code> attributes
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Positions</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2">
                                    <button class="btn btn-primary" data-bs-toggle="popover" data-bs-placement="top"
                                        data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus.">Top</button>
                                    <button class="btn btn-primary" data-bs-toggle="popover" data-bs-placement="right"
                                        data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus.">Right</button>
                                    <button class="btn btn-primary" data-bs-toggle="popover" data-bs-placement="bottom"
                                        data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus.">Bottom</button>
                                    <button class="btn btn-primary" data-bs-toggle="popover" data-bs-placement="left"
                                        data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus.">Left</button>
                                </div>
                                <p class="mb-0">Change popover placement by setting <code>data-bs-placement</code>
                                    attribute with <code>top|bottom|right|left</code></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">Dismiss on next click</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2">
                                    <button class="btn btn-primary" data-bs-toggle="popover" data-bs-trigger="focus"
                                        data-bs-content="And here's some amazing content. It's very engaging. Right?">Dismissible
                                        popover</button>
                                </div>
                                <p class="mb-0">Use the <code>data-bs-trigger="focus"</code> attribute to dismiss popovers
                                    on the user’s next click of a different element than the toggle element</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-lg-2">
                                <label class="col-form-label">HTML content</label>
                            </div>
                            <div class="col-sm-8 col-lg-10">
                                <div class="mb-2">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="popover"
                                        data-bs-html="true"
                                        data-bs-content="And here's some amazing <b>HTML</b> content. It's very <code>engaging</code>. Right?"
                                        data-bs-original-title="Popover title">Click me</button>
                                </div>
                                <p class="mb-0">Enable popover html content by setting <code>data-bs-html="true"</code>
                                </p>
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
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
