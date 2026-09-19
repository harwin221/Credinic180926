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
        {{html()->text('telefono1')->class(['form-control'])->placeholder('Número sin guiones')->id('telefono1')}}
    </div>
    @error('telefono1')
    <strong style="color: red">{{$message}}</strong>
    @enderror
</div>
<div class="form-group">
    <label for="telefono2" class="fw-bold">Teléfono2:</label>
    <div class="input-group">
        <span class="input-group-text"><i class="fa fa-phone"></i></span>
        {{html()->text('telefono2')->class(['form-control'])->placeholder('Número sin guiones')->id('telefono2')}}
    </div>
    @error('telefono2')
    <strong style="color: red">{{$message}}</strong>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="sexo" class="fw-bold">Sexo:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                {{html()->select('sexo',['0'=>'Masculino','1'=>'Femenino'])->class(['form-control'])->id('sexo')}}
            </div>
            @error('sexo')
            <strong style="color: red">{{$message}}</strong>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="estado_civil" class="fw-bold">Estado Civil:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-info"></i></span>
                {{html()->select('estado_civil',['0'=>'Soltero(a)','1'=>'Casado(a)','2'=>'Unión Libre','3'=>'Viudo(a)','4'=>'Divorciado(a)'])->class(['form-control'])->id('estado_civil')}}
            </div>
            @error('estado_civil')
            <strong style="color: red">{{$message}}</strong>
            @enderror
        </div>
    </div>
</div>


<div class="form-group">
    <label for="dep_mun" class="fw-bold">Departamento / Municipio</label>
    <div class="input-group">
        <span class="input-group-text"><i class="fa fa-info"></i></span>
        {{html()->select('dep_mun',[''=>'** Seleccione **']+departamento_municipios(),isset($user)?encode($user->dep_mun):null)->class(['form-control'])->required()->id('dep_mun')}}
    </div>
    @error('dep_mun')
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
