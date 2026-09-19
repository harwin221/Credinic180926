@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    Desembolsos (creados por el usuario actual)
@endsection
@section('content')
        <div class="row">
            <div class="col-md-12">
                <a href="{{route('agentes.user.desembolso.create')}}" class="btn btn-sm btn-primary">Nuevo Desembolso<i class="fa fa-plus"></i></a>
            </div>
        </div>
    <br>
    <div class="col-sm-12 col-md-6">
        {{html()->form('GET',route('agentes.user.desembolso.index'))->open()}}
        <div class="input-group mb-3">
            <input type="text" name="buscar" value="{{request('buscar')}}" class="form-control" placeholder="Buscar Desembolso" aria-label="Recipient's username" aria-describedby="button-addon2">
            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Buscar</button>
        </div>
        {{html()->form()->close()}}
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-stripped table-sm" style="font-size: 14px">
                    <thead class="table-info">
                    <tr>
                        <th>N° Préstamo</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Fecha Creado</th>
                        <th>Fecha del Desembolso</th>
                        <th>Desembolsado</th>
                        <th>Estado</th>
                        <th>Entregado</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($prestamos as $prestamo)
                        <tr>
                            <td>
                                {{$prestamo->consecutivo}}
                                @if($prestamo->represtamo)
                                    <a href="javascript:void(0)" class="btn btn-sm btn-info"><i class="fa fa-r text-white" title="Represtamo"></i></a>
                                @endif
                            </td>
                            <td>{{$prestamo->forma_pago}}</td>
                            <td>{{$prestamo->cliente->full_name}}</td>
                            <td>{{$prestamo->moneda}} {{number_format($prestamo->monto_prestamo,2)}}</td>
                            <td>{{fecha_d_m_Y_h_i($prestamo->created_at)}}</td>
                            <td>{{fecha_d_m_Y($prestamo->fecha_desembolso)}}</td>
                            <td>{{$prestamo->userDesembolso->full_name}}</td>
                            <td><span class="badge @if($prestamo->estado==1) bg-success @elseif($prestamo->estado == 4) bg-danger @else bg-primary @endif">
                                    {{$prestamo->estado_prestamo}}
                                </span>
                            </td>
                            <td>
                                <span>@if($prestamo->desembolsado==1)
                                        <i class="fa fa-check" style="color: green"></i>
                                    @else
                                        <i class="fa fa-times" style="color: red"></i>
                                    @endif</span>
                            </td>
                            <td>
                                    <a href="{{route('agentes.user.desembolso.show',$prestamo->id_enc)}}" class="w-100 mb-1 btn btn-sm btn-primary">Ver</a>
{{--                                    @if($prestamo->estado!=4)--}}
{{--                                        <a href="#" class="btn btn-sm btn-danger btnEliminar w-100" data-id="{{$prestamo->id_enc}}">Anular</i> </a>--}}
{{--                                    @endif--}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$prestamos->links()}}
            </div>
        </div>
    </div>
        {{html()->form('POST')->id('frmEliminarPrestamo')->open()}}
        @method('DELETE')
        {{html()->form()->close()}}

@endsection

@section('script')
    <script>
        document.querySelectorAll('.btnEliminar').forEach((button)=>{
            button.addEventListener('click',function (event){

                let id = event.target.closest('a', '.btnEditar').dataset.id

                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea anular el desembolso? El desembolso no se puede eliminar si ya tiene registro de abonos",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Anular'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = '{{route('agentes.user.desembolso.destroy','?')}}'
                        url=url.replace('?',id)

                        document.getElementById('frmEliminarPrestamo').action = url
                        document.getElementById('frmEliminarPrestamo').submit();
                    }
                })
            })
        })
    </script>
@endsection
