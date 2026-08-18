<!-- Modal add more stock-->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addStockModalLabel">Agregar mas stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-add-new-variant">
            <input type="hidden" name="variantIdStockProduct" id="variantIdStockProduct">
            <input type="number" name="inputNewStockVariant" id="inputNewStockVariant" min="1" class="form-control">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btnCloseAddStock" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnAddMoreStock">Guardar</button>
      </div>
    </div>
  </div>
</div>