<!-- Modal comprobación de stock -->
<div class="modal fade" id="checkStockModal" tabindex="-1" aria-labelledby="checkStockModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Comprobación de stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
            <tr>
              <th>Artículo</th>
              <th>Pedido</th>
              <th>Stock disponible</th>
              <th>Sucursales</th>
            </tr>
          </thead>
          <tbody id="stock-check-body">
            <!-- Se llena dinámicamente con JS -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button class="btn btn-primary" id="btn-confirm-stock">Confirmar stock</button>
      </div>
    </div>
  </div>
</div>