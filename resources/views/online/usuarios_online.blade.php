@extends('layouts.app')
@section('tituloPagina')
    Usuarios Online
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Tipo</th>
                    </tr>
                    @foreach($users as $user)
                        @if($user->isOnline())
                            <tr>
                                <td>{{$loop->index+1}}</td>
                                <td>{{$user->nombres}}</td>
                                <td>{{$user->apellidos}}</td>
                                <td>{{$user->tipo_usuario_actual}}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection
