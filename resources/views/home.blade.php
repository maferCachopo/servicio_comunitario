@extends('layouts.app')

@push('styles')
<style>
@if(Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'loan_user'))
.main-content {
    background-image: url('/storage/unet_fondo.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
}
@endif
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Mensaje de autenticación activa para usuarios loan_user --}}
                    @if(Auth::check() && Auth::user()->role == 'loan_user')
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ __('Autenticación activa - Usuario de préstamo conectado') }}
                        </div>
                    @else
                        {{ __('You are logged in!') }}
                    @endif

                    {{-- Notificación de préstamos pendientes para administradores --}}
                    @if(Auth::check() && Auth::user()->role == 'admin' && $pendingLoansCount > 0)
                        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                            <i class="fas fa-bell me-2"></i>
                            <strong>¡Atención!</strong> Tienes <strong>{{ $pendingLoansCount }}</strong> solicitudes de préstamo pendientes de revisión.
                            <a href="{{ route('inventory.index') }}" class="alert-link ms-2">
                                Ir al panel de inventario <i class="fas fa-arrow-right"></i>
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
