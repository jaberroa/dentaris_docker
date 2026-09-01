@extends('layouts.master')

@section('title')
    Chat
@endsection

@section('topbar-title')
    Elements
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Basic</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted"><strong>Chat</strong> elements as the name suggests can be used for messaging. To
                        make chat bubbles use <code>.chat-bubble</code> class with <code>&lt;p&gt;</code> tag. Wrap your
                        chat bubbles into <code>.chat-content</code>. You must set chat items alignment by extend
                        <code>.chat-item</code> class with <code>.chat-item-{start|end}</code> modifier classes.</p>
                    <div class="chat">
                        <div class="chat-item chat-item-end">
                            <div class="chat-content">
                                <span class="chat-author">Me</span>
                                <p class="chat-bubble">consectetur adipisicing elit, sed do eiusmod tempor.</p>
                                <p class="chat-bubble">Lorem ipsum dolor sit amet.</p>
                                <span class="chat-time">1 hrs ago</span>
                            </div>
                        </div>
                        <div class="chat-item chat-item-start">
                            <div class="chat-content">
                                <span class="chat-author">Charlie</span>
                                <p class="chat-bubble">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
                                    eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis
                                    aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
                                    pariatur. Excepteur sint occaecat cupidatat non proident, sunt in.</p>
                                <span class="chat-time">2 hrs ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Avatar</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">You can put your <a href="ui-avatar.html">avatar</a> into the chat elements. Wrap
                        your avatar into <code>.chat-avatar</code> class.</p>
                    <div class="chat">
                        <div class="chat-item chat-item-end">
                            <div class="chat-avatar">
                                <div class="avatar avatar-xs avatar-circle">
                                    <!-- <div class="avatar avatar-circle"> -->
                                    <div class="avatar-display">
                                        <i class="fa fa-user"></i>
                                    </div>
                                    <!-- </div> -->
                                </div>
                            </div>
                            <div class="chat-content">
                                <span class="chat-author">Me</span>
                                <p class="chat-bubble">consectetur adipisicing elit, sed do eiusmod tempor.</p>
                                <p class="chat-bubble">Lorem ipsum dolor sit amet.</p>
                                <span class="chat-time">1 hrs ago</span>
                            </div>
                        </div>
                        <div class="chat-item chat-item-start">
                            <div class="chat-avatar">
                                <div class="avatar avatar-circle">
                                    <div class="avatar-display">
                                        <img src="{{ URL::asset('build/images/users/avatar-1.png') }}" class="avatar-xs avatar avatar-circle"
                                            alt="Avatar image">
                                    </div>
                                </div>
                            </div>
                            <div class="chat-content">
                                <span class="chat-author">Charlie</span>
                                <p class="chat-bubble">Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut
                                    labore et dolore magna aliqua.</p><span class="chat-time">2 hrs ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Section</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Chat section is an element that can be used for marking time section, look the
                        example.</p>
                    <div class="chat">
                        <div class="chat-item chat-item-end">
                            <div class="chat-content"><span class="chat-author">Me</span>
                                <p class="chat-bubble">consectetur adipisicing elit, sed do eiusmod tempor.</p>
                                <p class="chat-bubble">Lorem ipsum dolor sit amet.</p>
                                <span class="chat-time">1 hrs ago</span>
                            </div>
                        </div>
                        <div class="chat-section">
                            <span class="chat-section-text">11 September</span>
                        </div>
                        <div class="chat-item chat-item-start">
                            <div class="chat-content">
                                <span class="chat-author">Charlie</span>
                                <p class="chat-bubble">Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut
                                    labore et dolore magna aliqua.</p><span class="chat-time">2 hrs ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Colors</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Change the bubble color with <code>.chat-bubble-{color}</code> modifier classes to
                        differentiate each chat bubbles.</p>
                    <div class="chat">
                        <div class="chat-item chat-item-end">
                            <div class="chat-content">
                                <span class="chat-author">Me</span>
                                <p class="chat-bubble chat-bubble-primary">consectetur adipisicing elit, sed do eiusmod
                                    tempor.</p>
                                <p class="chat-bubble chat-bubble-primary">Lorem ipsum dolor sit amet.</p><span
                                    class="chat-time">1 hrs ago</span>
                            </div>
                        </div>
                        <div class="chat-item chat-item-start">
                            <div class="chat-content">
                                <span class="chat-author">Charlie</span>
                                <p class="chat-bubble chat-bubble-info">Lorem ipsum dolor sit amet, consectetur adipisicing
                                    elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad
                                    minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore
                                    eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in.</p>
                                <span class="chat-time">2 hrs ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">More content</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">You can put chat bubble into another bubble to make mentioned message. Insert
                        images or any elements to make more rich messages.</p>
                    <div class="chat">
                        <div class="chat-item chat-item-end">
                            <div class="chat-avatar">
                                <div class="avatar avatar-xs avatar-circle">
                                    <!-- <div class="avatar avatar-circle"> -->
                                    <div class="avatar-display">
                                        <i class="fa fa-user"></i>
                                    </div>
                                    <!-- </div> -->
                                </div>
                            </div>
                            <div class="chat-content">
                                <span class="chat-author">Me</span>
                                <div class="chat-bubble chat-bubble-primary">
                                    <img src="{{ URL::asset('build/images/small/img-1.jpg') }}" class="chat-image img-fluid"
                                        alt="Avatar image">
                                    <p class="mb-0">Lorem ipsum dolor sit amet.</p>
                                </div>
                                <p class="chat-bubble chat-bubble-primary">Duis aute irure dolor in</p>
                                <span class="chat-time">1 hrs ago</span>
                            </div>
                        </div>
                        <div class="chat-item chat-item-start">
                            <div class="chat-avatar">
                                <div class="avatar avatar-circle">
                                    <div class="avatar-display">
                                        <img src="{{ URL::asset('build/images/users/avatar-2.png') }}" class="avatar-xs avatar avatar-circle"
                                            alt="Avatar image">
                                    </div>
                                </div>
                            </div>
                            <div class="chat-content">
                                <span class="chat-author">Charlie</span>
                                <div class="chat-bubble chat-bubble-info">
                                    <p class="chat-bubble chat-bubble-primary">Duis aute irure dolor in</p>
                                    <p class="mb-0">Excepteur sint occaecat cupidatat non proident, sunt in.</p>
                                </div>
                                <span class="chat-time">2 hrs ago</span>
                            </div>
                        </div>
                        <div class="chat-item chat-item-end">
                            <div class="chat-avatar">
                                <!-- <div class="avatar avatar-circle"> -->
                                <div class="avatar avatar-xs avatar-circle">
                                    <div class="avatar-display">
                                        <i class="fa fa-user"></i>
                                    </div>
                                </div>
                                <!-- </div> -->
                            </div>
                            <div class="chat-content">
                                <span class="chat-author">Me</span>
                                <div class="chat-bubble chat-bubble-primary">
                                    <div class="chat-bubble chat-bubble-primary">
                                        <div class="rich-list-item p-0">
                                            <div class="rich-list-prepend">
                                                <div class="avatar avatar-xs avatar-label-primary">
                                                    <div class="avatar-display">
                                                        <i class="far fa-file"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="rich-list-content">
                                                <h4 class="rich-list-title">Porta.zip</h4>
                                                <span class="rich-list-subtitle">Cras justo odio</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mb-0">Lorem ipsum dolor sit amet.</p>
                                </div>
                                <p class="chat-bubble chat-bubble-primary">Duis aute irure dolor in</p>
                                <span class="chat-time">3 hrs ago</span>
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
