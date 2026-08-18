<style>
    /* Consistencia total con el branding de Facturarg */
    .btn-facturarg-presupuesto {
        background: #0f172a; /* Fondo oscuro inicial igual al de ventas */
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 10px 24px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        letter-spacing: -0.3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .btn-facturarg-presupuesto:hover {
        background: #22c55e; /* Cambia a Verde al pasar el mouse (diferencia de Ventas) */
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(34, 197, 94, 0.3);
        border-color: #22c55e;
        color: #fff !important;
    }

    .btn-facturarg-presupuesto i {
        font-size: 1.1rem;
        color: #66DD7D; /* Verde suave inicial para el icono */
        transition: color 0.3s ease;
    }

    .btn-facturarg-presupuesto:hover i {
        color: #ffffff;
    }

    .margindivsection {
        margin-bottom: 2rem;
    }
</style>
<br>
<section class="content margindivsection">
    <div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <div class="d-flex align-items-center">
            <div>
                <a href="{{ route('presupuestos.create') }}" class="btn-facturarg-presupuesto">
                    <i class="fa fa-file-invoice"></i> 
                    <strong>Realizar un presupuesto</strong>
                </a>
            </div>
        </div>
    </div>
</section>