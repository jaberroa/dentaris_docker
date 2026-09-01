@extends('layouts.master')

@section('title')
    Clipboard
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
                        <strong>ClipboardJS</strong> is a modern approach to copy text to clipboard. A pretty common use
                        case is to copy content from another element. You can do that by adding a
                        <code>data-clipboard-target</code> attribute in your trigger element.
                    </p>
                    <p class="text-muted">
                        Additionally, you can define a <code>data-clipboard-action</code> attribute to specify if you want
                        to either copy or cut content. For more references, you can look
                        <a href="http://clipboardjs.com" target="_blank">ClipboardJS's page</a>.
                    </p>
                    <div class="d-grid gap-3">
                        <div class="input-group">
                            <input class="form-control" id="clipboard-1"
                                value="Lorem ipsum dolor sit amet, consectetur adipisicing elit." />
                            <button class="btn btn-primary btn-icon" data-clipboard-action="copy"
                                data-clipboard-target="#clipboard-1"><i class="fa fa-copy"></i></button>
                        </div>
                        <div class="input-group">
                            <input class="form-control" id="clipboard-2"
                                value="Lorem ipsum dolor sit amet, consectetur adipisicing elit." />
                            <button class="btn btn-primary btn-icon" data-clipboard-action="cut"
                                data-clipboard-target="#clipboard-2"><i class="fa fa-cut"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Textarea</h3>
                </div>
                <div class="card-body">
                    <textarea id="clipboard-3" class="form-control mb-2">Lorem ipsum dolor sit amet, consectetur adipisicing elit sed.</textarea>
                    <button class="btn btn-primary" data-clipboard-action="copy"
                        data-clipboard-target="#clipboard-3">Copy</button>
                    <button class="btn btn-primary" data-clipboard-action="cut"
                        data-clipboard-target="#clipboard-3">Cut</button>
                </div>
            </div>
            <!-- end card -->
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Copy from attribute</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">You don't even need another element to copy its content from. You can just include
                        a <code>data-clipboard-text</code> attribute in your trigger element.</p>
                    <button class="btn btn-primary" data-clipboard-action="copy"
                        data-clipboard-text="Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eveniet, saepe.">Copy
                        to clipboard</button>
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
    <script src="{{ URL::asset('build/libs/clipboard/dist/clipboard.min.js') }}"></script>
    <script>
        "use strict";
        $(function() {
            new ClipboardJS(".btn")
        });
    </script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
