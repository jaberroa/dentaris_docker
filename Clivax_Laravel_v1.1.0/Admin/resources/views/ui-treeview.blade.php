@extends('layouts.master')

@section('title')
    Treeview
@endsection

@section('topbar-title')
    Elements
@endsection

@section('css')
    <!-- jstree -->
    <link rel="stylesheet" href="{{ URL::asset('build/libs/jstree/dist/themes/default/style.min.css') }}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0"><strong>JSTree</strong> is jQuery plugin, that provides interactive trees. JSTree is
                        easily extendable and configurable, it supports HTML &amp; JSON data sources and AJAX loading. Check
                        more API references from <a href="http://www.jstree.com" target="_blank">JSTree's homepage</a>.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">HTML data</h3>
                </div>
                <div class="card-body">
                    <div id="jstree-1">
                        <ul>
                            <li>
                                Root node 1
                                <ul>
                                    <li data-jstree='{ "selected" : true }'><a href="javascript:;">Initially selected</a>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-briefcase" }'>Custom icon</li>
                                    <li data-jstree='{ "opened" : true }'>
                                        Initially open
                                        <ul>
                                            <li data-jstree='{ "disabled" : true }'>Disabled Node</li>
                                            <li data-jstree='{ "type" : "file" }'>Another node</li>
                                        </ul>
                                    </li>
                                    <li>
                                        Sub nodes
                                        <ul>
                                            <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                            <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                            <li>
                                                Sub nodes
                                                <ul>
                                                    <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                                    <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                                </ul>
                                            </li>
                                            <li data-jstree='{ "type" : "file" }'>Item 3</li>
                                        </ul>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-star text-success" }'>Custom color icon</li>
                                </ul>
                            </li>
                            <li data-jstree='{ "icon" : "fa fa-link text-info" }'><a href="http://google.com"
                                    target="_blank">Clickable link node</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Drag and drop</h3>
                </div>
                <div class="card-body">
                    <div id="jstree-4">
                        <ul>
                            <li>
                                Root node 1
                                <ul>
                                    <li data-jstree='{ "selected" : true }'><a href="javascript:;">Initially selected</a>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-briefcase" }'>Custom icon</li>
                                    <li data-jstree='{ "opened" : true }'>
                                        Initially open
                                        <ul>
                                            <li data-jstree='{ "disabled" : true }'>Disabled Node</li>
                                            <li data-jstree='{ "type" : "file" }'>Another node</li>
                                        </ul>
                                    </li>
                                    <li>
                                        Sub nodes
                                        <ul>
                                            <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                            <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                            <li>
                                                Sub nodes
                                                <ul>
                                                    <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                                    <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                                </ul>
                                            </li>
                                            <li data-jstree='{ "type" : "file" }'>Item 3</li>
                                        </ul>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-star text-success" }'>Custom color icon</li>
                                </ul>
                            </li>
                            <li data-jstree='{ "icon" : "fa fa-link text-info" }'><a href="http://google.com"
                                    target="_blank">Clickable link node</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Javascript data</h3>
                </div>
                <div class="card-body">
                    <div id="jstree-2"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Context Menu</h3>
                </div>
                <div class="card-body">
                    <div id="jstree-5">
                        <ul>
                            <li>
                                Root node 1
                                <ul>
                                    <li data-jstree='{ "selected" : true }'><a href="javascript:;">Initially selected</a>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-briefcase" }'>Custom icon</li>
                                    <li data-jstree='{ "opened" : true }'>
                                        Initially open
                                        <ul>
                                            <li data-jstree='{ "disabled" : true }'>Disabled Node</li>
                                            <li data-jstree='{ "type" : "file" }'>Another node</li>
                                        </ul>
                                    </li>
                                    <li>
                                        Sub nodes
                                        <ul>
                                            <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                            <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                            <li>
                                                Sub nodes
                                                <ul>
                                                    <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                                    <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                                </ul>
                                            </li>
                                            <li data-jstree='{ "type" : "file" }'>Item 3</li>
                                        </ul>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-star text-success" }'>Custom color icon</li>
                                </ul>
                            </li>
                            <li data-jstree='{ "icon" : "fa fa-link text-info" }'><a href="http://google.com"
                                    target="_blank">Clickable link node</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Checkbox</h3>
                </div>
                <div class="card-body">
                    <div id="jstree-3">
                        <ul>
                            <li>
                                Root node 1
                                <ul>
                                    <li data-jstree='{ "selected" : true }'><a href="javascript:;">Initially selected</a>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-briefcase" }'>Custom icon</li>
                                    <li data-jstree='{ "opened" : true }'>
                                        Initially open
                                        <ul>
                                            <li data-jstree='{ "disabled" : true }'>Disabled Node</li>
                                            <li data-jstree='{ "type" : "file" }'>Another node</li>
                                        </ul>
                                    </li>
                                    <li>
                                        Sub nodes
                                        <ul>
                                            <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                            <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                            <li>
                                                Sub nodes
                                                <ul>
                                                    <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                                    <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                                </ul>
                                            </li>
                                            <li data-jstree='{ "type" : "file" }'>Item 3</li>
                                        </ul>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-star text-success" }'>Custom color icon</li>
                                </ul>
                            </li>
                            <li data-jstree='{ "icon" : "fa fa-link text-info" }'><a href="http://google.com"
                                    target="_blank">Clickable link node</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Search</h3>
                </div>
                <div class="card-body"><input type="text" class="form-control mb-3" id="jstree-6-input"
                        placeholder="Search here...">
                    <div id="jstree-6">
                        <ul>
                            <li>
                                Root node 1
                                <ul>
                                    <li data-jstree='{ "selected" : true }'><a href="javascript:;">Initially selected</a>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-briefcase" }'>Custom icon</li>
                                    <li data-jstree='{ "opened" : true }'>
                                        Initially open
                                        <ul>
                                            <li data-jstree='{ "disabled" : true }'>Disabled Node</li>
                                            <li data-jstree='{ "type" : "file" }'>Another node</li>
                                        </ul>
                                    </li>
                                    <li>
                                        Sub nodes
                                        <ul>
                                            <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                            <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                            <li>
                                                Sub nodes
                                                <ul>
                                                    <li data-jstree='{ "type" : "file" }'>Item 1</li>
                                                    <li data-jstree='{ "type" : "file" }'>Item 2</li>
                                                </ul>
                                            </li>
                                            <li data-jstree='{ "type" : "file" }'>Item 3</li>
                                        </ul>
                                    </li>
                                    <li data-jstree='{ "icon" : "fa fa-star text-success" }'>Custom color icon</li>
                                </ul>
                            </li>
                            <li data-jstree='{ "icon" : "fa fa-link text-info" }'><a href="http://google.com"
                                    target="_blank">Clickable link node</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <!-- jstree js -->
    <script src="{{ URL::asset('build/libs/jstree/dist/jstree.min.js') }}"></script>

    <!-- treeview -->
    <script src="{{ URL::asset('build/js/pages/treeview.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
