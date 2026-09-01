@extends('layouts.master')

@section('title')
    Form Basic Editor
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
                    <p class="text-muted">This example contains the plugins needed for the most common use cases.</p>

                    <form method="post">
                        <textarea id="elm1" name="area">
                        <h1 class="" style="text-align: center; color: gray;">Basic Text Editor</h1>

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
        if ($("#elm1").length > 0) {
            tinymce.init({
                selector: "textarea#elm1",
                height: 400,
                plugins: [
                    "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
                style_formats: [{
                        title: 'Bold text',
                        inline: 'b'
                    },
                    {
                        title: 'Red text',
                        inline: 'span',
                        styles: {
                            color: '#ff0000'
                        }
                    },
                    {
                        title: 'Red header',
                        block: 'h1',
                        styles: {
                            color: '#ff0000'
                        }
                    },
                    {
                        title: 'Example 1',
                        inline: 'span',
                        classes: 'example1'
                    },
                    {
                        title: 'Example 2',
                        inline: 'span',
                        classes: 'example2'
                    },
                    {
                        title: 'Table styles'
                    },
                    {
                        title: 'Table row 1',
                        selector: 'tr',
                        classes: 'tablerow1'
                    }
                ]
            });
        }
    </script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
