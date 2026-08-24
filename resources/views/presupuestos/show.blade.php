@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Presupuesto #{{ $presupuesto->id }}</h1>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Cliente</h5>
            <p class="card-text">
                {{ $presupuesto->cliente->nombre }} {{ $presupuesto->cliente->paterno }} {{ $presupuesto->cliente->materno }} <br>
                {{ $presupuesto->cliente->direccion }} <br>
                Tel: {{ $presupuesto->cliente->telefono }} <br>
                Email: {{ $presupuesto->cliente->email }}
            </p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Datos del Presupuesto</h5>
            <p class="card-text">
                Fecha: {{ \Carbon\Carbon::parse($presupuesto->fecha)->format('d/m/Y') }} <br>
                Estado: 
                <span class="badge 
                    @if($presupuesto->estado == 'borrador') badge-secondary 
                    @elseif($presupuesto->estado == 'confirmado') badge-success 
                    @else badge-info @endif">
                    {{ ucfirst($presupuesto->estado) }}
                </span> <br>
                Total: <strong>${{ number_format($presupuesto->total, 2, ',', '.') }}</strong>
            </p>
        </div>
    </div>

    <h3>Detalles</h3>
    <table class="table table-bordered">
        <thead class="thead-light">
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presupuesto->detalles as $detalle)
            <tr>
                <td>{{ optional($detalle->articulo)->nombre ?? 'Producto eliminado del catálogo' }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td>${{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                <td>${{ number_format($detalle->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('presupuestos.index') }}" class="btn btn-secondary">Volver</a>
    <a href="{{ route('presupuestos.edit', $presupuesto) }}" class="btn btn-warning">Editar</a>
    <form action="{{ route('presupuestos.destroy', $presupuesto) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este presupuesto?')">
            Eliminar
        </button>
    </form>
</div>
@endsection