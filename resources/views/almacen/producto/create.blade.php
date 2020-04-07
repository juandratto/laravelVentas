@extends ('layouts.admin')
@section ('contenido')
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
		<h3>Nuevo Producto</h3>
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

{!!Form::open(array('url'=>'almacen/producto','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
{{Form::token()}}

<div class="row">
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label for="nombre">Nombre</label>
			<input type="text" class="form-control" name="nombre" required-value="{{old('nombre')}}" placeholder="Nombre..."></input>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label>Categoría</label>
			<select name="idcategoria" class="form-control">
				@foreach ($categorias as $cat)
				<option value="{{$cat->idcategoria}}">{{$cat->nombre}}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label for="codigo">Código</label>
			<input type="text" class="form-control" name="codigo" required-value="{{old('codigo')}}" placeholder="Código del producto..."></input>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label for="stock">Stock</label>
			<input type="text" class="form-control" name="stock" required-value="{{old('stock')}}" placeholder="Stock del Producto..."></input>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label for="descripcion">Descripción</label>
			<input type="text" class="form-control" name="descripcion" required-value="{{old('descripcion')}}" placeholder="Descripción del Producto..."></input>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
		<div class="form-group">
			<label>Unidad</label>
					<select name="unidad" class="form-control">
						<option value="UND">UND</option>
						<option value="KG">KG</option>
						<option value="LT">LT</option>
						<option value="M2">M2</option>
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