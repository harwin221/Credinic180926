<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
            <tr class="table-success">
                <th>#</th>
                <th>Tipo</th>
                <th>Monto</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            @if(isset($user))
            @forelse($user->prestamos as $prestamo)
                <tr>
                    <td>{{$prestamo->consecutivo}}</td>
                    <td>{{$prestamo->forma_pago}}</td>
                    <td>{{$prestamo->moneda}} {{number_format($prestamo->monto_prestamo,2)}}</td>
                    <td>{{fecha_d_m_Y($prestamo->fecha_prestamo)}}</td>
                    <td>{{$prestamo->estado_prestamo}}</td>
                        <td><a href="{{route('prestamos.show',$prestamo->id_enc)}}" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a></td>
                </tr>
            @empty
                <p class="text-center fw-bold">No hay registro de préstamos</p>
            @endforelse
            @endif
            </tbody>
        </table>
    </div>
</div>
