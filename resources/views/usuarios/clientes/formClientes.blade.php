<div class="row">
    <div class="col-md-4">
        <div class="row">
            <div class="form-group text-center">
                <img src="{{ isset($user) ? $user->user_image : asset('assets/img/clientesFotos/no-photo.jpg')}}" class="img-thumbnail" alt="">
            </div>
        </div>
        <br>
        <div class="row">
            <div class="input-group input-group-sm mb-3">
                <input type="file" accept="image/jpeg, image/png, image/jpeg" name="foto" class="form-control form-control-sm" id="foto">
                <label class="input-group-text" for="foto">Subir</label>
            </div>
        </div>
        @if(isset($user))
        <div class="row mb-4 text-center align-content-center">
            <div class="col">
                <a href="#" class="btn btn-sm btn-danger btnEliminarFoto" data-id="{{$user->id_enc}}">Eliminar Foto <i class="fa fa-times"></i> </a>
            </div>
        </div>
        @endif
    </div>
    <div class="col-md-8 border p-2">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab" aria-controls="datos" aria-selected="true">Datos Generales</button>
            </li>
            @if(isset($user))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="negocios-tab" data-bs-toggle="tab" data-bs-target="#negocios" type="button" role="tab" aria-controls="negocios" aria-selected="false">Negocios</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fiadores-tab" data-bs-toggle="tab" data-bs-target="#fiadores" type="button" role="tab" aria-controls="fiador" aria-selected="false">Fiadores</button>
                </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="prestamos-tab" data-bs-toggle="tab" data-bs-target="#prestamo" type="button" role="tab" aria-controls="prestamos" aria-selected="false">Histórico Préstamos</button>
            </li>
            @endif
        </ul>
        <div class="tab-content" id="myTabContent">
            <br>
            <div class="tab-pane fade show active" id="datos" role="tabpanel" aria-labelledby="datos-tab">
               @include('usuarios.clientes.tabs.tabDatosGenerales')
            </div>
            @if(isset($user))
                <div class="tab-pane fade" id="prestamo" role="tabpanel" aria-labelledby="prestamos-tab">
                    @include('usuarios.clientes.tabs.tabPrestamos')
                </div>
                <div class="tab-pane fade" id="fiadores" role="tabpanel" aria-labelledby="fiadores-tab">
                    <div class="row">
                        @can('Crear Fiadores')
                        <div class="col-md-12">
                            <a href="{{route('fiador.create',['user'=>$user->id_enc])}}" class="btn btn-sm btn-primary">Nuevo Fiador <i class="fa fa-plus"></i> </a>
                        </div>
                        @endcan
                    </div>
                    <br>
                        @include('usuarios.clientes.tabs.tabFiadores')
                </div>
                <div class="tab-pane fade" id="negocios" role="tabpanel" aria-labelledby="negocios-tab">
                    <div class="row">
                        <div class="col-md-12">
                            @can('Crear Negocios')
                            <a href="{{route('negocio.create',['user'=>$user->id_enc])}}" target="_blank" class="btn btn-sm btn-primary">Nuevo negocio <i class="fa fa-plus"></i> </a>
                            @endcan
                        </div>
                    </div>
                    <br>
                        @include('usuarios.clientes.tabs.tabNegocios')
                </div>
            @endif

        </div>

    </div>
</div>
<br>
<hr>
<div class="row">
    <div class="col-md-12">
        <h4>Documentos del Cliente</h4>
    </div>
</div>
<hr>
<div class="row">
    @foreach($tiposDocumentos as $documento)
    <div class="col-md-6">

            <div class="row">
                <div class="col-md-12">
                    <h5 class="p-1 bg-secondary-light">{{$documento->nombre}}</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        @if($documento->tipo_documento==2)
                            @if(isset($user) && $user->documentosUser()->where('documento_id',$documento->id)->exists())
                                @php
                                    $thisDocumento = $user->documentosUser->where('documento_id',$documento->id)->first()
                                @endphp
                            <div class="alert alert-primary" role="alert">
                                <strong>
                                    {{$thisDocumento->url}}
                                    <a target="_blank" href="{{asset('assets/img/documentos/'.$thisDocumento->url)}}" class="btn btn-sm btn-primary float-end"><i class="fa fa-eye"></i></a>
                                    <a href="javascript:void(0);" class="btn btn-sm float-end mx-1 btn-danger btnEliminarDocumento" data-id="{{$thisDocumento->id_enc}}"><i class="fa fa-times"></i></a>
                                </strong>
                            </div>
                            @endif
                            <div class="input-group input-group-sm mb-3">
                                <input type="file" accept=".xlsx,.xls,.doc, .docx,.txt,.pdf" name="file[{{$documento->id}}]">
                            </div>
                        @else
                            <div class="form-group text-center">
                                @php
                                    $totalDocumentos = isset($user) ? $user->documentosUser()->where('documento_id',$documento->id)->get():0;
                                @endphp
                                @if(isset($user) && count($totalDocumentos) > 0)
                                    @foreach($totalDocumentos as $doc)
                                        <img style="width: 200px" src="{{ asset('assets/img/documentos/'.$doc->url)}}" class="img-thumbnail" alt="">
                                        <a href="javascript:void(0);" class="btn btn-sm btn-danger btnEliminarDocumento" data-id="{{$doc->id_enc}}"><i class="fa fa-times"></i></a>
                                    @endforeach
                                @else
                                    <img style="width: 200px" src="{{ asset('assets/img/documentos/ico.png')}}" class="img-thumbnail" alt="">
                                @endif
                            </div>
                            <div class="input-group input-group-sm mb-3">
                                <input type="file" accept="image/jpeg, image/png, image/jpeg" name="file[{{$documento->id}}][]" multiple>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    </div>
    @endforeach

</div>
