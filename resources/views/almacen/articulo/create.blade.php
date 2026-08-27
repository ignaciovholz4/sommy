@extends('layouts.admin')
@section('contenido')
<style>
    .select-slim-variantes.ss-main {
        width: 100%;
        height: calc(2.25rem + 2px);
    }
    .show-variante-product{
        height:390px;
        overflow: auto;    
    }
</style>
<section class="section margindivsection">
    <div class="card">
        <div class="modal-body">
            @include('almacen.articulo.form_product')        
        </div>
    </div>
</section>
@endsection
@section('scripts')
  <script src="{{asset('js/funciones_articulo/create_articulo.js')}}?v={{ filemtime(public_path('js/funciones_articulo/create_articulo.js')) }}"></script> 
  <script>
  $(document).ready(function() {
    // Quick Category Creation
    $('#saveQuickCategory').on('click', function() {
        var categoryName = $('#categoryName').val().trim();
        var categoryDescription = $('#categoryDescription').val().trim();
        
        if (!categoryName) {
            Swal.fire('Error', 'El nombre de la categoría es requerido', 'error');
            return;
        }
        
        // Show loading
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: '{{ route("quick_create_category") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nombre: categoryName,
                descripcion: categoryDescription
            },
            success: function(response) {
                if (response.success) {
                    // Add new category to select dropdown
                    var newOption = new Option(categoryName, response.category.idcategoria, true, true);
                    $('#categoria-select').append(newOption).trigger('change');
                    
                    // Close modal and show success message
                    $('#quickCategoryModal').modal('hide');
                    Swal.fire('Éxito', 'Categoría creada exitosamente', 'success');
                    
                    // Reset form
                    $('#quickCategoryForm')[0].reset();
                } else {
                    Swal.fire('Error', response.message || 'Error al crear la categoría', 'error');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al crear la categoría';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                // Re-enable button
                $('#saveQuickCategory').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Categoría');
            }
        });
    });
    
    // Reset modal when closed
    $('#quickCategoryModal').on('hidden.bs.modal', function() {
        $('#quickCategoryForm')[0].reset();
    });

    // Quick Marca Creation
    $('#saveQuickMarca').on('click', function() {
        var marcaName = $('#marcaName').val().trim();
        var marcaDescription = $('#marcaDescription').val().trim();

        if (!marcaName) {
            Swal.fire('Error', 'El nombre de la marca es requerido', 'error');
            return;
        }

        // Show loading
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("quick_create_marca") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nombre: marcaName,
                descripcion: marcaDescription
            },
            success: function(response) {
                if (response.success) {
                    // Add new marca to select dropdown
                    var newOption = new Option(marcaName, response.marca.idmarca, true, true);
                    $('#marca-select').append(newOption).trigger('change');

                    // Close modal and show success message
                    $('#quickMarcaModal').modal('hide');
                    Swal.fire('Éxito', 'Marca creada exitosamente', 'success');

                    // Reset form
                    $('#quickMarcaForm')[0].reset();
                } else {
                    Swal.fire('Error', response.message || 'Error al crear la marca', 'error');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al crear la marca';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                $('#saveQuickMarca').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Marca');
            }
        });
    });

    // Reset modal when closed
    $('#quickMarcaModal').on('hidden.bs.modal', function() {
        $('#quickMarcaForm')[0].reset();
    });

    // Quick Unidad Creation
    $('#saveQuickUnidad').on('click', function() {
        var unidadName = $('#unidadName').val().trim();
        var unidadShortName = $('#unidadShortName').val().trim();
        var unidadDecimal = $('#unidadDecimal').val();

        if (!unidadName || !unidadShortName) {
            Swal.fire('Error', 'El nombre y nombre corto de la unidad son requeridos', 'error');
            return;
        }

        // Show loading
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("quick_create_unidad") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nombre: unidadName,
                nombre_corto: unidadShortName,
                decimal: unidadDecimal
            },
            success: function(response) {
                if (response.success) {
                    // Add new unidad to select dropdown
                    var newOption = new Option(unidadName + ' (' + unidadShortName + ')', response.unidad.idunidad, true, true);
                    $('#unidad-select').append(newOption).trigger('change');

                    // Close modal and show success message
                    $('#quickUnidadModal').modal('hide');
                    Swal.fire('Éxito', 'Unidad creada exitosamente', 'success');

                    // Reset form
                    $('#quickUnidadForm')[0].reset();
                } else {
                    Swal.fire('Error', response.message || 'Error al crear la unidad', 'error');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al crear la unidad';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                $('#saveQuickUnidad').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Unidad');
            }
        });
    });

    // Reset modal when closed
    $('#quickUnidadModal').on('hidden.bs.modal', function() {
        $('#quickUnidadForm')[0].reset();
    });
  });
  </script>
@endsection