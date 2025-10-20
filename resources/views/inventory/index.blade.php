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
                { data: 'instrumento', name: 'instrumento' },
                { data: 'cantidad', name: 'cantidad' },
                { data: 'gaveta', name: 'gaveta' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
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
                { data: 'instrumento', name: 'instrumento' },
                { data: 'cantidad', name: 'cantidad' },
                { data: 'fecha_prestamo', name: 'fecha_prestamo' },
                { data: 'fecha_devolucion', name: 'fecha_devolucion' },
                { data: 'estado', name: 'estado' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
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

    // Handle edit inventory button clicks
    $(document).on('click', '.edit-inventario-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        const cantidad = btn.data('cantidad');
        const cantidadDisponible = btn.data('cantidad-disponible');
        const estanteId = btn.data('estante-id');
        const gaveta = btn.data('gaveta');

        // Populate modal fields
        $('#editInventarioId').val(id);
        $('#editCantidad').val(cantidad);
        $('#editCantidadDisponible').val(cantidadDisponible);
        $('#editGaveta').val(gaveta);

        // Show modal
        $('#editInventarioModal').modal('show');
    });

    // Handle form submission
    $('#editInventarioForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const id = $('#editInventarioId').val();
        const cantidad = $('#editCantidad').val();
        const gaveta = $('#editGaveta').val();

        // Basic validation
        if (!cantidad || cantidad <= 0) {
            alert('La cantidad debe ser mayor que 0');
            return;
        }

        if (!gaveta || gaveta.trim() === '') {
            alert('La gaveta no puede estar vacía');
            return;
        }

        // AJAX request
        $.ajax({
            url: `inventory/${id}`,
            method: 'PUT',
            data: {
                cantidad: cantidad,
                gaveta: gaveta.trim(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Hide modal and remove focus from close button
                    $('#editInventarioModal').modal('hide');
                    // Remove focus from any element inside the modal
                    $('#editInventarioModal').find(':focus').blur();
                    partiturasTable.ajax.reload();
                    alert('Inventario actualizado exitosamente');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let message = 'Error al actualizar el inventario';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            }
        });
    });

    // Handle modal hide event to prevent focus issues
    $('#editInventarioModal').on('hidden.bs.modal', function () {
        // Ensure no elements inside the modal retain focus
        $(this).find(':focus').blur();
    });
});
</script>

<!-- Modal para editar inventario -->
<div class="modal fade" id="editInventarioModal" tabindex="-1" aria-labelledby="editInventarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInventarioModalLabel">Editar Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editInventarioForm">
                    <input type="hidden" id="editInventarioId" name="id">
                    
                    <div class="mb-3">
                        <label for="editCantidad" class="form-label">Cantidad Total</label>
                        <input type="number" class="form-control" id="editCantidad" name="cantidad" min="1" required>
                        <div class="form-text">Número total de copias existentes</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editGaveta" class="form-label">Ubicación (Gaveta)</label>
                        <input type="text" class="form-control" id="editGaveta" name="gaveta" required>
                        <div class="form-text">Ubicación física en el estante</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="editInventarioForm">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection