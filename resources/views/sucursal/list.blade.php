@extends('layouts.admin')

@section('contenido')
<style>
    /* VARIABLES Y ESTILOS GLOBALES DE FACTURARG */
    :root {
        --dark-navy: #0f172a;
        --brand-blue: #1e293b;
        --accent-blue: #3b82f6;
        --accent-green: #66DD7D;
        --border-color: #e2e8f0;
        --soft-bg: #f8fafc;
    }

    .content-wrapper-custom {
        background-color: var(--soft-bg);
        min-height: 100vh;
        padding: 2rem;
    }

    /* ENCABEZADO ESTILO APP BAR */
    .header-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        border-left: 5px solid var(--dark-navy);
    }

    .page-title {
        color: var(--dark-navy);
        font-weight: 800;
        font-size: 1.25rem;
        margin-bottom: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-sucursal {
        background-color: var(--dark-navy);
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
    }

    .btn-sucursal:hover {
        background-color: var(--brand-blue);
        color: var(--accent-green);
        transform: translateY(-2px);
    }

    /* CARD Y TABLA UNIFICADA */
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        background: #fff;
        overflow: hidden;
    }

    .table thead th {
        background-color: var(--dark-navy);
        color: #ffffff;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 15px;
        border: none;
    }

    .table tbody td {
        vertical-align: middle;
        padding: 1rem;
        color: #334155;
        border-bottom: 1px solid var(--border-color);
    }

    /* BOTONES DE ACCIÓN CIRCULARES (IGUAL A PRECIOS) */
    .btn-action { 
        width: 32px; 
        height: 32px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 8px; 
        transition: all 0.2s; 
        margin-left: 5px;
        border: none;
        text-decoration: none;
    }

    .btn-edit { background-color: #f1f5f9; color: var(--dark-navy); }
    .btn-edit:hover { background-color: var(--dark-navy); color: #ffffff !important; }

    .btn-delete { background-color: #fee2e2; color: #dc2626; }
    .btn-delete:hover { background-color: #dc2626; color: #ffffff !important; }

    /* BADGES SOFT */
    .badge-soft {
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-soft-success { background: #dcfce7; color: #15803d; }

    /* MODAL PREMIUM */
    .modal-content { border: none; border-radius: 15px; }
    .modal-header-dark {
        background-color: var(--dark-navy);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 1.5rem;
    }
    .form-label {
        font-weight: 700;
        color: var(--dark-navy);
        font-size: 0.8rem;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .form-control {
        border: 2px solid #f1f5f9;
        border-radius: 8px;
        padding: 10px 15px;
    }
    .form-control:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
</style>

<div class="content-wrapper-custom">
    
    <section class="header-section d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex flex-column">
            <small class="text-muted fw-bold">CONFIGURACIÓN > EMPRESA</small>
            <h2 class="page-title">Gestión de Sucursales</h2>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="javascript:void(0)" id="btnTransferirStock" class="btn-sucursal ms-2">
                <i class="fa fa-exchange-alt me-2"></i> TRANSFERIR STOCK
            </a>
            <a href="javascript:void(0)" id="btnNuevaSucursal" class="btn-sucursal">
                <i class="fa fa-plus-circle me-2"></i> NUEVA SUCURSAL
            </a>
        </div>
    </section>

    <div class="card card-modern">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="tablaSucursales">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Artículos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b>Casa Central</b></td>
                            <td><span class="text-muted">001</span></td>
                            <td>Av. Colón 123, Córdoba</td>
                            <td>351-1234567</td>
                            <td><span class="badge-soft badge-soft-success">Activo</span></td>
                            <td><span class="badge bg-light text-dark border">1,240</span></td>
                            <td class="text-end">
                                <a href="javascript:void(0)" class="btn-action btn-edit"><i class="fas fa-pen fa-xs"></i></a>
                                <button class="btn-action btn-delete"><i class="fas fa-trash fa-xs"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSucursal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-dark d-flex justify-content-between align-items-center">
                <h5 class="modal-title m-0"><i class="fas fa-store me-2 text-info"></i> DATOS DE LA SUCURSAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formSucursal">
                    <input type="hidden" id="sucursal_id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nombre Comercial</label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Código Suc.</label>
                            <input type="text" class="form-control" id="codigo">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección Física</label>
                        <input type="text" class="form-control" id="direccion">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email de Contacto</label>
                            <input type="email" class="form-control" id="email">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                <button type="button" class="btn btn-primary px-4" id="btnGuardarSucursal" style="background-color: var(--dark-navy); border: none;">
                    <i class="fas fa-save me-2"></i> GUARDAR SUCURSAL
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalTransferirStock" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header-dark d-flex justify-content-between align-items-center">
        <h5 class="modal-title m-0">
          <i class="fas fa-exchange-alt me-2 text-success"></i> TRANSFERENCIA DE STOCK ENTRE SUCURSALES
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="formTransferirStock">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Sucursal Origen</label>
              <select class="form-select" id="stock_origen" name="origen_id" required></select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Sucursal Destino</label>
              <select class="form-select" id="stock_destino" name="destino_id" required></select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-8">
              <label class="form-label">Producto</label>
              <select class="form-select" id="stock_producto" name="producto_id" required></select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Cantidad</label>
              <input type="number" min="1" class="form-control" id="stock_cantidad" name="cantidad" required>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">CANCELAR</button>
        <button type="submit" form="formTransferirStock" class="btn btn-primary px-4" style="background-color: var(--dark-navy); border: none;">
          <i class="fas fa-exchange-alt me-2"></i> CONFIRMAR TRANSFERENCIA
        </button>
      </div>
    </div>
  </div>
</div>

@endsection