@extends('layouts.master')

@section('title')
    Bubble Editor
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
                <div class="card-header">
                    <h4 class="card-title">Tinymce</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">This example shows you the inline editing capabilities of TinyMCE.</p>

                    <form method="post">
                        <div class="demo-inline border rounded p-3 shadow-lg">
                            <div class="">
                                <h1 class="tinymce-heading text-muted" style="text-align: center;">Bubble Text Editor</h1>
                                <ol class="tinymce-body">
                                    <li>Cillum dolore eu fugiat nulla pariatur</li>
                                    <li>Duis aute irure dolor in</li>
                                    <li>Tempor incididunt ut labore</li>
                                    <li>
                                        <ol>
                                            <li>Lorem ipsum dolor sit amet</li>
                                            <li>Ommodo consequat</li>
                                            <li>Duis aute irure dolor in</li>
                                        </ol>
                                    </li>
                                    <li>Laboris nisi ut aliquip ex ea</li>
                                    <li>Excepteur sint occaecat</li>
                                </ol>
                                <p>
                                    <strong>Lorem ipsum</strong> dolor sit amet, consectetur adipisicing elit, sed do
                                    eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                    quis nostrud exercitation
                                    ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
                                    reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur
                                    sint occaecat
                                    cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection

@section('scripts')
    <!--tinymce js-->
    <script src="{{ URL::asset('build/libs/tinymce/tinymce.min.js') }}"></script>

    <!-- init js -->
    <script src="{{ URL::asset('build/js/pages/form-editor.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
