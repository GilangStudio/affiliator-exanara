<!-- BEGIN PAGE LIBRARIES -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="{{ asset('libs/apexcharts/dist/apexcharts.min.js') }}" defer></script>
<script src="{{ asset('libs/jsvectormap/dist/jsvectormap.min.js') }}" defer></script>
<script src="{{ asset('libs/jsvectormap/dist/maps/world.js') }}" defer></script>
<script src="{{ asset('libs/jsvectormap/dist/maps/world-merc.js') }}" defer></script>
<script src="{{ asset('libs/list.js/dist/list.min.js') }}" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<!-- END PAGE LIBRARIES -->
<!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
{{-- <script src="{{ asset('js/tabler.min.js') }}" defer></script> --}}
<!-- END GLOBAL MANDATORY SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
@stack('scripts')
