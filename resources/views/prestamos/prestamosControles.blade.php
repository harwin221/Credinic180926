<div class="col-md-4">
    <div class="form-floating">
        <input type="number" min="1" step="0.01" class="form-control" value id="monto_prestamo" placeholder="Monto total del préstamo" disabled>
        <label for="monto_prestamo" class="required" id="monto_prestamo">Monto del Préstamo:</label>
    </div>
</div>


<div class="col-md-4">
    <div class="form-floating">
        {{html()->date('fecha_prestamo',\Carbon\Carbon::now())->id('fecha_prestamo')->class('form-control')->required()}}
        <label for="monto_prestamo" class="required" id="fecha_prestamo">Fecha del préstamo</label>
    </div>
</div>
