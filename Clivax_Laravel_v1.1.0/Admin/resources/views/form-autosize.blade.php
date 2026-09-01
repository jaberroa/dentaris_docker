@extends('layouts.master')

@section('title')
    Autosize
@endsection

@section('topbar-title')
    Forms
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Basic</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <strong>Autosize</strong> will automatically resize your textarea to fit the content. You must
                        initialize autosize via javascript: <code>autosize($('.autosize'))</code>. Check more API on
                        <a href="http://www.jacklmoore.com/autosize" target="_blank">Autosize's page</a>.
                    </p>
                    <textarea class="form-control autosize" placeholder="Type Something..."></textarea>
                </div>
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Predefined value</h3>
                </div>
                <div class="card-body">
                    <textarea class="form-control autosize" placeholder="Type Something...">Triggers the height adjustment for an assigned textarea element. Autosize will automatically adjust the textarea height on keyboard and window resize events. There is no efficient way for Autosize to monitor for when another script has changed the textarea value or for changes in layout that impact the textarea element.</textarea>
                </div>
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <!-- bs custom file input plugin -->
    <script src="{{ URL::asset('build/libs/autosize/dist/autosize.min.js') }}"></script>

    <script>
        "use strict";
        $(function() {
            autosize($(".autosize"))
        });
    </script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
