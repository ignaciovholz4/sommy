@extends('layouts.admin')
@section('contenido')

<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-3"><i class="fas fa-robot mr-2"></i> Reposición inteligente de stock</h4>
            <p class="text-muted">
                Compara la velocidad de venta reciente de cada artículo contra su stock y su mínimo por sucursal.
                Los que están por debajo generan un <strong>pedido de compra borrador</strong> agrupado por proveedor
                (no toca stock ni cuentas por pagar hasta que lo conviertas a mano en
                <a href="{{ route('pedidos-compra.index') }}">Pedidos de Compra</a>).
            </p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('finanzas.reposicion.ajustes.guardar') }}" method="POST">
                @csrf

                <div class="form-group form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" {{ $ajustes->activo ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">Corrida automática semanal activada (lunes 06:30)</label>
                </div>

                <div class="form-group">
                    <label for="ventana_analisis_dias">Ventana de análisis (días)</label>
                    <input type="number" class="form-control" id="ventana_analisis_dias" name="ventana_analisis_dias"
                           value="{{ $ajustes->ventana_analisis_dias }}" min="7" max="365" required>
                    <small class="form-text text-muted">Cuántos días hacia atrás se mira la venta para calcular la velocidad diaria de cada artículo.</small>
                </div>

                <div class="form-group">
                    <label for="dias_cobertura_objetivo">Días de cobertura objetivo</label>
                    <input type="number" class="form-control" id="dias_cobertura_objetivo" name="dias_cobertura_objetivo"
                           value="{{ $ajustes->dias_cobertura_objetivo }}" min="1" max="365" required>
                    <small class="form-text text-muted">Para cuántos días de venta se sugiere reponer stock (velocidad diaria × este número).</small>
                </div>

                <div class="form-group">
                    <label for="stock_minimo_default">Stock mínimo por defecto</label>
                    <input type="number" class="form-control" id="stock_minimo_default" name="stock_minimo_default"
                           value="{{ $ajustes->stock_minimo_default }}" min="0" required>
                    <small class="form-text text-muted">Se usa para los artículos que no tienen un mínimo propio cargado en Stock por Sucursal.</small>
                </div>

                <button type="submit" class="btn btn-primary mt-2">
                    <i class="fas fa-save mr-1"></i> Guardar ajustes
                </button>
            </form>
        </div>
    </div>
</section>

@endsection
