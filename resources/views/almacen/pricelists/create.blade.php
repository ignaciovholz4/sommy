@extends('layouts.admin')
@section('contenido')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Nueva lista de precios</h5>
    <a href="{{ route('pricelists.index') }}" class="btn btn-secondary btn-sm">Volver</a>
  </div>
  <div class="card-body">
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('pricelists.store') }}" method="POST">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Nombre</label>
          <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="form-group col-md-6">
          <label>Contexto</label>
          <select name="context" class="form-control" required>
            <option value="">Seleccionar contexto</option>
            <option value="sales" {{ old('context') == 'sales' ? 'selected' : '' }}>Venta</option>
            <option value="purchase" {{ old('context') == 'purchase' ? 'selected' : '' }}>Compra</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Tipo</label>
          <select name="type" class="form-control" required>
            <option value="">Seleccionar tipo</option>
            <option value="general_percentage" {{ old('type') == 'general_percentage' ? 'selected' : '' }}>Lista por porcentaje general</option>
          </select>
        </div>
        <div class="form-group col-md-6">
          <label>Tipo de valor</label>
          <select name="value_type" class="form-control">
            <option value="">Seleccionar tipo de valor</option>
            <option value="discount" {{ old('value_type') == 'discount' ? 'selected' : '' }}>Descuento</option>
            <option value="increase" {{ old('value_type') == 'increase' ? 'selected' : '' }}>Aumento</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Porcentaje</label>
          <input type="number" step="0.01" min="0" name="percentage" class="form-control" value="{{ old('percentage') }}">
        </div>

        <div class="form-group col-md-6">
          <label>Aplicable</label>
          <select name="applicable" class="form-control">
            <option value="" {{ old('applicable')=='' ? 'selected' : '' }}>Seleccionar tipo de aplicación</option>
            <option value="categoria" {{ old('applicable')=='categoria' ? 'selected' : '' }}>Categoría</option>
            <option value="marca" {{ old('applicable')=='marca' ? 'selected' : '' }}>Marca</option>
            <option value="articulo" {{ old('applicable')=='articulo' ? 'selected' : '' }}>Artículo</option>
          </select>
        </div>
      </div>

      <div class="form-group mt-4">
        <h6>Seleccionar elementos aplicables</h6>
        <div id="applicable-options">
          <p class="text-muted">Seleccione un tipo de aplicación para ver las opciones.</p>
        </div>
      </div>


      <div class="form-group">
        <button type="submit" class="btn btn-primary">Crear lista</button>
        <a href="{{ route('pricelists.index') }}" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const applicableSelect = document.querySelector('select[name="applicable"]');
  const optionsContainer = document.getElementById('applicable-options');

  applicableSelect.addEventListener('change', function() {
    const value = this.value;
    optionsContainer.innerHTML = '<div class="alert alert-info">Cargando opciones...</div>';

    if (!value) {
      optionsContainer.innerHTML = '<div class="alert alert-secondary">Seleccione un tipo de aplicación para ver las opciones.</div>';
      return;
    }

    fetch(`/price-lists/load-options/${value}`)
      .then(response => response.json())
      .then(data => {
        if (!data || data.length === 0) {
          optionsContainer.innerHTML = '<div class="alert alert-warning">No hay elementos disponibles.</div>';
          return;
        }

        let html = `
          <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered align-middle">
              <thead class="thead-light">
                <tr>
                  <th class="col-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="select-all">
                      <label class="form-check-label" for="select-all">Todos</label>
                    </div>
                  </th>
        `;

        // Encabezados según tipo
        if (value === 'categoria') {
          html += '<th>Nombre (Categoría)</th><th>Descripción</th>';
        } else if (value === 'marca') {
          html += '<th>Nombre (Marca)</th><th>Descripción</th>';
        } else if (value === 'articulo') {
          html += '<th>Nombre (Artículo)</th><th>Código</th><th>Precio con IVA</th>';
        }

        html += '</tr></thead><tbody>';

        // Filas según tipo
        data.forEach(item => {
          if (value === 'categoria') {
            html += `
              <tr>
                <td class="col-2">
                  <div class="form-check">
                    <input class="form-check-input applicable-checkbox" type="checkbox" name="applicable_ids[]" value="${item.id}" id="chk-cat-${item.id}">
                  </div>
                </td>
                <td><label for="chk-cat-${item.id}" class="mb-0"><strong>${item.nombre}</strong></label></td>
                <td>${item.descripcion ?? ''}</td>
              </tr>
            `;
          } else if (value === 'marca') {
            html += `
              <tr>
                <td class="col-2">
                  <div class="form-check">
                    <input class="form-check-input applicable-checkbox" type="checkbox" name="applicable_ids[]" value="${item.id}" id="chk-marca-${item.id}">
                  </div>
                </td>
                <td><label for="chk-marca-${item.id}" class="mb-0"><strong>${item.nombre}</strong></label></td>
                <td>${item.descripcion ?? ''}</td>
              </tr>
            `;
          } else if (value === 'articulo') {
            html += `
              <tr>
                <td class="col-2">
                  <div class="form-check">
                    <input class="form-check-input applicable-checkbox" type="checkbox" name="applicable_ids[]" value="${item.id}" id="chk-art-${item.id}">
                  </div>
                </td>
                <td><label for="chk-art-${item.id}" class="mb-0"><strong>${item.nombre}</strong></label></td>
                <td>${item.codigo}</td>
                <td><span class="badge badge-info">$${parseFloat(item.pventa_con_iva).toFixed(2)}</span></td>
              </tr>
            `;
          }
        });

        html += '</tbody></table></div>';
        optionsContainer.innerHTML = html;

        // Lógica de seleccionar todo
        const selectAll = document.getElementById('select-all');
        const checkboxes = optionsContainer.querySelectorAll('.applicable-checkbox');

        selectAll.addEventListener('change', function() {
          checkboxes.forEach(cb => cb.checked = this.checked);
        });
      })
      .catch(error => {
        console.error(error);
        optionsContainer.innerHTML = '<div class="alert alert-danger">Error al cargar opciones.</div>';
      });
  });
});
</script>
@endsection