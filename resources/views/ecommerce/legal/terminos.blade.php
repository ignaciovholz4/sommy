@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Términos y condiciones | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container sommy-legal" style="max-width:760px;">
        <h1>Términos y condiciones</h1>

        <h2>1. Sobre nosotros</h2>
        <p>{{ $arrayEmpresa['name'] ?? 'Sommy' }} es una empresa fabricante de colchones que comercializa sus productos y complementos para el descanso a través de esta tienda online y otros canales de venta.</p>

        <h2>2. Productos y precios</h2>
        <p>Los precios publicados están expresados en pesos argentinos e incluyen IVA. Las fotos son ilustrativas de los modelos. Los precios y el stock pueden modificarse sin previo aviso; el precio válido es el vigente al momento de confirmar el pedido.</p>

        <h2>3. Compra y pago</h2>
        <p>Al confirmar un pedido recibís un número de seguimiento. El pago puede realizarse online (Mercado Pago), por transferencia bancaria o por el medio coordinado con nuestro equipo. El pedido se prepara una vez confirmado el pago.</p>

        <h2>4. Envíos y entrega</h2>
        <p>Realizamos envíos a domicilio coordinando día y franja horaria. Los plazos y costos dependen de la zona de entrega y se informan antes de cerrar la compra. Al recibir, revisá el producto antes de firmar la conformidad.</p>

        <h2>5. Cambios, devoluciones y garantía</h2>
        <p>Consultá nuestra <a href="{{ url('/cambios-y-devoluciones') }}">política de cambios y devoluciones</a>. Todos nuestros colchones cuentan con garantía de fábrica cuyo plazo figura en la ficha de cada producto.</p>

        <h2>6. Derecho de arrepentimiento</h2>
        <p>Si compraste online, podés arrepentirte dentro de los 10 días corridos desde la entrega (Ley 24.240, art. 34) usando el <a href="{{ url('/arrepentimiento') }}">botón de arrepentimiento</a>, sin costo alguno.</p>

        <h2>7. Datos personales</h2>
        <p>Los datos que nos brindás se usan exclusivamente para gestionar tu compra, la entrega y la comunicación con vos. No compartimos tus datos con terceros ajenos a la operación (Ley 25.326 de Protección de Datos Personales).</p>

        <h2>8. Contacto</h2>
        <p>Ante cualquier consulta escribinos por <a href="{{ url('/contacto') }}">nuestros canales de contacto</a>.</p>
    </div>
</section>

<style>
.sommy-legal h1 { font-size: 26px; font-weight: 600; margin-bottom: 22px; }
.sommy-legal h2 { font-size: 16.5px; font-weight: 600; color: #1B2B5A; margin: 24px 0 6px; }
.sommy-legal p { font-weight: 300; color: #47536F; line-height: 1.8; font-size: 14.5px; }
.sommy-legal a { color: #2563EB; }
</style>

@endsection
