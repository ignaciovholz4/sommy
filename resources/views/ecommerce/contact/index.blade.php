@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Contacto | Sommy')
@section('contentEcommerce')

    {{-- Hero institucional --}}
    <section class="sommy-page-hero" data-aos="fade-up">
        <span class="kicker">Contacto</span>
        <h1>Hablemos de tu descanso.</h1>
        <p>Escribinos, llamanos o visitanos: te asesoramos con calma y sin compromiso para que encuentres lo que necesitás.</p>
        <div class="sommy-page-hero-illus" style="max-width:300px;">
            <img src="{{ asset('imagenes/marca/sommy-illus-charla.svg') }}" alt="Conversemos sobre tu descanso">
        </div>
    </section>

    {{-- Datos de contacto --}}
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                @if(!empty($arrayEmpresa['adress']))
                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="sommy-info-card">
                        <div class="icon"><i class="fa-solid fa-location-dot"></i></div>
                        <h3>Dirección</h3>
                        <p>{{ $arrayEmpresa['adress'] }}</p>
                    </div>
                </div>
                @endif

                @if(!empty($arrayEmpresa['phone']))
                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="sommy-info-card">
                        <div class="icon"><i class="fa-brands fa-whatsapp"></i></div>
                        <h3>WhatsApp</h3>
                        <p>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $arrayEmpresa['whatsapp']) }}" target="_blank" rel="noopener noreferrer">
                                +{{ $arrayEmpresa['phone'] }}
                            </a>
                        </p>
                    </div>
                </div>
                @endif

                @if(!empty($arrayEmpresa['email']))
                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="sommy-info-card">
                        <div class="icon"><i class="fa-solid fa-envelope"></i></div>
                        <h3>Correo</h3>
                        <p>
                            <a href="mailto:{{ $arrayEmpresa['email'] }}">{{ $arrayEmpresa['email'] }}</a>
                        </p>
                    </div>
                </div>
                @endif

                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="sommy-info-card">
                        <div class="icon"><i class="fa-solid fa-clock"></i></div>
                        <h3>Horario de atención</h3>
                        <p>Todos los días.<br>Consultanos por WhatsApp cuando quieras.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA de cierre --}}
    <section class="pb-5">
        <div class="container-fluid">
            <div class="sommy-cta sommy-noche-estrellas" style="position:relative;overflow:hidden;" data-aos="zoom-in">
                <span class="sommy-estrellas-b" aria-hidden="true"></span>
                {{-- Formas de magia blancas --}}
                <img src="{{ asset('imagenes/marca/sommy-magia-rulo.png') }}" alt="" aria-hidden="true"
                     style="position:absolute;left:2%;top:8%;width:190px;opacity:.25;pointer-events:none;">
                <img src="{{ asset('imagenes/marca/sommy-magia-onda.png') }}" alt="" aria-hidden="true"
                     style="position:absolute;right:2%;bottom:6%;width:260px;opacity:.35;pointer-events:none;transform:scaleX(-1);">
                <h2 style="position:relative;">La forma más rápida: WhatsApp.</h2>
                <p style="position:relative;">Contanos qué estás buscando y te respondemos con opciones, precios y tiempos de entrega.</p>
                <div class="btn-row">
                    @if(!empty($arrayEmpresa['phone']))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $arrayEmpresa['whatsapp']) }}" target="_blank" rel="noopener noreferrer" class="btn-sommy-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i> Iniciar conversación
                    </a>
                    @endif
                    <a href="{{ url('/') }}" class="btn-sommy-primary">Ver la tienda</a>
                </div>
            </div>
        </div>
    </section>

@endsection
