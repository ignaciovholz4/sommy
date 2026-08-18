@extends('layouts.admin')
@section('contenido')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Editar lista: {{ $list->name }} ({{ $list->context === 'sales' ? 'Venta' : 'Compra' }})</h5>
    <a href="{{ route('pricelists.index') }}" class="btn btn-secondary btn-sm">Volver</a>
  </div>
  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form action="{{ route('pricelists.update', $list->id) }}" method="POST" class="mb-4">
      @csrf
      @method('PUT')
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Nombre</label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $list->name) }}" required>
        </div>
        <div class="form-group col-md-4">
          <label>Contexto</label>
          <select name="context" class="form-control" required>
            <option value="" {{ old('context', $list->context)=='' ? 'selected' : '' }}>Seleccionar contexto</option>
            <option value="sales" {{ old('context', $list->context)=='sales' ? 'selected' : '' }}>Venta</option>
            <option value="purchase" {{ old('context', $list->context)=='purchase' ? 'selected' : '' }}>Compra</option>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label>Tipo</label>
          <select class="form-control" disabled>
            <option value="" {{ $list->type=='' ? 'selected' : '' }}>Seleccionar tipo</option>
            <option value="general_percentage" @selected($list->type==='general_percentage')>Lista por porcentaje general</option>
          </select>
          <input type="hidden" name="type" value="{{ $list->type }}">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Tipo de valor</label>
          <select name="value_type" class="form-control">
            <option value="" {{ old('value_type', $list->value_type)=='' ? 'selected' : '' }}>Seleccionar tipo de valor</option>
            <option value="discount" @selected($list->value_type==='discount')>Descuento</option>
            <option value="increase" @selected($list->value_type==='increase')>Aumento</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Porcentaje</label>
          <input type="number" step="0.01" min="0" name="percentage" class="form-control" value="{{ old('percentage', $list->percentage) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Aplicable</label>
          <select class="form-control" disabled>
            <option value="" {{ old('applicable', $list->applicable)=='' ? 'selected' : '' }}>Seleccionar tipo de aplicación</option>
            <option value="categoria" @selected($list->applicable==='categoria')>Categoría</option>
            <option value="marca" @selected($list->applicable==='marca')>Marca</option>
            <option value="articulo" @selected($list->applicable==='articulo')>Artículo</option>
          </select>
          <!-- hidden para enviar el valor real -->
          <input type="hidden" name="applicable" value="{{ $list->applicable }}">
        </div>

        <div class="form-group col-md-3 d-flex align-items-center">
          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" name="active" value="1" id="activeChk" {{ $list->active ? 'checked' : '' }}>
            <label class="form-check-label" for="activeChk">Activo</label>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Descripción</label>
        <textarea name="description" class="form-control">{{ old('description', $list->description) }}</textarea>
      </div>

      <!-- Bloque dinámico de selección -->
      <div class="form-group mt-4">
        <h6>Seleccionar elementos aplicables</h6>
        <div id="applicable-options">
          <p class="text-muted">Seleccione un tipo de aplicación para ver las opciones.</p>
        </div>
      </div>

      <button class="btn btn-primary">Guardar</button>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const applicableValue = '{{ $list->applicable }}'; // valor fijo porque el select está disabled
  const optionsContainer = document.getElementById('applicable-options');
  const listId = {{ $list->id }};

  // Cargar la grilla directamente
  loadOptions(applicableValue);

  function loadOptions(value) {
    optionsContainer.innerHTML = '<div class="alert alert-info">Cargando opciones...</div>';

    Promise.all([
      fetch(`/price-lists/load-options/${value}`).then(r => r.json()),
      fetch(`/price-lists/${listId}/items`).then(r => r.json())
    ])
    .then(([data, listData]) => {
      let selectedIds = listData.items.map(i => i.applicable_id);
      if (!data || data.length === 0) {
        optionsContainer.innerHTML = '<div class="alert alert-warning">No hay elementos disponibles.</div>';
        return;
      }

      let html = `
        <div class="table-responsive">
          <table class="table table-striped table-hover table-bordered align-middle">
            <thead class="thead-light">
              <tr>
                <th class="col-2">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all">
                    <label class="form-check-label" for="select-all">Todos</label>
                  </div>
                </th>
      `;

      if (value === 'categoria') {
        html += '<th>Nombre (Categoría)</th><th>Descripción</th>';
      } else if (value === 'marca') {
        html += '<th>Nombre (Marca)</th><th>Descripción</th>';
      } else {
        html += '<th>Nombre (Artículo)</th><th>Código</th><th>Precio con IVA</th>';
      }

      html += '</tr></thead><tbody>';

      data.forEach(item => {
        const checked = selectedIds.includes(item.id) ? 'checked' : '';
        if (value === 'categoria') {
          html += `
            <tr>
              <td class="col-2">
                <div class="form-check">
                  <input class="form-check-input applicable-checkbox" type="checkbox" name="applicable_ids[]" value="${item.id}" id="chk-cat-${item.id}" ${checked}>
                </div>
              </td>
              <td><label for="chk-cat-${item.id}" class="mb-0"><strong>${item.nombre}</strong></label></td>
              <td>${item.descripcion ?? ''}</td>
            </tr>`;
        } else if (value === 'marca') {
          html += `
            <tr>
              <td class="col-2">
                <div class="form-check">
                  <input class="form-check-input applicable-checkbox" type="checkbox" name="applicable_ids[]" value="${item.id}" id="chk-marca-${item.id}" ${checked}>
                </div>
              </td>
              <td><label for="chk-marca-${item.id}" class="mb-0"><strong>${item.nombre}</strong></label></td>
              <td>${item.descripcion ?? ''}</td>
            </tr>`;
        } else {
          html += `
            <tr>
              <td class="col-2">
                <div class="form-check">
                  <input class="form-check-input applicable-checkbox" type="checkbox" name="applicable_ids[]" value="${item.id}" id="chk-art-${item.id}" ${checked}>
                </div>
              </td>
              <td><label for="chk-art-${item.id}" class="mb-0"><strong>${item.nombre}</strong></label></td>
              <td>${item.codigo}</td>
              <td><span class="badge badge-info">$${parseFloat(item.pventa_con_iva).toFixed(2)}</span></td>
            </tr>`;
        }
      });

      html += '</tbody></table></div>';
      optionsContainer.innerHTML = html;

      // Seleccionar todo
      const selectAll = document.getElementById('select-all');
      const checkboxes = optionsContainer.querySelectorAll('.applicable-checkbox');
      selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
      });
    })
    .catch(() => {
      optionsContainer.innerHTML = '<div class="alert alert-danger">Error al cargar opciones.</div>';
    });
  }
});
</script>
@endsection

