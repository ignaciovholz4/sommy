@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Programa de Creadores Sommy | Creá contenido y ganá comisión')
@section('meta_description', 'Sumate al Programa de Creadores de Sommy. Recomendá nuestros productos en tus redes con tu link y tu QR propios: cuando alguien compra, cobrás comisión. Sin stock, sin inversión.')
@section('contentEcommerce')

<style>
    .rvp { font-family: 'Poppins', sans-serif; color: #1B2B5A; }

    .rvp-hero {
        background: linear-gradient(135deg, #131C36 0%, #1B2B5A 55%, #223a75 100%);
        color: #fff; padding: 64px 20px 88px; text-align: center; position: relative; overflow: hidden;
    }
    .rvp-hero .kicker { font-size: 12px; letter-spacing: .22em; text-transform: uppercase; color: #C6A15B; font-weight: 500; }
    .rvp-hero h1 { font-size: clamp(28px, 5vw, 46px); font-weight: 600; margin: 14px 0 16px; line-height: 1.18; color: #fff; }
    .rvp-hero p { font-size: clamp(14.5px, 2vw, 16.5px); font-weight: 300; color: #D3DAEC; max-width: 560px; margin: 0 auto 28px; line-height: 1.65; }
    .rvp-hero .cta {
        display: inline-flex; align-items: center; gap: 10px; background: #1EBE5A; color: #fff; border-radius: 999px;
        padding: 15px 38px; font-size: 15px; font-weight: 600; text-decoration: none;
        box-shadow: 0 16px 40px rgba(0,0,0,.28); transition: transform .25s ease;
    }
    .rvp-hero .cta:hover { transform: translateY(-2px); color: #fff; }

    .rvp-pasos { max-width: 1080px; margin: -46px auto 0; padding: 0 20px; position: relative; z-index: 3; }
    .rvp-pasos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
    .rvp-paso {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 20px; padding: 24px 22px;
        box-shadow: 0 20px 50px rgba(27,43,90,.10);
    }
    .rvp-paso .n {
        width: 36px; height: 36px; border-radius: 50%; background: #1B2B5A; color: #fff;
        display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 15px; margin-bottom: 12px;
    }
    .rvp-paso h3 { font-size: 16px; font-weight: 600; margin-bottom: 7px; }
    .rvp-paso p { font-size: 13.5px; font-weight: 300; color: #5D6884; line-height: 1.6; margin: 0; }
    .rvp-mini-wsp { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 13px; font-weight: 600; color: #1EBE5A; text-decoration: none; }
    .rvp-mini-wsp:hover { text-decoration: underline; color: #1EBE5A; }

    .rvp-seccion { max-width: 1080px; margin: 60px auto 0; padding: 0 20px; }
    .rvp-h2 { font-size: clamp(22px, 3.4vw, 30px); font-weight: 600; text-align: center; margin-bottom: 30px; }

    .rvp-dos-col { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; max-width: 900px; margin: 0 auto; }
    .rvp-col-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 20px; padding: 26px 24px; box-shadow: 0 14px 34px rgba(27,43,90,.06); }
    .rvp-col-card h3 { font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9AA5BD; margin-bottom: 12px; }
    .rvp-col-card .rvp-check-item:first-of-type { padding-top: 0; }
    .rvp-col-card .rvp-nota-autentico { text-align: left; font-size: 12.5px; color: #9AA5BD; font-weight: 300; margin-top: 12px; }

    .rvp-check-item { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid #EDF0F7; }
    .rvp-check-item:last-of-type { border-bottom: none; }
    .rvp-check-item i { color: #1EBE5A; font-size: 16px; margin-top: 2px; }
    .rvp-check-item span { font-size: 14px; color: #1B2B5A; font-weight: 500; }

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
    .rvp-campo input[type="file"] { padding: 10px 14px; }
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

    .rvp-declaracion { background: #F8FAFC; border: 1px solid #E7EAF2; border-radius: 14px; padding: 16px 18px; margin-top: 4px; }
    .rvp-declaracion label { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; font-weight: 400; color: #47536F; line-height: 1.6; cursor: pointer; }
    .rvp-declaracion input[type="checkbox"] { width: auto; margin-top: 3px; flex-shrink: 0; }
    .rvp-declaracion input[type="checkbox"]:disabled + span { color: #B7C0D4; }

    .rvp-terminos-box { max-height: 220px; overflow-y: auto; border: 1px solid #E1E6F0; border-radius: 14px; padding: 16px 18px; background: #F8FAFC; font-size: 12.5px; color: #47536F; line-height: 1.7; }
    .rvp-terminos-box h4 { font-size: 12.5px; font-weight: 700; color: #1B2B5A; margin: 14px 0 4px; }
    .rvp-terminos-box h4:first-child { margin-top: 0; }
    .rvp-terminos-box p { margin: 0; }
    .rvp-terminos-estado { display: flex; align-items: center; gap: 7px; font-size: 12.5px; margin-top: 8px; color: #b4552d; font-weight: 500; }
    .rvp-terminos-estado.ok { color: #0d8a4f; }

    .rvp-firma-wrap { margin-top: 4px; opacity: .45; transition: opacity .25s; }
    .rvp-firma-wrap.activo { opacity: 1; }
    .rvp-firma-canvas { width: 100%; height: 170px; border: 1.5px dashed #C7CFE0; border-radius: 14px; background: #fff; touch-action: none; display: block; cursor: crosshair; }
    .rvp-firma-wrap:not(.activo) .rvp-firma-canvas { pointer-events: none; }
    .rvp-firma-acciones { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; gap: 10px; flex-wrap: wrap; }
    .rvp-firma-limpiar { border: none; background: none; color: #2563EB; font-size: 12.5px; font-weight: 600; cursor: pointer; padding: 0; font-family: inherit; }

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
        .rvp-hero { padding: 48px 20px 76px; }
        .rvp-dos-col { grid-template-columns: 1fr; }
    }
</style>

@php
    $wspNumero = preg_replace('/\D/', '', $arrayEmpresa['whatsapp'] ?? '');
    $wspTexto = urlencode('Hola! Quiero sumarme al Programa de Creadores de Sommy 🙌');
    $wspLink = $wspNumero ? "https://wa.me/{$wspNumero}?text={$wspTexto}" : '#sumarme';
@endphp

<div class="rvp">

    <section class="rvp-hero">
        <div class="kicker">Programa de Creadores</div>
        <h1>Creá contenido y ganá con Sommy</h1>
        <p>
            Recomendá nuestros colchones y sommiers en tus redes, de forma auténtica,
            y cobrá comisión por cada venta. Sin stock, sin inversión.
        </p>
        <a href="#sumarme" class="cta">
            <i class="fas fa-pen-nib"></i> Quiero ser creador
        </a>
    </section>

    <section class="rvp-pasos">
        <div class="rvp-pasos-grid">
            <div class="rvp-paso">
                <div class="n">1</div>
                <h3>Nos escribís</h3>
                <p>Contanos quién sos y por dónde pensás compartir. Te confirmamos rápido.</p>
                <a href="{{ $wspLink }}" target="_blank" rel="noopener noreferrer" class="rvp-mini-wsp"><i class="fab fa-whatsapp"></i> Escribinos</a>
            </div>
            <div class="rvp-paso">
                <div class="n">2</div>
                <h3>Cargás tus datos</h3>
                <p>Un formulario corto con tu DNI. Al toque te generamos tu link y tu QR propios.</p>
            </div>
            <div class="rvp-paso">
                <div class="n">3</div>
                <h3>Compartís contenido</h3>
                <p>Mostrás o recomendás productos Sommy a tu manera en tus redes, con tu link.</p>
            </div>
            <div class="rvp-paso">
                <div class="n">4</div>
                <h3>Cobrás tu comisión</h3>
                <p>Cada venta que entra por tu link queda a tu nombre. Nosotros liquidamos, vos no reclamás nada.</p>
            </div>
        </div>
    </section>

    <section class="rvp-seccion">
        <h2 class="rvp-h2">Lo que necesitás y lo que ganás</h2>
        <div class="rvp-dos-col">
            <div class="rvp-col-card">
                <h3>Necesitás</h3>
                <div class="rvp-check-item"><i class="fas fa-check-circle"></i><span>Una red social activa (Instagram, TikTok, X o YouTube)</span></div>
                <div class="rvp-check-item"><i class="fas fa-check-circle"></i><span>Perfil público</span></div>
                <div class="rvp-check-item"><i class="fas fa-check-circle"></i><span>Ganas de crear contenido</span></div>
                <p class="rvp-nota-autentico">No hace falta ser influencer. Buscamos autenticidad.</p>
            </div>
            <div class="rvp-col-card">
                <h3>Ganás</h3>
                <div class="rvp-check-item"><i class="fas fa-check-circle"></i><span>Cero inversión: no comprás ni pagás nada para entrar</span></div>
                <div class="rvp-check-item"><i class="fas fa-check-circle"></i><span>Precio de fábrica, con garantía, para tu cliente</span></div>
                <div class="rvp-check-item"><i class="fas fa-check-circle"></i><span>Nosotros hacemos el envío, la facturación y la posventa</span></div>
                <div class="rvp-check-item"><i class="fas fa-check-circle"></i><span>Tu link y tu QR propios para compartir donde quieras</span></div>
            </div>
        </div>
    </section>

    <div class="rvp-form-wrap" id="sumarme">
        <div class="rvp-form-card">
            <h2>Generá tu link y tu QR</h2>
            <p class="sub">Ya te confirmamos por WhatsApp o querés empezar de una — completá tus datos y en el mismo momento te generamos tu link y tu QR. Tu cuenta queda a revisión hasta que la validemos.</p>

            @if($errors->any())
                <div class="rvp-error">
                    <strong>Revisá estos datos:</strong>
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @if(session('error_recuperar'))
                <div class="rvp-error">{{ session('error_recuperar') }}</div>
            @endif

            <form method="POST" action="{{ route('revendedores.store') }}" enctype="multipart/form-data">
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
                    <label for="rv-vende">¿Dónde pensás crear/compartir contenido?</label>
                    <textarea id="rv-vende" name="como_vende" rows="3" placeholder="Instagram, TikTok, YouTube, mi grupo de conocidos...">{{ old('como_vende') }}</textarea>
                </div>

                <div class="rvp-campo">
                    <label for="rv-dnifoto">Foto de tu DNI (frente) *</label>
                    <input type="file" id="rv-dnifoto" name="dni_foto" accept="image/*" required>
                    <div class="hint">La usamos solo para validar tu identidad antes de aprobarte. No se publica en ningún lado.</div>
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

                <div class="rvp-campo">
                    <label>Términos y condiciones del Programa de Creadores *</label>
                    <div class="rvp-terminos-box" id="rv-terminos-box" tabindex="0">
                        <h4>1. Qué es esto</h4>
                        <p>El Programa de Creadores de Sommy te da un link y un QR propios para recomendar nuestros productos en tus redes. Cuando alguien compra entrando por tu link, cobrás una comisión. No es un empleo: es una colaboración comercial independiente.</p>

                        <h4>2. No hay relación de dependencia</h4>
                        <p>Participás como <strong>creador/vendedor independiente</strong>, no como empleado de Sommy. No hay horario que cumplir, no hay exclusividad, no hay obligación de generar contenido con una frecuencia mínima y podés dejar de participar cuando quieras, sin ningún tipo de indemnización ni preaviso. Vos decidís cuánto, cómo y cuándo compartir tu link.</p>

                        <h4>3. Cómo se paga</h4>
                        <p>Cobrás únicamente <strong>comisión sobre ventas confirmadas</strong> atribuidas a tu link o QR (por defecto {{ $comisionBase }}% sobre el valor de los productos vendidos). No hay sueldo, viático ni ningún otro pago fijo. La comisión se liquida una vez que el pedido fue entregado y cobrado, transferida a la cuenta que nos dejaste en este formulario.</p>

                        <h4>4. Atribución de ventas</h4>
                        <p>Tu link/QR deja una marca (cookie) en el navegador de quien lo abre, válida por 30 días. Si esa persona compra dentro de ese plazo, la venta queda a tu nombre automáticamente. Si el pedido se cancela, se devuelve o se anula, la comisión correspondiente tampoco se liquida.</p>

                        <h4>5. Aprobación de la cuenta</h4>
                        <p>Tu link y tu QR se generan apenas te registrás, pero tu cuenta queda <strong>pendiente de revisión</strong>. Recién empieza a atribuir ventas cuando Sommy valida tus datos y tu DNI y aprueba tu cuenta. Sommy puede rechazar o suspender una cuenta en cualquier momento, por ejemplo ante datos falsos, contenido engañoso o incumplimiento de estos términos.</p>

                        <h4>6. Tu DNI y tus datos</h4>
                        <p>Pedimos una foto de tu DNI únicamente para verificar tu identidad antes de aprobarte y poder transferirte tu comisión sin problemas. No se publica ni se comparte con terceros, y se usa conforme a la Ley 25.326 de Protección de Datos Personales.</p>

                        <h4>7. Cómo tenés que promocionar</h4>
                        <p>El contenido que hagas tiene que ser auténtico y honesto: mostrar el producto real, sin inventar precios, promociones, plazos de entrega o características que Sommy no ofrece. No podés usar spam, mensajes masivos no solicitados ni publicidad paga con la marca Sommy sin autorización previa por escrito.</p>

                        <h4>8. Propiedad intelectual</h4>
                        <p>El contenido que crees mencionando a Sommy podés seguir usándolo en tus redes; Sommy puede resubir o compartir ese contenido dando crédito a su autor, salvo que nos pidas expresamente que no lo hagamos.</p>

                        <h4>9. Sin garantías de ingresos</h4>
                        <p>Sommy no garantiza un monto mínimo de ventas ni de ingresos. Lo que cobrás depende exclusivamente de las ventas reales atribuidas a tu link durante tu participación en el programa.</p>

                        <h4>10. Aceptación</h4>
                        <p>Al completar este formulario y firmar en el recuadro de abajo, declarás que leíste y aceptás estos términos en su totalidad, junto con los <a href="{{ url('/terminos') }}" target="_blank" style="color:#2563EB;">términos y condiciones generales</a> del sitio.</p>
                    </div>
                    <div class="rvp-terminos-estado" id="rv-terminos-estado">
                        <i class="fas fa-arrow-down"></i> Desplazate hasta el final para poder firmar
                    </div>
                </div>

                <div class="rvp-declaracion">
                    <label for="rv-declaracion">
                        <input type="checkbox" id="rv-declaracion" name="acepta_independiente" value="1" required disabled>
                        <span>
                            Declaro que <strong>no soy empleado de Sommy</strong>: actúo como
                            <strong>vendedor/creador independiente que trabaja por comisión online</strong>,
                            sin relación de dependencia, sin horario fijo ni obligación de exclusividad, y que leí
                            los términos y condiciones completos de arriba.
                        </span>
                    </label>
                </div>

                <div class="rvp-campo rvp-firma-wrap" id="rv-firma-wrap">
                    <label>Tu firma *</label>
                    <canvas id="rv-firma-canvas" class="rvp-firma-canvas" width="600" height="170"></canvas>
                    <div class="rvp-firma-acciones">
                        <span class="hint" style="margin:0;">Dibujá tu firma con el mouse o el dedo.</span>
                        <button type="button" class="rvp-firma-limpiar" id="rv-firma-limpiar">Borrar y firmar de nuevo</button>
                    </div>
                </div>
                <input type="hidden" name="firma" id="rv-firma-input">
                <input type="hidden" name="terminos_leidos" id="rv-terminos-input" value="">

                <button type="submit" class="rvp-submit">Generar mi link y mi QR</button>
                <p class="rvp-legal">
                    Al registrarte aceptás nuestros <a href="{{ url('/terminos') }}" style="color:#5D6884;text-decoration:underline;">términos y condiciones</a>.
                </p>
            </form>
        </div>

        <div class="rvp-recuperar">
            <details>
                <summary>Ya soy creador y perdí mi link</summary>
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
            <p>Cobrás un porcentaje sobre el valor de los productos de cada compra confirmada. El porcentaje base es del {{ $comisionBase }}% y te lo confirmamos por WhatsApp junto con tu link; si generás volumen, lo revisamos y lo subimos.</p>
        </details>

        <details>
            <summary>¿Tengo que comprar mercadería o tener stock?</summary>
            <p>No. No comprás nada ni guardás productos. Vos recomendás, la compra se hace en nuestra tienda y nosotros entregamos.</p>
        </details>

        <details>
            <summary>¿Por qué me piden mi DNI?</summary>
            <p>Para validar que sos una persona real antes de aprobarte y poder pagarte tu comisión sin problemas. Es solo para nosotros, no se publica.</p>
        </details>

        <details>
            <summary>¿Ya puedo empezar a vender apenas me registro?</summary>
            <p>Tu link y tu QR se generan al toque, pero quedan <strong>a revisión</strong> hasta que validemos tu perfil y tu DNI. Recién cuando te aprobamos tu link empieza a sumar ventas — te avisamos por WhatsApp apenas quede activo.</p>
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

        <details>
            <summary>¿Soy empleado de Sommy?</summary>
            <p>No. Sos un creador/vendedor independiente que trabaja por comisión, sin relación de dependencia, sin horario ni exclusividad. Vos decidís cuánto y cuándo compartir tu link.</p>
        </details>
    </section>

</div>

<script>
(function () {
    var canvas = document.getElementById('rv-firma-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    ctx.strokeStyle = '#1B2B5A';
    ctx.lineWidth = 2.2;
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';

    var wrap = document.getElementById('rv-firma-wrap');
    var drawing = false;
    var hasDrawn = false;

    function activo() { return wrap.classList.contains('activo'); }

    function pos(e) {
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width;
        var scaleY = canvas.height / rect.height;
        var t = e.touches && e.touches[0];
        var clientX = t ? t.clientX : e.clientX;
        var clientY = t ? t.clientY : e.clientY;
        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
    }
    function start(e) {
        if (!activo()) return;
        drawing = true;
        var p = pos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        e.preventDefault();
    }
    function move(e) {
        if (!drawing) return;
        var p = pos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        hasDrawn = true;
        e.preventDefault();
    }
    function end() { drawing = false; }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    window.addEventListener('mouseup', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end);

    document.getElementById('rv-firma-limpiar').addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
    });

    // Gate: hay que llegar al final de los términos para poder marcar la declaración,
    // y hay que marcar la declaración para que se habilite el recuadro de firma.
    var box = document.getElementById('rv-terminos-box');
    var estado = document.getElementById('rv-terminos-estado');
    var terminosInput = document.getElementById('rv-terminos-input');
    var checkbox = document.getElementById('rv-declaracion');

    function marcarLeido() {
        terminosInput.value = '1';
        checkbox.disabled = false;
        estado.classList.add('ok');
        estado.innerHTML = '<i class="fas fa-check-circle"></i> Leíste los términos completos';
    }
    function chequearFin() {
        if (box.scrollTop + box.clientHeight >= box.scrollHeight - 6) marcarLeido();
    }
    box.addEventListener('scroll', chequearFin);
    // Si el texto ya entra completo sin necesidad de scrollear (pantallas muy altas), no lo bloqueamos.
    window.addEventListener('load', chequearFin);
    chequearFin();

    checkbox.addEventListener('change', function () {
        if (checkbox.checked) {
            wrap.classList.add('activo');
        } else {
            wrap.classList.remove('activo');
        }
    });

    var form = checkbox.closest('form');
    form.addEventListener('submit', function (e) {
        if (terminosInput.value !== '1') {
            e.preventDefault();
            alert('Tenés que leer los términos y condiciones completos antes de firmar.');
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        if (!checkbox.checked) {
            e.preventDefault();
            alert('Tenés que aceptar la declaración de vendedor independiente.');
            return;
        }
        if (!hasDrawn) {
            e.preventDefault();
            alert('Tenés que firmar en el recuadro para poder registrarte.');
            canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        document.getElementById('rv-firma-input').value = canvas.toDataURL('image/png');
    });
})();
</script>

@endsection
