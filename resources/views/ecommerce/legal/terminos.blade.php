@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Términos y condiciones | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container sommy-legal" style="max-width:760px;">
        <h1>Términos y condiciones</h1>
        <p class="sommy-legal-fecha">Última actualización: {{ now()->translatedFormat('F Y') }}</p>

        <h2>1. Introducción</h2>
        <p>Bienvenido a Sommy. Estos Términos y Condiciones regulan el uso de este sitio web y la compra de productos a través de nuestra tienda online. Al acceder y utilizar este sitio, usted acepta estar sujeto a estos términos y a nuestra <a href="{{ url('/politica-de-privacidad') }}">Política de Privacidad</a>.</p>
        <p>Sommy se reserva el derecho de modificar estos términos en cualquier momento. Las modificaciones entran en vigor desde su publicación en esta página, por lo que recomendamos revisarla periódicamente.</p>

        <h2>2. Sobre nosotros</h2>
        <p>Sommy es una fábrica y distribuidora de colchones, sommiers y complementos para el descanso, que comercializa sus productos a través de esta tienda online y de nuestros canales de atención por WhatsApp, Facebook e Instagram.</p>

        <h2>3. Uso del sitio web</h2>
        <p>Usted declara tener al menos 18 años, o acceder bajo la supervisión de un padre o tutor. Se compromete a usar este sitio únicamente para fines lícitos. Queda prohibido usarlo para fines ilegales, intentar acceder a áreas restringidas, transmitir virus o código malicioso, o interferir con su funcionamiento normal.</p>

        <h2>4. Productos y precios</h2>
        <p>Los precios publicados están expresados en pesos argentinos (ARS) e incluyen IVA. Las fotos son ilustrativas de los modelos: no podemos garantizar que los colores se vean exactamente iguales en cada dispositivo. Los precios y el stock pueden modificarse sin previo aviso; el precio válido es el vigente al momento de confirmar el pedido.</p>

        <h2>5. Proceso de compra</h2>
        <p>Para comprar: seleccioná los productos y agregalos al carrito, revisá el pedido en el checkout, indicá tus datos de envío, elegí el medio de pago y confirmá. Al confirmar recibís un número de pedido para hacer seguimiento. Un asesor puede contactarte por WhatsApp para coordinar el pago y la entrega.</p>

        <h2>6. Métodos de pago</h2>
        <p>Actualmente aceptamos únicamente <strong>efectivo</strong> y <strong>transferencia bancaria</strong>. No procesamos pagos con tarjeta de crédito/débito ni financiación en cuotas por el momento. El pedido se prepara una vez coordinado o acreditado el pago.</p>

        <h2>7. Envíos y entregas</h2>
        <p>Realizamos envío propio con nuestros fleteros en <strong>Córdoba Capital y alrededores</strong>, coordinando día y franja horaria con cada cliente. Si tu domicilio está fuera de esa zona, un asesor te confirma por WhatsApp si llegamos y cómo. Al recibir el pedido, revisá el producto antes de firmar la conformidad.</p>

        <h2>8. Garantía</h2>
        <p>Todos nuestros colchones y sommiers cuentan con garantía de fábrica contra defectos de fabricación; el plazo exacto según el modelo figura en la ficha de cada producto o te lo confirma un asesor. La garantía no cubre daños por mal uso, negligencia, accidentes o desgaste normal.</p>

        <h2>9. Cambios, devoluciones y derecho de arrepentimiento</h2>
        <p>Consultá nuestra <a href="{{ url('/cambios-y-devoluciones') }}">política de cambios y devoluciones</a>. Si compraste online, podés arrepentirte dentro de los 10 días corridos desde la entrega, sin necesidad de justificar el motivo (Ley 24.240, art. 34), usando el <a href="{{ url('/arrepentimiento') }}">botón de arrepentimiento</a>, sin costo alguno. Si el producto llega con fallas de fábrica o distinto al pedido, coordinamos el cambio sin cargo.</p>

        <h2>10. Propiedad intelectual</h2>
        <p>Los textos, imágenes, logotipos y demás contenido de este sitio son propiedad de Sommy o de sus proveedores de contenido y están protegidos por las leyes de propiedad intelectual argentinas. Queda prohibida su reproducción o uso comercial sin autorización expresa.</p>

        <h2>11. Datos personales</h2>
        <p>Los datos que nos brindás se usan exclusivamente para gestionar tu compra, la entrega y la comunicación con vos, conforme a la Ley 25.326 de Protección de Datos Personales. Para el detalle completo de qué datos recopilamos y cómo los tratamos, consultá nuestra <a href="{{ url('/politica-de-privacidad') }}">Política de Privacidad</a>.</p>

        <h2>12. Limitación de responsabilidad</h2>
        <p>Sommy no será responsable por daños indirectos o consecuentes derivados del uso de nuestros productos o de este sitio. Nuestra responsabilidad máxima está limitada al precio de compra del producto en cuestión. No garantizamos que el sitio esté libre de errores o funcione sin interrupciones.</p>

        <h2>13. Ley aplicable y jurisdicción</h2>
        <p>Estos Términos y Condiciones se rigen por las leyes de la República Argentina. Ante cualquier disputa, las partes se someten a la jurisdicción de los tribunales ordinarios de la Ciudad de Córdoba.</p>

        <h2>14. Contacto</h2>
        <p>Ante cualquier consulta sobre estos Términos, escribinos por WhatsApp al {{ $arrayEmpresa['whatsapp'] ?? '3513133881' }} o por <a href="{{ url('/contacto') }}">nuestros canales de contacto</a>.</p>
    </div>
</section>

<style>
.sommy-legal h1 { font-size: 26px; font-weight: 600; margin-bottom: 4px; }
.sommy-legal-fecha { color: #94A3B8; font-size: 12.5px; margin-bottom: 22px; }
.sommy-legal h2 { font-size: 16.5px; font-weight: 600; color: #1B2B5A; margin: 24px 0 6px; }
.sommy-legal p { font-weight: 300; color: #47536F; line-height: 1.8; font-size: 14.5px; }
.sommy-legal a { color: #2563EB; }
</style>

@endsection
