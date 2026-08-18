@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Cambios y devoluciones | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container sommy-legal" style="max-width:760px;">
        <h1>Cambios, devoluciones y garantía</h1>

        <h2>Arrepentimiento de compra (compra online)</h2>
        <p>Tenés 10 días corridos desde la entrega para arrepentirte de tu compra online, sin necesidad de justificación y sin costo (Ley 24.240). Usá el <a href="{{ url('/arrepentimiento') }}">botón de arrepentimiento</a> y coordinamos el retiro del producto, que debe estar sin uso y en su embalaje original.</p>

        <h2>Producto con falla o daño de entrega</h2>
        <p>Si el producto llega dañado o presenta una falla de fabricación, lo cambiamos sin costo. Avisanos dentro de las 48 hs de recibido por WhatsApp o por el formulario de contacto, idealmente con fotos, y gestionamos el cambio.</p>

        <h2>Garantía de fábrica</h2>
        <p>Somos fabricantes: cada colchón tiene garantía de fábrica contra defectos de fabricación (el plazo figura en la ficha del producto). La garantía cubre defectos estructurales y no cubre el desgaste normal por uso, manchas o daños por uso indebido.</p>

        <h2>Cómo gestionar cualquier caso</h2>
        <p>Escribinos por <a href="{{ url('/contacto') }}">WhatsApp o nuestros canales de contacto</a> con tu número de pedido. Al ser fabricantes, respondemos directo y sin intermediarios, normalmente dentro de las 24 hs hábiles.</p>
    </div>
</section>

<style>
.sommy-legal h1 { font-size: 26px; font-weight: 600; margin-bottom: 22px; }
.sommy-legal h2 { font-size: 16.5px; font-weight: 600; color: #1B2B5A; margin: 24px 0 6px; }
.sommy-legal p { font-weight: 300; color: #47536F; line-height: 1.8; font-size: 14.5px; }
.sommy-legal a { color: #2563EB; }
</style>

@endsection
