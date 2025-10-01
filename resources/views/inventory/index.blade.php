@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Inventario') }}</div>

                <div class="card-body">
                    <div class="text-center">
                        <h1 class="display-4">¡Bienvenido al Inventario!</h1>
                        <p class="lead">Esta es la sección de gestión de inventario del sistema.</p>
                        <hr class="my-4">
                        <p>Próximamente aquí encontrarás todas las herramientas para administrar el inventario de partituras.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection