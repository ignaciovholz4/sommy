@extends('layouts.admin')
@section('contenido')
<style>
    .permiso-grupo { border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
    .permiso-grupo-header { background: #f8fafc; padding: 10px 16px; font-weight: 700; text-transform: uppercase; font-size: .72rem; letter-spacing: .5px; color: #334155; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    .permiso-grupo-header.sensible { background: #FEF2F2; color: #B91C1C; }
    .permiso-fila { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; border-top: 1px solid #f1f5f9; font-size: .84rem; }
    .permiso-fila .nombre { color: #334155; }
    .permiso-opciones { display: flex; gap: 12px; font-size: .78rem; white-space: nowrap; }
    .sucursales-check { display: flex; flex-wrap: wrap; gap: 10px; }
    .sucursales-check label { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; font-size: .85rem; cursor: pointer; }
</style>
<div class="container"><br>
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header"><h5>Editar Usuario</h5></div>

                <div class="card-body">
                    @include('custom.message')
                    <form action="{{route('user.update',$user->id)}}" method="post">
                    @csrf
                    @method('put')
                    <div class="container">
                        <div class="input-group mb-3">
                            <div class="input-group-append" >
                                <div class="input-group-text style-icon-fas" style="background-color:black">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control " name="name" id="name" value="{{old('name',$user->name)}}" placeholder="Nombre">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <div class="input-group-text style-icon-fas">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control "  name="email" id="email" value="{{old('email',$user->email)}}" placeholder="slug">
                        </div>
                        <div class="input-group mb-4">
                            <div class="input-group-append">
                                <div class="input-group-text style-icon-fas">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                            </div>
                            <select name="roles" id="roles" class="form-control ">
                            <option value="selecciona">Seleciona un rol</option>
                            @foreach($roles as $role)
                                <option value="{{$role->id}}"
                                    @isset($user->roles[0]->name)
                                        @if($role->name == $user->roles[0]->name)
                                           selected
                                        @endif
                                    @endisset
                                >{{$role->name}}</option>
                            @endforeach
                            </select>
                        </div>

                        {{-- Sucursales explícitas: sin ninguna tildada, el usuario ve todas las sucursales --}}
                        <div class="mb-4">
                            <label class="fw-bold mb-2"><i class="fas fa-store me-2"></i>Sucursales permitidas</label>
                            <p class="text-muted small mb-2">Sin marcar ninguna, esta persona ve datos de <strong>todas</strong> las sucursales. Marcá una o más para restringirla a solo esas.</p>
                            <div class="sucursales-check">
                                @foreach($sucursales as $suc)
                                <label>
                                    <input type="checkbox" name="sucursales[]" value="{{ $suc->id }}"
                                        {{ in_array($suc->id, $sucursalesAsignadas) ? 'checked' : '' }}>
                                    {{ $suc->nombre }}
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Permisos por persona: excepciones puntuales sobre lo que ya da el rol --}}
                        <div class="mb-3">
                            <label class="fw-bold mb-2"><i class="fas fa-user-shield me-2"></i>Permisos individuales</label>
                            <p class="text-muted small mb-2">"Por rol" es lo que ya le da el rol asignado arriba. "Otorgado" y "Denegado" son excepciones puntuales para esta persona — un "Denegado" manda siempre, aunque el rol tenga acceso total.</p>

                            @foreach($permisosPorModulo as $modulo => $permisos)
                            <div class="permiso-grupo">
                                <div class="permiso-grupo-header {{ in_array($modulo, $modulosSensibles) ? 'sensible' : '' }}" data-bs-toggle="collapse" data-bs-target="#grupo-{{ $modulo }}">
                                    <span>{{ ucfirst($modulo) }} @if(in_array($modulo, $modulosSensibles)) <i class="fas fa-exclamation-triangle ms-1" title="Módulo sensible"></i> @endif</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="collapse" id="grupo-{{ $modulo }}">
                                    @foreach($permisos as $permiso)
                                    @php $actual = $overridesActuales[$permiso->id] ?? 'rol'; @endphp
                                    <div class="permiso-fila">
                                        <span class="nombre">{{ $permiso->name }}</span>
                                        <span class="permiso-opciones">
                                            <label><input type="radio" name="permiso[{{ $permiso->id }}]" value="rol" {{ $actual === 'rol' ? 'checked' : '' }}> Por rol</label>
                                            <label class="text-success"><input type="radio" name="permiso[{{ $permiso->id }}]" value="otorgado" {{ $actual === 'otorgado' ? 'checked' : '' }}> Otorgado</label>
                                            <label class="text-danger"><input type="radio" name="permiso[{{ $permiso->id }}]" value="denegado" {{ $actual === 'denegado' ? 'checked' : '' }}> Denegado</label>
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                        <button type="submit" class="btn btn6 mx-2"><i class="fas fa-check-circle text-success mr-2"></i>Actualizar</button>
                        <a class="btn btn5" href="{{route('user.index')}}"><i class="fas fa-window-close mr-2 "></i>Regresar</a>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>
                    </form>
                    {{-- {!! dd(old()) !!} --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
