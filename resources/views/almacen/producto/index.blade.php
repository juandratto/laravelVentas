@extends ('layouts.admin')
@section ('contenido')
<div class="row">
	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
		<h3>Listado de Productos <a href="producto/create"><button class="btn btn-success">Nuevo</button></a></h3>
		@include('almacen.producto.search')
	</div>
</div>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th>Id</th>
					<th>Nombre</th>
					<th>Código</th>
					<th>Categoría</th>
					<th>Stock</th>
					<!-- <th>Imagen</th> -->
					<th>Unidad</th>
					<th>Estado</th>
					<th>Opciones</th>
				</thead>
				@foreach ($productos as $prod)
				<tr>
					<td>{{ $prod->idproducto}}</td>
					<td>{{ $prod->nombre}}</td>
					<td>{{ $prod->codigo}}</td>
					<td>{{ $prod->categoria}}</td>
					<td>{{ $prod->stock}}</td>
					<td>{{ $prod->unidad}}</td>
					<!-- <td>
						<img src="{{asset('imagenes/productos/'.$prod->imagen)}}" alt="{{ $prod->nombre}}" height="100px" width="100px" class="img-thumbnail">
					</td> -->
					<td>{{ $prod->estado}}</td>
					<td>
						<a href="{{URL::action('ProductoController@edit',$prod->idproducto)}}"><button class="btn btn-info">Editar</button></a>
						<a href="" data-target="#modal-delete-{{$prod->idproducto}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
					</td>
				</tr>
				@include('almacen.producto.modal')
				@endforeach
			</table>
		</div>
		{{$productos->render()}}
	</div>
</div>
@endsection