@extends ('layouts.admin')
@section ('contenido')
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
		<h5>Nueva Venta</h5>
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

{!!Form::open(array('url'=>'ventas/venta','method'=>'POST','autocomplete'=>'off'))!!}
{{Form::token()}}

<div class="row">
	<div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
		<div class="form-group">
			<label for="nombre">Cliente</label>
			<select name="idcliente" id="idcliente" class="form-control selectpicker" data-live-search="true">
				@foreach($personas as $persona)
				<option value="{{$persona->idpersona}}">{{$persona->nombre}}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-lg-4 col-sm-4 col-md-4 col-xs-12">
		<div class="form-group">
			<label>Tipo Comprobante</label>
			<select name="tipo_comprobante" id="ptipocomprobante" class="form-control">
			@foreach($comprobantes as $comprobante)
				<option value="{{$comprobante->tipo_comprobante}}_{{$comprobante->serie_comprobante}}_{{$comprobante->num_comprobante}}">{{$comprobante->tipo_comprobante}}</option>
			@endforeach
			</select>
		</div>
	</div>
	<div class="col-lg-4 col-sm-4 col-md-4 col-xs-12">
		<div class="form-group">
			<label for="serie_comprobante">Serie Comprobante</label>
			<input type="text" class="form-control" id="pseriecomprobante" name="serie_comprobante" value="{{$comprobantes[0]->serie_comprobante}}" placeholder="Serie de comprobante..."></input>
		</div>
	</div>
	<div class="col-lg-4 col-sm-4 col-md-4 col-xs-12">
		<div class="form-group">
			<label for="num_comprobante">Número de Comprobante</label>
			<input type="text" class="form-control" id="pnumcomprobante" name="num_comprobante" required value="{{$comprobantes[0]->num_comprobante}}" placeholder="Número de comprobante..."></input>
		</div>
	</div>
</div>
<div class="row">
	<div class="panel panel-primary">
		<div class="panel-body">
			<div class="col-lg-4 col-sm-4 col-md-4 col-xs-12">
				<div class="form-group">
					<label>Producto</label>
					<select name="pidproducto" class="form-control selectpicker" id="pidproducto" data-live-search="true">
						@foreach($productos as $producto)
						<option value="{{$producto->idproducto}}_{{$producto->stock}}_{{$producto->precio_promedio}}">{{$producto->producto}}</option>
						@endforeach 
					</select>
				</div>
			</div>
			<div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
				<div class="form-group">
					<label for="cantidad">Cantidad</label>
					<input type="number" name="pcantidad" id="pcantidad" class="form-control" placeholder="cantidad">
				</div>
			</div>
			<div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
				<div class="form-group">
					<label for="stock">Stock</label>
					<input type="number" disabled name="pstock" id="pstock" class="form-control" placeholder="stock">
				</div>
			</div>
			<div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
				<div class="form-group">
					<label for="precio_venta">Precio Venta</label>
					<input type="number" disabled name="pprecio_venta" id="pprecio_venta" class="form-control" placeholder="P. Venta">						
				</div>
			</div>
			<div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
				<div class="form-group">
					<label for="descuento">Descuento</label>
					<input type="number" name="pdescuento" id="pdescuento" class="form-control" placeholder="Descuento">					
				</div>
			</div>
			<div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
				<div class="form-group">
					<button type="button" id="bt_add" class="btn btn-primary">Agregar</button>
				</div>
			</div>
			<div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
				<table id="detalles" class="table table-striped table-bordered table-condensed table-hover">
					<thead style="background-color: #A9D0F5">
						<th>Opciones</th>
						<th>Producto</th>
						<th>Cantidad</th>
						<th>Precio Venta</th>
						<th>Descuento</th>
						<th>Sub Total</th>
					</thead>
					<tfoot>
						<th>TOTAL</th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th><h4 id="total">S/ 0.00</h4><input type="hidden" name="total_venta" id="total_venta"></th>	
					</tfoot>
					<tbody>
						
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12" id="guardar">
		<div class="form-group">
			<input name="_token" value="{{csrf_token()}}" type="hidden"></input>
			<button class="btn btn-primary" type="submit">Guardar</button>
			<button class="btn btn-danger" type="reset">Cancelar</button>
		</div>	
	</div>
</div>
{!!Form::close()!!}

@push ('scripts')
<script type="text/javascript">
	
	$(document).ready(function(){
		$('#bt_add').click(function(){
			agregar();
		});
	});

    var cont = 0;
	total=0;
	subtotal=[];

	$("#guardar").hide();
	$("#pidproducto").change(mostrarValores);
	$("#ptipocomprobante").change(cambioValComprob);

	function mostrarValores(){

		datosProducto=document.getElementById('pidproducto').value.split('_');
		$("#pprecio_venta").val(datosProducto[2]);
		$("#pstock").val(datosProducto[1]);
	}

	function cambioValComprob(){

		datoscomprobante=document.getElementById('ptipocomprobante').value.split('_');
		if (datoscomprobante[0] == "Factura"){
			$("#pseriecomprobante").val(datoscomprobante[1]);
			$("#pnumcomprobante").val(datoscomprobante[2]);
		}
	}

	function agregar(){

		datosProducto=document.getElementById('pidproducto').value.split('_');

		idproducto=datosProducto[0];
		producto=$("#pidproducto option:selected").text();
		cantidad=parseInt($("#pcantidad").val()) || 0;
		descuento=parseInt($("#pdescuento").val()) || 0;
		precio_venta=$("#pprecio_venta").val();
		stock=$("#pstock").val();

		if (idproducto!="" && cantidad>0 && precio_venta!="") 
		{
			if (stock>=cantidad)
			{
			subtotal[cont]=(cantidad*precio_venta - descuento);
			total= total+subtotal[cont];

			var fila='<tr class="selected" id="fila'+cont+'"><td><button type="button" class="btn btn-warning" onclick="eliminar('+cont+');">X</button></td><td><input type="hidden" name="idproducto[]" value="'+idproducto+'">'+producto+'</td><td><input type="number" name="cantidad[]" value="'+cantidad+'"></td><td><input type="number" name="precio_venta[]" value="'+precio_venta+'"></td><td><input type="number" name="descuento[]" value="'+descuento+'"></td><td>'+subtotal[cont]+'</td></tr>';

			cont++;
			limpiar();
			$("#total").html("S/ "+total);
			$("#total_venta").val(total);
			evaluar();
			$('#detalles').append(fila);
			}
			else{
				alert("No hay stock disponible para el producto...")
			}

		}
		else{
			alert("Error al ingresar el detalle de venta, revise los datos del producto");
		}

	}

	function limpiar(){
		$("#pcantidad").val("");
		$("#pdescuento").val("");
		$("#pprecio_venta").val("");
	}

	function evaluar(){
		if (total > 0)
		{
			$("#guardar").show();
		}
		else
		{
			$("#guardar").hide();
		}
	}

	function eliminar(index){
		total=total-subtotal[index];
		$("#total").html("S/ " + total);
		$("#total_venta").val(total);
		$("#fila" + index).remove();
		evaluar();
	}
</script>
@endpush
@endsection