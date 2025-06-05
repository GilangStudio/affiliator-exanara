<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="{{ asset('css/tabler.min.css') }}" rel="stylesheet" />
<link href="{{ asset('icons/tabler-icons.min.css') }}" rel="stylesheet" />
<!-- END GLOBAL MANDATORY STYLES -->

<style>
    @import url("https://rsms.me/inter/inter.css");

    .list-group-item:active, .list-group-item:focus, .list-group-item:hover {
        background-color: transparent !important;
    }

    .navbar .navbar-nav .nav-link .badge {
        top: .750rem !important;
        transform: unset !important;
    }
</style>

@stack('styles')