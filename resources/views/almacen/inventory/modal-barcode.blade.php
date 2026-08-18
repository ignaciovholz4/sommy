<!-- Modal -->
<div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="barcodeModalLabel">Imprimir etiquetas de producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form method="get" action="{{ route('barcodeProductpdf') }}">
        <div class="modal-body">
          <div class="mb-3">
            <label for="productoNombre" class="form-label">Producto seleccionado</label>
            <input type="text" class="form-control" id="productoNombre" name="productoNombre" readonly>
          </div>
          <div class="mb-3">
            <label for="quantity" class="form-label">Cantidad de etiquetas</label>
            <input type="number" class="form-control" id="quantity" name="quantity" min="1" placeholder="Ej: 10" required>
            <input type="hidden" id="productoId" name="productoId">
          </div>
          <div class="mb-3">
            <label class="form-label">Formato de etiqueta</label>
            <p class="small text-muted">
              La etiqueta incluirá: <strong>Nombre</strong>, <strong>Código</strong>, <strong>Precio venta s/IVA</strong>, <strong>Precio venta c/IVA</strong> y el <strong>Código de barras</strong>.
            </p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Generar PDF</button>
        </div>
      </form>
    </div>
  </div>
</div>