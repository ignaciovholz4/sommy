@extends('layouts.admin')
@section('contenido')

<form action="{{route('role.update',$role->id)}}" method="post" autocomplete="off">
@csrf
@method('put')
<section class="margin">
    <div class="card">
        <div class="card-header">
            <strong>Editar el rol</strong> 
        </div>
        <div class="card-body">
            @include('custom.message')
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Nombre del rol</label>
                            <input type="text" class="form-control" name="name" id="name" value="{{old('name',$role->name)}}" placeholder="Nombre">
                        </div>
                    </div>
                    <div class="col-md-3" style="display: none">
                        <div class="form-group">
                            <label for="">Nombre del slug</label>
                            <input type="text" class="form-control"  name="slug" id="slug" value="{{old('slug',$role->slug)}}" placeholder="slug">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Descripcion del rol</label>
                            <textarea class="form-control" name="description"  id="description" rows="1" placeholder="Descripción">{{old('description',$role->description)}}</textarea>    
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 text-center">
                    <h4>Acceso completo</h4>
                    <small class="form-text text-muted">
                    Si usted checked Yes no es necesario checked la lista de permisos
                    </small>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="fullaccessyes" name="full-access" class="custom-control-input" value="yes"
                        @if($role['full-access'] == "yes")
                            checked
                        
                        @elseif(old('full-access') == "yes")
                            checked
                        @endif
                        >
                        <label class="custom-control-label" for="fullaccessyes">Yes</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="fullaccessno" name="full-access" class="custom-control-input" value="no"
                            @if($role['full-access'] == "no")
                            checked
                        
                        @elseif(old('full-access') == "no")
                            checked
                        @endif
                        >
                        <label class="custom-control-label" for="fullaccessno">No</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@php
    $modulosPorFila = $permisosPorModulo->chunk(3);
@endphp
@foreach($modulosPorFila as $fila)
<section style="margin-top:-1%;">
    <div class="card-group">
        @foreach($fila as $modulo => $permisos)
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Módulo {{ ucfirst($modulo) }}</h5>
                @foreach($permisos as $permission)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="permission_{{$permission->id}}" value="{{$permission->id}}" name="permission[]"
                    @if(is_array(old('permission')) && in_array("$permission->id", old('permission')))
                        checked
                    @elseif(is_array($permission_role) && in_array("$permission->id", $permission_role))
                        checked
                    @endif
                    >
                    <label class="custom-control-label" for="permission_{{$permission->id}}">{{$permission->name}} <em> ( {{$permission->description}} )</em></label>
                </div>
                @endforeach
            </div>
            <div class="card-footer">
            <small class="text-muted">{{ $permisos->count() }} permiso(s)</small>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endforeach
<section class="margin">
    <div class="container margin">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-12">
                        <!--input type="submit" class="btn btn-primary" value="Guardar"-->
                        <button type="submit" class="btn btn6 mr-3"><i class="fas fa-check-circle text-success mr-2"></i>Guardar</button>
                        <a class="btn btn5" href="{{route('role.index')}}"><i class="fas fa-window-close mr-2 "></i>Regresar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</form>


<style>
.margin{
    margin-top: 5px;
}
</style>
<script>
    const slug = document.querySelector("#slug");
    const nameRol = document.querySelector("#name");
    nameRol.addEventListener("keyup", (event) => {
        //e.preventDefault;
        //console.log(event.target.value)
        slug.value = event.target.value;
    });
</script>
@endsection
