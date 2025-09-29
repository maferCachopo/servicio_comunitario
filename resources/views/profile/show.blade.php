@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Perfil de Administrador') }}</div>

                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        {{-- Foto de Perfil en Círculo --}}
                        @if (Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Foto de Perfil" class="profile-picture-circle">
                        @else
                            {{-- Icono por defecto si no hay foto --}}
                            <div class="profile-picture-circle default-profile-icon">
                                <i class="fas fa-user fa-3x text-secondary"></i>
                            </div>
                        @endif

                        {{-- Nombre del Usuario --}}
                        <h2 class="ms-3 mb-0">{{ Auth::user()->name }}</h2>
                    </div>

                    <hr>

                    {{-- Información Estática --}}
                    <dl class="row">
                        <dt class="col-sm-3">Email:</dt>
                        <dd class="col-sm-9">{{ Auth::user()->email }}</dd>

                        <dt class="col-sm-3">Miembro desde:</dt>
                        <dd class="col-sm-9">{{ Auth::user()->created_at->format('d/m/Y') }}</dd>
                    </dl>

                    <hr>

                    {{-- Botón para ir a la Edición --}}
                    <div class="text-end">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            <i class="fas fa-pencil-alt me-1"></i> Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection