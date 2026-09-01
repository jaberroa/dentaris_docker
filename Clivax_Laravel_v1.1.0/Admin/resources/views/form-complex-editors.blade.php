@extends('layouts.master')

@section('title')
    Complex Editor
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
                    <p class="text-muted">This example shows you how to use TinyMCE Classic Editor.</p>

                    <form method="post">
                        <textarea id="basic-example">
                        <h1 class="" style="text-align: center; color: gray;">Complex Text Editor</h1>

                        <ol style="font-size: 14px;">
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
                        <p style="font-size: 14px;">
                            <strong>Lorem ipsum</strong> dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation
                            ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat
                            cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                        </p>
                    </textarea>
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
    <script>
        if ($("#basic-example").length > 0) {
            tinymce.init({
                selector: 'textarea#basic-example',
                height: 400,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
            });
        }
    </script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
