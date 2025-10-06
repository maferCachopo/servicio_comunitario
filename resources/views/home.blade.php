@extends('layouts.app')

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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
