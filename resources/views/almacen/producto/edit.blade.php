@extends ('layouts.admin')
@section ('contenido')
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
		<h3>Editar Producto: {{$producto->nombre}}</h3>
		@if (count($errors)>0)
		<div class="alert alert-danger">
			<ul>
			@foreach ($errors->all() as $error)
				<li>{{$error}}</li>
			@endforeach
			</ul>
		</div>
		@endif
	</div>
</div>

		{!!Form::model($producto,['method'=>'PATCH','route'=>['almacen.producto.update',$producto->idproducto], 'files'=>'true'])!!}
		{{Form::token()}}
<div class="row">
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label for="nombre">Nombre</label>
			<input type="text" class="form-control" name="nombre" required value="{{$producto->nombre}}"></input>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label>Categoría</label>
			<select name="idcategoria" class="form-control">
				@foreach ($categorias as $cat)
					@if($cat->idcategoria==$producto->idcategoria)
					<option value="{{$cat->idcategoria}}" selected>{{$cat->nombre}}</option>
					@else
					<option value="{{$cat->idcategoria}}">{{$cat->nombre}}</option>
					@endif
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label for="codigo">Código</label>
			<input type="text" class="form-control" name="codigo" required value="{{$producto->codigo}}"></input>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label for="stock">Stock</label>
			<input type="text" class="form-control" name="stock" required value="{{$producto->stock}}"></input>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label for="descripcion">Descripción</label>
			<input type="text" class="form-control" name="descripcion" required value="{{$producto->descripcion}}" placeholder="Descripción del Producto..."></input>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label>Unidad</label>
					<select name="unidad" class="form-control">
					@php ($unidades = ["UND", "KG", "LT", "M2"])
					@foreach ($unidades as $un)
						@if($un==$producto->unidad)
						<option value="{{$un}}" selected>{{$un}}</option>
						@else
						<option value="{{$un}}">{{$un}}</option>
						@endif
					@endforeach
					</select>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<button class="btn btn-primary" type="submit">Guardar</button>
			<button class="btn btn-danger" type="reset">Cancelar</button>
		</div>	
	</div>
</div>

		{!!Form::close()!!}

@endsection