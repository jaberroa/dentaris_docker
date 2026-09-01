<!-- ========== Left Sidebar Start ========== -->
<div class="sidebar-left">

    <div data-simplebar class="h-100">

        <!--- Sidebar-menu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="left-menu list-unstyled" id="side-menu">
                <li>
                    <a href="{{ url('index') }}" class="">
                        <i class="fas fa-desktop"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="menu-title">Pages</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow ">
                        <i class="fa fa-unlock-alt"></i>
                        <span>Authentication</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ url('auth-login') }}"><i
                                    class="mdi mdi-checkbox-blank-circle align-middle"></i> Login</a></li>
                        <li><a href="{{ url('auth-register') }}"><i
                                    class="mdi mdi-checkbox-blank-circle align-middle"></i> Register</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow ">
                        <i class="fa fa-unlink"></i>
                        <span>Error</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ url('pages-404') }}"><i
                                    class="mdi mdi-checkbox-blank-circle align-middle"></i> Error 404</a></li>
                        <li><a href="{{ url('pages-500') }}"><i
                                    class="mdi mdi-checkbox-blank-circle align-middle"></i> Error 500</a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ url('pages-starter') }}"><i class="fas fa-pager"></i> <span>Starter Page</span></a>
                </li>

                <li>
                    <a href="{{ url('pages-comingsoon') }}"><i class="fas fa-tape"></i> <span>Coming Soon</span></a>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
