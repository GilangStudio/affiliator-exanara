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

    .form-select:focus {
        box-shadow: var(--tblr-shadow-input), 0 0 0 .12rem rgba(var(--tblr-primary-rgb), 1) !important;
    }

    .form-imagecheck-input:checked~.form-imagecheck-figure {
        border: 4px var(--tblr-border-style) var(--tblr-primary) !important;
    }
</style>

@stack('styles')