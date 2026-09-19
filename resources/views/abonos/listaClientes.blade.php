<table class="table table-sm">
    <thead class="table-info">
    <tr>
        <th>#</th>
        <th>Cliente</th>
        <th>Cédula</th>
        <th></th>
    </tr>
    </thead>

    <tbody>
    @foreach($clientes as $cliente)
        <tr>
            <td>{{$loop->index+1}}</td>
            <td>{{$cliente->full_name}}</td>
            <td>{{$cliente->cedula}}</td>
            <td><a href="javascript:void(0);" data-id="{{$cliente->id_enc}}" data-prestamo-id="{{encode($cliente->prestamo_id)}}" class="btnSelCliente btn btn-sm btn-info"><i class="fa fa-plus"></i></a></td>
        </tr>
    @endforeach
    </tbody>
</table>
{{$clientes->links()}}

