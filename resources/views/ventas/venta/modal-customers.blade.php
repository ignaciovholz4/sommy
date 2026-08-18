
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header style-modal-form">
        <h5 class="modal-title" id="exampleModalLabel">Buscar un cliente</h5>
        <div class="d-flex">
          <button type="button" class="btn btn-outline-primary btn-sm mr-2" data-bs-toggle="modal" data-bs-target="#quickCustomerModal" data-bs-dismiss="modal">
            <i class="fas fa-plus"></i> Agregar Cliente
          </button>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
      <div class="modal-body style-modal-form">
        <div class="container">
          <div class="row height d-flex justify-content-center align-items-center">
              <div class="col-md-6">
                  <div class="div-content-search"> <i class="fa fa-search"></i> <input type="text" id="customers-searchs" class="form-control input_search" placeholder="Buscar un cliente..." autocomplete="off">  </div>
              </div>
          </div>
          <br>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">Agregar</th>
                <th scope="col">Nombre</th>
                <th scope="col">Direccion</th>
                <th scope="col">Email</th>
              </tr>
            </thead>
            <tbody id="show-customers">
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer style-modal-form">
        <button type="button" class="btn btn5 btn-close-customer" data-bs-dismiss="modal"><i class="fas fa-window-close mr-2 "></i>Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Quick Customer Creation Modal -->
<div class="modal fade" id="quickCustomerModal" tabindex="-1" aria-labelledby="quickCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header style-modal-form">
        <h5 class="modal-title" id="quickCustomerModalLabel">
          <i class="fas fa-user-plus"></i> Agregar Cliente Rápido
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body style-modal-form">
        <form id="quickCustomerForm">
          @csrf
          <div class="form-group mb-3">
            <label>Nombre<i class="text-danger"><strong>*</strong></i></label>
            <input type="text" class="form-control style-input" name="nombre" required placeholder="Ingresa el nombre del cliente">
          </div>
          <div class="form-group mb-3">
            <label>Direccion<i class="text-danger"><strong>*</strong></i></label>
            <input type="text" class="form-control style-input" name="direccion" required placeholder="Ingresa la direccion del cliente">
          </div>
          <div class="form-group mb-3">
            <label>Telefono<i class="text-danger"><strong>*</strong></i></label>
            <input type="number" class="form-control style-input" name="telefono" required placeholder="Ingresa el telefono del cliente">
          </div>
          <div class="form-group mb-3">
            <label>Email<i class="text-danger"><strong>*</strong></i></label>
            <input type="email" class="form-control style-input" name="email" required placeholder="Ingresa el email del cliente">
          </div>
          <div id="quick-customer-errors" class="alert alert-danger" style="display: none;">
            <ul id="quick-customer-error-list"></ul>
          </div>
        </form>
      </div>
      <div class="modal-footer style-modal-form">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="button" class="btn btn-primary" id="saveQuickCustomer">
          <i class="fas fa-save"></i> Guardar Cliente
        </button>
      </div>
    </div>
  </div>
</div>