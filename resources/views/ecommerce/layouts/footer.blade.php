<style>
.ec-footer {
    background: #0f172a;
    color: #94a3b8;
    font-size: 14px;
}
.ec-footer-main {
    padding: 56px 0 40px;
}
.ec-footer h6 {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #fff;
    margin-bottom: 16px;
}
.ec-footer-brand img {
    height: 48px;
    width: auto;
    border-radius: 8px;
    object-fit: contain;
    margin-bottom: 12px;
}
.ec-footer-brand .brand-name {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    display: block;
    margin-bottom: 4px;
}
.ec-footer-info { line-height: 1.8; }
.ec-footer-info i {
    width: 16px;
    color: #64748b;
    margin-right: 6px;
    font-size: 12px;
}
.ec-footer-links { list-style: none; padding: 0; margin: 0; }
.ec-footer-links li { margin-bottom: 10px; }
.ec-footer-links a {
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.15s;
}
.ec-footer-links a:hover { color: #fff; }
.ec-footer-social {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 4px;
}
.ec-footer-social a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px; height: 36px;
    border-radius: 8px;
    background: #1e293b;
    color: #94a3b8;
    text-decoration: none;
    font-size: 15px;
    transition: background 0.15s, color 0.15s;
}
.ec-footer-social a:hover {
    background: #334155;
    color: #fff;
}
.ec-footer-bottom {
    border-top: 1px solid #1e293b;
    padding: 18px 0;
}
.ec-footer-bottom-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    max-width: 1600px;
    margin: 0 auto;
    padding: 0 24px;
}
.ec-footer-bottom small { color: #475569; font-size: 13px; }
.ec-powered {
    font-size: 12px;
    color: #334155;
}
</style>

<footer class="ec-footer">
    {{-- Trails de magia decorando el cielo estrellado del footer --}}
    {{-- Solo aparición por fundido: las rotaciones van inline para que AOS no las pise --}}
    <img src="{{ asset('imagenes/marca/sommy-magia-trazo.png') }}" alt="" class="ec-footer-magia" aria-hidden="true" data-aos="fade" data-aos-duration="1400">
    <img src="{{ asset('imagenes/marca/sommy-magia-onda.png') }}" alt="" class="ec-footer-magia--2" aria-hidden="true" style="transform:scaleX(-1) rotate(-6deg);" data-aos="fade" data-aos-delay="250" data-aos-duration="1400">
    <img src="{{ asset('imagenes/marca/sommy-magia-rulo.png') }}" alt="" class="ec-footer-magia--3" aria-hidden="true" style="transform:rotate(10deg);" data-aos="fade" data-aos-delay="120" data-aos-duration="1400">
    <div class="ec-footer-main">
        <div class="container-fluid">
            <div class="row g-5">

                {{-- Marca --}}
                <div class="col-lg-4 col-md-6">
                    <div class="ec-footer-brand">
                        {{-- Letras blancas sin pluma, directo sobre el azul noche --}}
                        <img src="{{ asset('imagenes/marca/sommy-logo-blanco-texto.png') }}" alt="Sommy" style="height:52px;width:auto;border-radius:0;background:none;">
                        <span class="brand-name" style="display:none;">{{ $arrayEmpresa['name'] }}</span>
                    </div>
                    <div class="ec-footer-info">
                        @if(!empty($arrayEmpresa['adress']))
                        <div class="mb-1"><i class="fa-solid fa-location-dot"></i>{{ $arrayEmpresa['adress'] }}</div>
                        @endif
                        @if(!empty($arrayEmpresa['email']))
                        <div class="mb-1"><i class="fa-solid fa-envelope"></i>{{ $arrayEmpresa['email'] }}</div>
                        @endif
                        @if(!empty($arrayEmpresa['phone']))
                        <div><i class="fa-solid fa-phone"></i>+{{ $arrayEmpresa['phone'] }}</div>
                        @endif
                    </div>
                </div>

                {{-- Empresa --}}
                <div class="col-lg-2 col-md-3 col-6">
                    <h6>Empresa</h6>
                    <ul class="ec-footer-links">
                        <li><a href="{{ url('/nosotros') }}">Nosotros</a></li>
                        <li><a href="{{ url('/contacto') }}">Contacto</a></li>
                        <li><a href="{{ url('/revendedores') }}">Vendé Sommy</a></li>
                        <li><a href="{{ url('/terminos') }}">Términos y condiciones</a></li>
                        <li><a href="{{ url('/cambios-y-devoluciones') }}">Cambios y devoluciones</a></li>
                        <li><a href="{{ url('/arrepentimiento') }}" style="color:#fff !important;font-weight:500;">Botón de arrepentimiento</a></li>
                    </ul>
                </div>

                {{-- Categorías --}}
                <div class="col-lg-3 col-md-3 col-6">
                    <h6>Categorías</h6>
                    <ul class="ec-footer-links">
                        @foreach ($getCategoryLimit as $catL)
                            @if($loop->index < 5)
                            <li><a href="{{ url('categoria/' . $catL->slug) }}">{{ $catL->nombre }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                {{-- Redes --}}
                <div class="col-lg-3 col-md-6">
                    <h6>Seguinos</h6>
                    <div class="ec-footer-social">
                        <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter / X"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        <a href="#" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="ec-footer-bottom">
        <div class="ec-footer-bottom-inner">
            <small>&copy; {{ date('Y') }} {{ $arrayEmpresa['name'] }}. Todos los derechos reservados.</small>
            {{-- Acceso al panel de administración --}}
            <a href="{{ Auth::check() ? url('/dashboard') : url('/login') }}" class="ec-footer-admin">
                <i class="fa-solid fa-lock"></i> Panel de administración
            </a>
            <span class="ec-powered">Powered by <strong style="color:#475569;">FacturARG</strong></span>
        </div>
    </div>
</footer>
