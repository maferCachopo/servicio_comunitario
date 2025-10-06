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
<script>
// Initialize loan request functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof LoanRequestManager !== 'undefined') {
        new LoanRequestManager();
    }
});
</script>
@endpush

{{-- Vista exclusiva de Préstamo de Partituras para usuarios loan_user --}}
{{-- Esta vista habilita únicamente el acceso al formulario de solicitud de préstamo --}}