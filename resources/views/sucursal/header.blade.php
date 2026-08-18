<style>
    :root {
        --dark-navy: #0f172a;
        --accent-green: #22c55e;
        --soft-gray: #f8fafc;
    }

    .header-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
        border-left: 5px solid var(--dark-navy); /* Acento azul oscuro a la izquierda */
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
        background-color: var(--dark-navy); /* Fondo azul oscuro */
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);
    }

    .btn-sucursal:hover {
        background-color: #1e293b;
        color: #66DD7D; /* El verde que usabas ahora queda como acento al pasar el mouse */
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.3);
    }

    .btn-sucursal i {
        font-size: 1.1rem;
        margin-right: 8px;
        color: #66DD7D; /* El icono mantiene tu verde de identidad */
    }

    .breadcrumb-custom {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 5px;
    }
</style>

<section class="content margindivsection px-4 pt-4">
    <div class="header-section d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <div class="d-flex flex-column">
            <span class="breadcrumb-custom">Configuración > Empresa</span>
            <h2 class="page-title">Gestión de Sucursales</h2>
        </div>

        <div class="d-flex align-items-center mt-3 mt-sm-0">
            <a href="#" id="btnNuevaSucursal" class="btn-sucursal">
                <i class="fa fa-plus-circle"></i>
                <span>NUEVA SUCURSAL</span>
            </a>
        </div>
    </div>
</section>