@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('home')])
    Arqueos
@endsection
@section('content')
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{route('reportes.arqueo.nuevoArqueo')}}" class="btn btn-sm btn-primary">Nuevo Arqueo <i class="fa fa-plus"></i></a>
        </div>
    </div>
    <br>
    {{html()->form('GET',route('reportes.arqueo.list'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="fecha"><strong>Fecha Arqueo:</strong></label>
                <input type="date" id="fecha" name="fecha" value="{{$fecha}}" class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="cobrador"><strong>Cobrador:</strong></label>
                {{html()->select('cobrador',[0=>'Todos']+$listaCobradores,request('cobrador'))->class('form-control select2')->style(['width'=>'100%'])->id('cobrador')}}
            </div>
        </div>
        <div class="col-md-3 align-self-end">
             <button type="submit" class="btn btn-primary w-100">Generar Lista <i class="fa fa-cog"></i></button>
        </div>
    </div>
    {{html()->form()->close()}}
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead class="table-info">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Cobrador</th>
                        <th>Creado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($arqueos as $arq)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{fecha_d_m_Y($arq->fecha_arqueo)}}</td>
                            <td>{{$arq->cobrador->username}}</td>
                            <td>{{$arq->created_user->username}}</td>
                            <td class="text-center">
                                <a href="{{route('reportes.arqueo.index',$arq->id_enc)}}" class="btn btn-sm btn-primary" title="Ver Arqueo">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger btnEliminarArqueo"
                                    data-target="{{ route('reportes.arqueo.eliminar', $arq->id_enc) }}"
                                    title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>

    {{-- Formulario oculto para DELETE --}}
    {{ html()->form('post')->id('frmEliminarArqueo')->open() }}
    @method('DELETE')
    {{ html()->form()->close() }}
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.querySelectorAll('.btnEliminarArqueo').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.dataset.target;
            Swal.fire({
                title: '¿Eliminar arqueo?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#3085d6',
                confirmButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('frmEliminarArqueo');
                    form.action = url;
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
