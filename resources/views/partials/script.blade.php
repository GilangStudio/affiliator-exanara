<!-- BEGIN PAGE LIBRARIES -->
<script src="{{ asset('libs/apexcharts/dist/apexcharts.min.js') }}" defer></script>
<script src="{{ asset('libs/jsvectormap/dist/jsvectormap.min.js') }}" defer></script>
<script src="{{ asset('libs/jsvectormap/dist/maps/world.js') }}" defer></script>
<script src="{{ asset('libs/jsvectormap/dist/maps/world-merc.js') }}" defer></script>
<script src="{{ asset('libs/list.js/dist/list.min.js') }}" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<!-- END PAGE LIBRARIES -->
<!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
<script src="{{ asset('js/tabler.min.js') }}" defer></script>
<!-- END GLOBAL MANDATORY SCRIPTS -->

{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.min.js" integrity="sha384-RuyvpeZCxMJCqVUGFI0Do1mQrods/hhxYlcVfGPOfQtPJh0JCw12tUAZ/Mv10S7D" crossorigin="anonymous"></script> --}}
@stack('scripts')
