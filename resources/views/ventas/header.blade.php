<style>
    /* Estilos basados en el branding de Facturarg */
    .btn-facturarg {
        background: #0f172a; /* Azul oscuro del dashboard */
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px; /* Bordes redondeados modernos */
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

    .btn-facturarg:hover {
        background: #FF9900; /* Naranja de los arcos del logo */
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(255, 153, 0, 0.3);
        border-color: #FF9900;
        color: #fff !important;
    }

    .btn-facturarg i {
        font-size: 1.1rem;
        color: #00A3E0; /* Celeste branding para el icono */
        transition: color 0.3s ease;
    }

    .btn-facturarg:hover i {
        color: #ffffff; /* El icono pasa a blanco en hover */
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
                <a href="{{ route('ventas.create') }}" class="btn-facturarg">
                    <i class="fa fa-shopping-cart"></i> <span>Realizar una venta</span>
                </a>
            </div>
        </div>
    </div>
</section>