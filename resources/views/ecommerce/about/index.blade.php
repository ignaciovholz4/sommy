@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Nosotros | Sommy')
@section('contentEcommerce')

    {{-- Hero institucional --}}
    <section class="sommy-page-hero" data-aos="fade-up">
        <span class="kicker">Nosotros</span>
        <h1>El arte del descanso, hecho con calma.</h1>
        <p>En {{ $arrayEmpresa['name'] ?? 'Sommy' }} creemos que dormir bien no es un lujo: es la base de un buen día. Por eso trabajamos todos los días para que encuentres tu mejor descanso.</p>
        <div class="sommy-page-hero-illus">
            <img src="{{ asset('imagenes/marca/sommy-illus-descanso.svg') }}" alt="El arte del descanso Sommy">
        </div>
    </section>

    {{-- Quiénes somos + Por qué elegirnos --}}
    <section class="py-5">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="sommy-section-heading" style="text-align:left;">¿Quiénes somos?</h2>
                    <p style="font-weight:300;line-height:1.8;color:#5D6884;">
                        Somos fabricantes de colchones: cada uno nace en nuestra fábrica y llega
                        directo a tu casa, sin intermediarios. Y completamos tu descanso con
                        sábanas, almohadas y sommiers elegidos con el mismo criterio —
                        todo lo que tu habitación necesita, en un solo lugar.
                    </p>
                    <p style="font-weight:300;line-height:1.8;color:#5D6884;">
                        Atendemos de manera cercana y honesta: te escuchamos, te asesoramos
                        y te acompañamos hasta que tu compra llega a tu casa.
                    </p>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="sommy-section-heading" style="text-align:left;">¿Por qué elegirnos?</h2>
                    {{-- Viñetas destello: máximo 3 por vista, regla del brandbook --}}
                    <ul class="sommy-list mt-3">
                        <li><strong style="color:#1B2B5A;font-weight:500;">Selección serena.</strong> Pocos productos, bien elegidos: calidad antes que cantidad.</li>
                        <li><strong style="color:#1B2B5A;font-weight:500;">Asesoría real.</strong> Te recomendamos según cómo dormís vos, no según lo que necesitamos vender.</li>
                        <li><strong style="color:#1B2B5A;font-weight:500;">Acompañamiento total.</strong> De la consulta a la entrega, siempre hablás con nosotros.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Caja de cierre (estilo CTA noche con estrellas y magia) --}}
    <section class="pb-5">
        <div class="container-fluid">
            <div class="sommy-cta sommy-noche-estrellas" style="position:relative;overflow:hidden;" data-aos="zoom-in">
                <span class="sommy-estrellas-b" aria-hidden="true"></span>
                {{-- Formas de magia blancas --}}
                <img src="{{ asset('imagenes/marca/sommy-magia-rulo.png') }}" alt="" aria-hidden="true"
                     style="position:absolute;left:2%;top:8%;width:190px;opacity:.25;pointer-events:none;">
                <img src="{{ asset('imagenes/marca/sommy-magia-onda.png') }}" alt="" aria-hidden="true"
                     style="position:absolute;right:2%;bottom:6%;width:260px;opacity:.35;pointer-events:none;transform:scaleX(-1);">
                <h2 style="position:relative;">Descansá tranquilo, estamos cerca.</h2>
                <p style="position:relative;">Visitanos, escribinos o hacé tu pedido online: lo importante es que esta noche duermas mejor.</p>
                <div class="btn-row">
                    @if(!empty($arrayEmpresa['phone']))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $arrayEmpresa['whatsapp']) }}" target="_blank" rel="noopener noreferrer" class="btn-sommy-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i> Escribinos por WhatsApp
                    </a>
                    @endif
                    <a href="{{ url('/contacto') }}" class="btn-sommy-primary">Contactanos</a>
                </div>
            </div>
        </div>
    </section>

@endsection
