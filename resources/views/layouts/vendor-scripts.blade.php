<!-- JAVASCRIPT -->
<script src="{{ asset('build/libs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('build/libs/bootstrap/dist/js/bootstrap.bundle.js') }}"></script>
<script src="{{ asset('build/libs/metismenu/dist/metisMenu.js') }}"></script>
<script src="{{ asset('build/libs/simplebar/dist/simplebar.js') }}"></script>
<script src="{{ asset('build/libs/node-waves/dist/waves.js') }}"></script>

<script>
// JavaScript para manejar los menús desplegables - Solo lo necesario
(function ($) {
    'use strict';

    function initMetisMenu() {
        //metis menu
        $("#side-menu").metisMenu();
    }

    function initLeftMenuCollapse() {
        $('#sidebar-btn').on('click', function (event) {
            event.preventDefault();
            $('body').toggleClass('sidebar-enable');
            if ($(window).width() >= 992) {
                $('body').toggleClass('sidebar-collpsed');
            } else {
                $('body').removeClass('sidebar-collpsed');
            }
        });

        $('body,html').click(function (e) {
            var container = $("#sidebar-btn");
            if (!container.is(e.target) && container.has(e.target).length === 0 && !(e.target).closest('div.vertical-menu')) {
                $("body").removeClass("sidebar-enable");
            }
        });
    }

    function initActiveMenu() {
        // === following js will activate the menu in left side bar based on url ====
        $("#sidebar-menu a").each(function () {
            var pageUrl = window.location.href.split(/[?#]/)[0];
            if (this.href == pageUrl) {
                $(this).addClass("active");
                $(this).parent().addClass("mm-active"); // add active to li of the current link
                $(this).parent().parent().addClass("mm-show");
                $(this).parent().parent().prev().addClass("mm-active"); // add active class to an anchor
                $(this).parent().parent().parent().addClass("mm-active");
                $(this).parent().parent().parent().parent().addClass("mm-show"); // add active to li of the current link
                $(this).parent().parent().parent().parent().parent().addClass("mm-active");
            }
        });
    }

    function init() {
        initMetisMenu();
        initLeftMenuCollapse();
        initActiveMenu();
        Waves.init();
    }

    init();

})(jQuery);
</script>

@yield('scripts')
