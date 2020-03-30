<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$prod->idproducto}}">
	{{Form::open(array('action'=>array('ProductoController@destroy',$prod->idproducto),'method'=>'delete'))}}

	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" datad-dismiss='modal' arial-label="Close">
					&times; 
					<!--<span aria-hidden='true'>x</span>-->
				</button>
				<h4 class="modal-title">Eliminar Producto</h4>
			</div>
			<div class="modal-body">
				<p>Confirme si desea Eliminar el Producto</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss='modal'>Cerrar</button>
				<button type="submit" class="btn btn-primary">Confirmar</button>
			</div>
		</div>
	</div>
	{{Form::close()}}
</div>