@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Inventario') }}</div>

                <div class="card-body">
                    <!-- Bootstrap Tabs -->
                    <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="partituras-tab" data-bs-toggle="tab" data-bs-target="#partituras" type="button" role="tab" aria-controls="partituras" aria-selected="true">
                                Historial de Partituras
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="prestamos-tab" data-bs-toggle="tab" data-bs-target="#prestamos" type="button" role="tab" aria-controls="prestamos" aria-selected="false">
                                Historial de Préstamos
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="inventoryTabContent">
                        <div class="tab-pane fade show active" id="partituras" role="tabpanel" aria-labelledby="partituras-tab">
                            <div class="mt-4">
                                @include('inventory.partials.partituras_table')
                            </div>
                        </div>
                        <div class="tab-pane fade" id="prestamos" role="tabpanel" aria-labelledby="prestamos-tab">
                            <div class="mt-4">
                                @include('inventory.partials.prestamos_table')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- DataTables Spanish Language -->
<script src="https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"></script>

<script>
$(document).ready(function() {
    console.log('Page loaded, setting up DataTables...');

    let partiturasTable = null;
    let prestamosTable = null;

    // Function to initialize Partituras DataTable
    function initPartiturasTable() {
        if (partiturasTable) return; // Already initialized

        console.log('Initializing Partituras DataTable...');
        partiturasTable = $('#partiturasTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.partituras.data') }}",
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, error, thrown) {
                    console.error('Partituras DataTables AJAX Error:', xhr.responseText);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading partituras data: ' + xhr.status + ' - ' + xhr.responseText);
                }
            },
            columns: [
                { data: 'titulo', name: 'titulo' },
                { data: 'autor', name: 'autor' },
                { data: 'tipo_contribucion', name: 'tipo_contribucion' },
                { data: 'anio', name: 'anio' },
                { data: 'cantidad', name: 'cantidad' },
                { data: 'gaveta', name: 'gaveta' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            },
            dom: 'Bfrtip',
            buttons: [
                'excel', 'pdf', 'print'
            ],
            responsive: true,
            initComplete: function() {
                console.log('Partituras DataTable initialized successfully');
            }
        });
    }

    // Function to initialize Prestamos DataTable
    function initPrestamosTable() {
        if (prestamosTable) return; // Already initialized

        console.log('Initializing Prestamos DataTable...');
        prestamosTable = $('#prestamosTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.prestamos.data') }}",
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, error, thrown) {
                    console.error('Prestamos DataTables AJAX Error:', xhr.responseText);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading prestamos data: ' + xhr.status + ' - ' + xhr.responseText);
                }
            },
            columns: [
                { data: 'usuario_nombre', name: 'usuario_nombre' },
                { data: 'obra_titulo', name: 'obra_titulo' },
                { data: 'fecha_prestamo', name: 'fecha_prestamo' },
                { data: 'fecha_devolucion', name: 'fecha_devolucion' },
                { data: 'estado', name: 'estado' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            },
            dom: 'Bfrtip',
            buttons: [
                'excel', 'pdf', 'print'
            ],
            responsive: true,
            initComplete: function() {
                console.log('Prestamos DataTable initialized successfully');
            }
        });
    }

    // Initialize the active tab's table immediately
    setTimeout(function() {
        console.log('Checking initial tab state...');
        console.log('Partituras tab classes:', $('#partituras').attr('class'));
        console.log('Prestamos tab classes:', $('#prestamos').attr('class'));

        if ($('#partituras').hasClass('show') || $('#partituras').hasClass('active')) {
            console.log('Initializing partituras table on page load');
            initPartiturasTable();
        }

        // Also check if prestamos tab is active (in case someone bookmarks it)
        if ($('#prestamos').hasClass('show') || $('#prestamos').hasClass('active')) {
            console.log('Initializing prestamos table on page load');
            initPrestamosTable();
        }
    }, 100);

    // Handle tab changes - Bootstrap 5 uses buttons, not links
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var targetTab = $(e.target).attr('data-bs-target'); // Get the target tab

        console.log('Tab changed to:', targetTab);

        if (targetTab === '#partituras') {
            initPartiturasTable();
        } else if (targetTab === '#prestamos') {
            initPrestamosTable();
        }
    });

    // Also listen for click events as backup
    $('button[data-bs-toggle="tab"]').on('click', function (e) {
        var targetTab = $(this).attr('data-bs-target');

        console.log('Tab clicked:', targetTab);

        // Small delay to ensure Bootstrap has processed the tab change
        setTimeout(function() {
            if (targetTab === '#partituras') {
                initPartiturasTable();
            } else if (targetTab === '#prestamos') {
                initPrestamosTable();
            }
        }, 50);
    });

    // Also listen for the older Bootstrap events just in case
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var targetTab = $(e.target).attr('href');

        if (targetTab === '#partituras') {
            initPartiturasTable();
        } else if (targetTab === '#prestamos') {
            initPrestamosTable();
        }
    });
});
</script>
@endpush
@endsection