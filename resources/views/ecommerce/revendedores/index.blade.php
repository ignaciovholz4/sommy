@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Vendé Sommy y ganá comisión | Programa de revendedores')
@section('meta_description', 'Sumate como revendedor de Sommy. Te damos tu link y tu QR: compartís, la gente compra y vos cobrás comisión. Sin stock, sin inversión y sin trámites.')
@section('contentEcommerce')

<style>
    .rvp { font-family: 'Poppins', sans-serif; color: #1B2B5A; }

    .rvp-hero {
        background: linear-gradient(135deg, #131C36 0%, #1B2B5A 55%, #223a75 100%);
        color: #fff; padding: 84px 20px 96px; text-align: center; position: relative; overflow: hidden;
    }
    .rvp-hero .kicker { font-size: 12px; letter-spacing: .22em; text-transform: uppercase; color: #C6A15B; font-weight: 500; }
    .rvp-hero h1 { font-size: clamp(30px, 5vw, 50px); font-weight: 600; margin: 14px 0 16px; line-height: 1.15; }
    .rvp-hero p { font-size: clamp(15px, 2vw, 17px); font-weight: 300; color: #D3DAEC; max-width: 640px; margin: 0 auto 30px; line-height: 1.7; }
    .rvp-hero .cta {
        display: inline-block; background: #fff; color: #1B2B5A; border-radius: 999px;
        padding: 15px 40px; font-size: 15px; font-weight: 600; text-decoration: none;
        box-shadow: 0 16px 40px rgba(0,0,0,.28); transition: transform .25s ease;
    }
    .rvp-hero .cta:hover { transform: translateY(-2px); color: #1B2B5A; }

    .rvp-pasos { max-width: 1080px; margin: -52px auto 0; padding: 0 20px; position: relative; z-index: 3; }
    .rvp-pasos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 18px; }
    .rvp-paso {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 20px; padding: 28px 24px;
        box-shadow: 0 20px 50px rgba(27,43,90,.10);
    }
    .rvp-paso .n {
        width: 40px; height: 40px; border-radius: 50%; background: #1B2B5A; color: #fff;
        display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px; margin-bottom: 14px;
    }
    .rvp-paso h3 { font-size: 16.5px; font-weight: 600; margin-bottom: 8px; }
    .rvp-paso p { font-size: 13.5px; font-weight: 300; color: #5D6884; line-height: 1.65; margin: 0; }

    .rvp-beneficios { max-width: 1080px; margin: 74px auto 0; padding: 0 20px; }
    .rvp-h2 { font-size: clamp(24px, 3.4vw, 32px); font-weight: 600; text-align: center; margin-bottom: 12px; }
    .rvp-lead { text-align: center; font-size: 15px; font-weight: 300; color: #5D6884; max-width: 620px; margin: 0 auto 38px; line-height: 1.7; }
    .rvp-benef-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
    .rvp-benef { text-align: center; padding: 8px; }
    .rvp-benef i { font-size: 26px; color: #C6A15B; margin-bottom: 12px; display: block; }
    .rvp-benef h4 { font-size: 15.5px; font-weight: 600; margin-bottom: 6px; }
    .rvp-benef p { font-size: 13.5px; font-weight: 300; color: #5D6884; line-height: 1.65; margin: 0; }

    .rvp-form-wrap { background: #F4F6FB; margin-top: 78px; padding: 66px 20px 80px; }
    .rvp-form-card {
        max-width: 720px; margin: 0 auto; background: #fff; border: 1px solid #E7EAF2;
        border-radius: 24px; padding: 40px; box-shadow: 0 24px 60px rgba(27,43,90,.09);
    }
    .rvp-form-card h2 { font-size: 24px; font-weight: 600; margin-bottom: 6px; }
    .rvp-form-card .sub { font-size: 14px; font-weight: 300; color: #5D6884; margin-bottom: 26px; line-height: 1.65; }
    .rvp-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .rvp-campo { margin-bottom: 16px; }
    .rvp-campo label { display: block; font-size: 12px; font-weight: 500; color: #5D6884; margin-bottom: 6px; }
    .rvp-campo input, .rvp-campo textarea, .rvp-campo select {
        width: 100%; border: 1px solid #E1E6F0; border-radius: 12px; padding: 13px 16px;
        font-size: 15px; font-family: inherit; color: #1B2B5A; background: #fff; transition: border-color .2s;
    }
    .rvp-campo input:focus, .rvp-campo textarea:focus { outline: none; border-color: #1B2B5A; }
    .rvp-campo .hint { font-size: 11.5px; color: #9AA5BD; margin-top: 5px; }
    .rvp-sep { border: none; border-top: 1px solid #EDF0F7; margin: 26px 0 22px; }
    .rvp-sep-t { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .1em; color: #9AA5BD; margin-bottom: 14px; }
    .rvp-submit {
        width: 100%; border: none; background: #1B2B5A; color: #fff; border-radius: 999px;
        padding: 16px; font-size: 15.5px; font-weight: 600; cursor: pointer; margin-top: 8px;
        font-family: inherit; transition: background .2s;
    }
    .rvp-submit:hover { background: #2563EB; }
    .rvp-legal { font-size: 12px; color: #9AA5BD; text-align: center; margin-top: 16px; line-height: 1.6; }

    .rvp-error { background: #FBEDE6; color: #b4552d; border-radius: 12px; padding: 13px 18px; font-size: 13.5px; margin-bottom: 20px; }
    .rvp-error ul { margin: 6px 0 0; padding-left: 18px; }

    .rvp-recuperar { max-width: 720px; margin: 26px auto 0; text-align: center; }
    .rvp-recuperar summary { cursor: pointer; font-size: 13.5px; color: #5D6884; list-style: none; }
    .rvp-recuperar summary::-webkit-details-marker { display: none; }
    .rvp-recuperar summary:hover { color: #1B2B5A; }
    .rvp-recuperar form { display: flex; gap: 10px; justify-content: center; margin-top: 16px; flex-wrap: wrap; }
    .rvp-recuperar input { border: 1px solid #E1E6F0; border-radius: 999px; padding: 12px 20px; font-size: 14px; min-width: 260px; font-family: inherit; }
    .rvp-recuperar button { border: 1.5px solid #1B2B5A; background: #fff; color: #1B2B5A; border-radius: 999px; padding: 12px 26px; font-size: 14px; font-weight: 500; cursor: pointer; font-family: inherit; }
    .rvp-recuperar button:hover { background: #1B2B5A; color: #fff; }

    .rvp-faq { max-width: 720px; margin: 70px auto 90px; padding: 0 20px; }
    .rvp-faq details { border-bottom: 1px solid #EDF0F7; padding: 18px 0; }
    .rvp-faq summary { cursor: pointer; font-size: 15.5px; font-weight: 500; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 14px; }
    .rvp-faq summary::-webkit-details-marker { display: none; }
    .rvp-faq summary::after { content: '+'; font-size: 20px; color: #C6A15B; font-weight: 300; }
    .rvp-faq details[open] summary::after { content: '−'; }
    .rvp-faq p { font-size: 14px; font-weight: 300; color: #5D6884; line-height: 1.75; margin: 12px 0 0; }

    @media (max-width: 620px) {
        .rvp-grid2 { grid-template-columns: 1fr; }
        .rvp-form-card { padding: 28px 22px; border-radius: 20px; }
        .rvp-hero { padding: 64px 20px 84px; }
    }
</style>

<div class="rvp">

    <section class="rvp-hero">
        <div class="kicker">Programa de revendedores</div>
        <h1>Vendé Sommy sin poner un peso</h1>
        <p>
            Te damos tu link y tu QR personal. Lo compartís por WhatsApp, Instagram o donde vendas:
            cuando alguien compra por ahí, la venta queda registrada a tu nombre y te pagamos la comisión.
            Sin stock, sin inversión y sin planillas — de la logística, la facturación y la posventa nos ocupamos nosotros.
        </p>
        <a href="#sumarme" class="cta">Quiero mi link</a>
    </section>

    <section class="rvp-pasos">
        <div class="rvp-pasos-grid">
            <div class="rvp-paso">
                <div class="n">1</div>
                <h3>Te registrás</h3>
                <p>Un formulario corto, una sola vez. Al terminar te aparece tu link y tu QR, y también te los mandamos por mail.</p>
            </div>
            <div class="rvp-paso">
                <div class="n">2</div>
                <h3>Compartís</h3>
                <p>Mandás tu link o mostrás tu QR. Tu cliente compra en la tienda con precios y promos oficiales de Sommy.</p>
            </div>
            <div class="rvp-paso">
                <div class="n">3</div>
                <h3>Nosotros entregamos</h3>
                <p>Coordinamos el envío, cobramos y hacemos la posventa. Vos no tocás stock ni te ocupás de la entrega.</p>
            </div>
            <div class="rvp-paso">
                <div class="n">4</div>
                <h3>Cobrás tu comisión</h3>
                <p>Llevamos la cuenta de todo lo que vendiste y te transferimos. No tenés que registrar ni reclamar nada.</p>
            </div>
        </div>
    </section>

    <section class="rvp-beneficios">
        <h2 class="rvp-h2">Por qué conviene</h2>
        <p class="rvp-lead">Somos fabricantes: colchones, sommiers, almohadas, sábanas y todo lo que hace falta para una buena habitación.</p>
        <div class="rvp-benef-grid">
            <div class="rvp-benef">
                <i class="fas fa-wallet"></i>
                <h4>Cero inversión</h4>
                <p>No comprás mercadería ni pagás nada para entrar. Solo compartís tu link.</p>
            </div>
            <div class="rvp-benef">
                <i class="fas fa-industry"></i>
                <h4>Precio de fábrica</h4>
                <p>Tu cliente compra directo al fabricante, con garantía y sin intermediarios.</p>
            </div>
            <div class="rvp-benef">
                <i class="fas fa-truck-fast"></i>
                <h4>Envío y posventa nuestros</h4>
                <p>Entregamos, facturamos y resolvemos cualquier reclamo. Tu trabajo termina en la recomendación.</p>
            </div>
            <div class="rvp-benef">
                <i class="fas fa-qrcode"></i>
                <h4>Link y QR propios</h4>
                <p>Un QR imprimible para tu local, tu feria o tu tarjeta, y un link para redes.</p>
            </div>
        </div>
    </section>

    <div class="rvp-form-wrap" id="sumarme">
        <div class="rvp-form-card">
            <h2>Sumate como revendedor</h2>
            <p class="sub">Completá tus datos y en el mismo momento te generamos tu link. Tarda menos de un minuto.</p>

            @if($errors->any())
                <div class="rvp-error">
                    <strong>Revisá estos datos:</strong>
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @if(session('error_recuperar'))
                <div class="rvp-error">{{ session('error_recuperar') }}</div>
            @endif

            <form method="POST" action="{{ route('revendedores.store') }}">
                @csrf

                <div class="rvp-grid2">
                    <div class="rvp-campo">
                        <label for="rv-nombre">Nombre y apellido *</label>
                        <input type="text" id="rv-nombre" name="nombre" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="rvp-campo">
                        <label for="rv-tel">WhatsApp *</label>
                        <input type="tel" id="rv-tel" name="telefono" value="{{ old('telefono') }}" placeholder="11 5555 5555" required>
                    </div>
                </div>

                <div class="rvp-grid2">
                    <div class="rvp-campo">
                        <label for="rv-email">Email *</label>
                        <input type="email" id="rv-email" name="email" value="{{ old('email') }}" required>
                        <div class="hint">Acá te mandamos tu link.</div>
                    </div>
                    <div class="rvp-campo">
                        <label for="rv-dni">DNI o CUIT</label>
                        <input type="text" id="rv-dni" name="dni_cuit" value="{{ old('dni_cuit') }}">
                    </div>
                </div>

                <div class="rvp-grid2">
                    <div class="rvp-campo">
                        <label for="rv-loc">Localidad</label>
                        <input type="text" id="rv-loc" name="localidad" value="{{ old('localidad') }}">
                    </div>
                    <div class="rvp-campo">
                        <label for="rv-prov">Provincia</label>
                        <input type="text" id="rv-prov" name="provincia" value="{{ old('provincia') }}">
                    </div>
                </div>

                <div class="rvp-campo">
                    <label for="rv-ig">Instagram (opcional)</label>
                    <input type="text" id="rv-ig" name="instagram" value="{{ old('instagram') }}" placeholder="tucuenta">
                </div>

                <div class="rvp-campo">
                    <label for="rv-vende">¿Dónde pensás vender?</label>
                    <textarea id="rv-vende" name="como_vende" rows="3" placeholder="Local, redes, ferias, mi grupo de conocidos...">{{ old('como_vende') }}</textarea>
                </div>

                <hr class="rvp-sep">
                <div class="rvp-sep-t">Para poder pagarte</div>

                <div class="rvp-grid2">
                    <div class="rvp-campo">
                        <label for="rv-alias">Alias de tu cuenta</label>
                        <input type="text" id="rv-alias" name="alias_cbu" value="{{ old('alias_cbu') }}" placeholder="mi.alias.mp">
                    </div>
                    <div class="rvp-campo">
                        <label for="rv-cbu">CBU / CVU</label>
                        <input type="text" id="rv-cbu" name="cbu" value="{{ old('cbu') }}">
                    </div>
                </div>
                <div class="rvp-campo">
                    <label for="rv-titular">Titular de la cuenta</label>
                    <input type="text" id="rv-titular" name="titular_cuenta" value="{{ old('titular_cuenta') }}" placeholder="Si es distinto a tu nombre">
                </div>

                <button type="submit" class="rvp-submit">Generar mi link de revendedor</button>
                <p class="rvp-legal">
                    Al registrarte aceptás nuestros <a href="{{ url('/terminos') }}" style="color:#5D6884;text-decoration:underline;">términos y condiciones</a>.
                    Podés cargar los datos bancarios más adelante escribiéndonos.
                </p>
            </form>
        </div>

        <div class="rvp-recuperar">
            <details>
                <summary>Ya soy revendedor y perdí mi link</summary>
                <form method="POST" action="{{ route('revendedores.recuperar') }}">
                    @csrf
                    <input type="email" name="email" placeholder="El email con el que te registraste" required>
                    <button type="submit">Recuperar mi link</button>
                </form>
            </details>
        </div>
    </div>

    <section class="rvp-faq">
        <h2 class="rvp-h2" style="margin-bottom:26px;">Preguntas frecuentes</h2>

        <details open>
            <summary>¿Cuánto gano por venta?</summary>
            <p>Cobrás un porcentaje sobre el valor de los productos de cada compra confirmada. El porcentaje base es del {{ $comisionBase }}% y te lo confirmamos por mail junto con tu link; si vendés volumen, lo revisamos y lo subimos.</p>
        </details>

        <details>
            <summary>¿Tengo que comprar mercadería o tener stock?</summary>
            <p>No. No comprás nada ni guardás productos. Vos recomendás, la compra se hace en nuestra tienda y nosotros entregamos.</p>
        </details>

        <details>
            <summary>¿Cómo saben que la venta fue mía?</summary>
            <p>Tu link deja una marca en el navegador de quien lo abre y dura 30 días. Si esa persona compra en ese lapso, la venta queda registrada a tu nombre automáticamente. Con el QR pasa lo mismo.</p>
        </details>

        <details>
            <summary>¿Tengo que llevar alguna cuenta o cargar las ventas?</summary>
            <p>No, y esa es la idea: no tenés que hacer ningún seguimiento. Nosotros registramos cada venta tuya, calculamos tu comisión y te transferimos. Si querés saber cómo venís, nos escribís y te pasamos el detalle.</p>
        </details>

        <details>
            <summary>¿Cuándo cobro?</summary>
            <p>Las comisiones se liquidan una vez que el pedido fue entregado y cobrado. Te transferimos a la cuenta que nos dejaste al registrarte.</p>
        </details>

        <details>
            <summary>¿Qué pasa si el cliente devuelve el producto?</summary>
            <p>Si la venta se cae o se anula, esa comisión no se liquida. Las demás siguen su curso normal.</p>
        </details>
    </section>

</div>

@endsection
