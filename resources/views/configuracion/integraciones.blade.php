@extends('layouts.admin')

@section('title', 'Integraciones')

@section('contenido')
<style>
    .int-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 980px; margin: 0 auto; }
    .int-title { font-size: 21px; font-weight: 600; margin-bottom: 2px; }
    .int-sub { font-size: 13.5px; color: #6E7A96; font-weight: 300; margin-bottom: 20px; }

    .int-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.08); padding: 20px; margin-bottom: 16px;
    }
    .int-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 4px; }
    .int-head h3 { font-size: 15px; font-weight: 600; margin: 0; }
    .int-head h3 i { color: #2563EB; width: 22px; }
    .int-desc { font-size: 12.5px; color: #6E7A96; font-weight: 300; margin-bottom: 14px; }
    .int-estado { border-radius: 999px; font-size: 11px; font-weight: 600; padding: 4px 14px; }
    .int-estado.ok { background: #DCFCE7; color: #166534; }
    .int-estado.falta { background: #FEF3C7; color: #92400E; }

    .int-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
    @media (max-width: 767px) { .int-grid { grid-template-columns: 1fr; } }
    .int-campo label { font-size: 12.5px; font-weight: 500; display: flex; align-items: center; gap: 6px; margin-bottom: 3px; }
    .int-campo input {
        width: 100%; border: 1px solid #E7EAF2; border-radius: 10px; padding: 9px 12px;
        font-size: 13px; color: #1B2B5A; font-family: 'Poppins', monospace;
    }
    .int-campo input:focus { border-color: #2563EB; outline: none; }
    .int-campo .ayuda { font-size: 10.5px; color: #94A3B8; font-weight: 300; margin-top: 2px; }
    .int-dot { width: 8px; height: 8px; border-radius: 999px; display: inline-block; }
    .int-dot.ok { background: #16a34a; }
    .int-dot.falta { background: #E7EAF2; }

    .int-guardar {
        border: none; border-radius: 999px; padding: 12px 30px; font-size: 14px; font-weight: 600;
        cursor: pointer; background: #1B2B5A; color: #fff;
    }
    .int-guardar:hover { background: #2563EB; }
    .int-nota { font-size: 11.5px; color: #6E7A96; font-weight: 300; }
</style>

<div class="int-wrap">
    <div class="int-title"><i class="fas fa-plug" style="color:#2563EB;"></i> Integraciones</div>
    <div class="int-sub">Cargá acá todas las claves para que el software funcione completo. Los campos con candado son secretos: se muestran solo los últimos 4 caracteres, y dejarlos como están conserva la clave actual.</div>

    @if(session('int_ok'))
        <div class="alert alert-success" style="border-radius:12px;">{{ session('int_ok') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="border-radius:12px;">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('integraciones.guardar') }}">
        @csrf

        @foreach($grupos as $g)
        <div class="int-card">
            <div class="int-head">
                <h3><i class="fas {{ $g['icono'] }}"></i> {{ $g['titulo'] }}</h3>
                <span class="int-estado {{ $g['estado'] ? 'ok' : 'falta' }}">
                    {{ $g['estado'] ? '✓ Configurada' : 'Pendiente' }}
                </span>
            </div>
            <div class="int-desc">{{ $g['descripcion'] }}</div>
            <div class="int-grid">
                @foreach($g['campos'] as $c)
                <div class="int-campo">
                    <label>
                        <span class="int-dot {{ $c['cargada'] ? 'ok' : 'falta' }}"></span>
                        {{ $c['label'] }}
                        @if($c['secreto'])<i class="fas fa-lock" style="font-size:10px;color:#94A3B8;"></i>@endif
                    </label>
                    <input type="text" name="claves[{{ $c['env'] }}]"
                           value="{{ $c['valor'] }}"
                           placeholder="{{ $c['secreto'] && !$c['cargada'] ? 'Pegá la clave acá' : '' }}"
                           autocomplete="off" spellcheck="false">
                    @if($c['ayuda'])<div class="ayuda">{{ $c['ayuda'] }}</div>@endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:30px;">
            <span class="int-nota"><i class="fas fa-shield-alt"></i> Antes de cada guardado se hace una copia de seguridad (.env.bak). Los cambios se activan al instante.</span>
            <button type="submit" class="int-guardar"><i class="fas fa-save"></i> Guardar integraciones</button>
        </div>
    </form>
</div>
@endsection
