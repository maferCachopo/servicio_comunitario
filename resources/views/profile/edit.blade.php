@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Editar Perfil') }}</div>

                <div class="card-body">
                    {{-- Mensaje de éxito después de actualizar --}}
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Mostramos errores de validación generales si los hay --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Directiva para usar el método HTTP PUT --}}

                        {{-- Campo para el Nombre --}}
                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Nombre') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        {{-- Campo para el Email (deshabilitado) --}}
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled>
                                <small class="form-text text-muted">El email no se puede modificar.</small>
                            </div>
                        </div>

                        {{-- Campo para el Rol (solo lectura) --}}
                        <div class="row mb-3">
                            <label for="role" class="col-md-4 col-form-label text-md-end">{{ __('Rol') }}</label>
                            <div class="col-md-6">
                                <input id="role" type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
                                <small class="form-text text-muted">El rol no se puede modificar.</small>
                            </div>
                        </div>

                        {{-- Campo para la Foto de Perfil --}}
                        <div class="row mb-3">
                            <label for="photo" class="col-md-4 col-form-label text-md-end">{{ __('Foto de Perfil') }}</label>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    @if ($user->profile_photo_path)
                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Foto de Perfil" width="100" class="img-thumbnail">
                                    @else
                                        <small class="text-muted">No hay foto de perfil.</small>
                                    @endif
                                </div>
                                <input id="photo" type="file" class="form-control @error('photo') is-invalid @enderror" name="photo">
                                @error('photo')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <hr>
                        <p class="text-center text-muted">Deja los campos de contraseña en blanco si no deseas cambiarla.</p>

                        {{-- Campo para la Nueva Contraseña --}}
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Nueva Contraseña') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        {{-- Campo para Confirmar la Nueva Contraseña --}}
                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirmar Nueva Contraseña') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
                            </div>
                        </div>

                        {{-- Botón de Guardar Cambios --}}
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Guardar Cambios') }}
                                </button>
                                <a href="{{ route('profile.show') }}" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-1"></i> Volver al Perfil
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection