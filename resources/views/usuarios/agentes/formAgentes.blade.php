<div class="row">
    <div class="col-md-4 my-auto">
        <div class="row">
            <div class="form-group text-center">
                <img src="{{ isset($user) ? $user->user_image : asset('assets/img/agentesFotos/no-photo.jpg')}}" class="img-thumbnail" alt="">
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
    <div class="col-md-8">
        <div class="form-group">
            <label for="nombre" class="fw-bold required">Nombres:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                {{html()->text('nombres')->class(['form-control'])->id('nombre')->required()}}
            </div>
            @error('nombres')
                <strong style="color: red">{{$message}}</strong>
            @enderror
        </div>
        <div class="form-group">
            <label for="apellidos" class="fw-bold required">Apellidos:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                {{html()->text('apellidos')->class(['form-control'])->id('apellidos')->required()}}
            </div>
            @error('apellidos')
            <strong style="color: red">{{$message}}</strong>
            @enderror
        </div>
        <div class="form-group">
            <label for="cedula" class="fw-bold required">Cédula:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                {{html()->text('cedula')->class(['form-control'])->id('cedula')->required()}}
            </div>
            @error('cedula')
                <strong style="color: red">{{$message}}</strong>
            @enderror
        </div>
        <div class="form-group">
            <label for="telefono1" class="fw-bold">Teléfono1:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                {{html()->text('telefono1')->class(['form-control'])->id('telefono1')}}
            </div>
            @error('telefono1')
                <strong style="color: red">{{$message}}</strong>
            @enderror
        </div>
        <div class="form-group">
            <label for="telefono2" class="fw-bold">Teléfono2:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                {{html()->text('telefono2')->class(['form-control'])->id('telefono2')}}
            </div>
            @error('telefono2')
                <strong style="color: red">{{$message}}</strong>
            @enderror
        </div>

        <div class="form-group">
            <label for="direccion" class="fw-bold">Dirección:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-address-book"></i></span>
                {{html()->textarea('direccion')->class(['form-control'])->id('direccion')}}
            </div>
        </div>

        <div class="form-group">
            <label for="estado" class="fw-bold">Estado:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-info"></i></span>
                {{html()->select('estado',['1'=>'Activo','2'=>'Inactivo'])->class(['form-control'])->id('estado')}}
            </div>
        </div>

    </div>
</div>
