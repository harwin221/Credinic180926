@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('roles.index')])
    Asignación Permisos a Rol | <span style="color: blue">{{$rolObj->name}}</span>
@endsection

@section('content')
    {{html()->form('POST',route('permisos.asignarPermisosRol',encode($rolObj->id)))->open()}}
    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-sm btn-success float-end mr-2">Guardar <i class="fa fa-save"></i></button>
        </div>
    </div>
    <hr>
    <div class="row">
        @foreach($permisos as $ind=> $categoria)
            <div class="col-md-4">
                <div class="card" style="background-color: #e9ecef; border: 1px solid #ced4da;">
                    <div class="card-header" style="background: #d1d5db; color: #000; font-weight: bold;">
                        {{$ind}}
                        <div class="icheck-primary d-inline float-end">
                            <input id="{{$ind}}" data-type="chkSelAll" type="checkbox">
                            <label for="{{$ind}}"></label>
                        </div>
                    </div>
                    <div class="card-body" style="background-color: #f3f4f6;">
                        <ul class="list-group list-unstyled mt-2">
                            @foreach($categoria as $permiso)
                                <li>
                                    {{$permiso->name}}
                                    <div class="icheck-primary d-inline float-end">
                                        <input name="permiso[{{encode($permiso->id)}}]" data-id="{{$ind}}" type="checkbox" value="{{encode($permiso->id)}}" id="permiso{{encode($permiso->id)}}" @if($rolObj->hasPermissionTo($permiso->name)) checked @endif>
                                        <label for="permiso{{\Hashids::encode($permiso->id)}}"></label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
    @endforeach
    {{html()->form()->close()}}
@endsection

@section('script')
                <script>

                    document.querySelectorAll('[data-type="chkSelAll"]').forEach(element => {
                        element.addEventListener('change', function (el) {
                            document.querySelectorAll('[data-id="' + el.target.id + '"]').forEach(chk => {
                                chk.checked = el.target.checked
                            })
                        })
                    })
                </script>
@endsection
