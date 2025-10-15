@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Sección de Préstamo de Partituras - Solo para usuarios loan_user --}}
            @if(Auth::check() && Auth::user()->role == 'loan_user')
                <div class="card" id="loan-section">
                    <div class="card-header">
                        <i class="fas fa-music me-2"></i>Préstamo de Partituras
                    </div>
                    <div class="card-body">
                        {{-- Botón para solicitar préstamo --}}
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loanRequestModal">
                                <i class="fas fa-hand-paper me-2"></i>Solicitar préstamo
                            </button>
                        </div>

                        {{-- Historial de préstamos del usuario --}}
                        <div class="loan-history">
                            <h5>Historial de Préstamos</h5>
                            <div id="userLoanHistory" class="table-responsive">
                                <p class="text-muted">Cargando historial...</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Include the loan request modal --}}
                @include('profile.partials.loan_request_modal')
            @else
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-exclamation-triangle me-2"></i>Acceso Restringido
                    </div>
                    <div class="card-body">
                        <p class="text-muted">No tiene permisos para acceder a esta sección.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')

{{-- 1. Carga el archivo JS que define LoanRequestManager usando Vite --}}
@vite('resources/js/loan-request.js')

{{-- 2. Tu script que inicializa la clase (ahora sí la encontrará) --}}
<script>
// Initialize loan request functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof LoanRequestManager !== 'undefined') {
        new LoanRequestManager();
    } else {
        // Este mensaje te ayudará a depurar si algo no funciona
        console.error('La clase LoanRequestManager no está definida. Asegúrate de que "npm run dev" esté en ejecución y que el archivo esté configurado en vite.config.js.');
    }
});
</script>
@endpush