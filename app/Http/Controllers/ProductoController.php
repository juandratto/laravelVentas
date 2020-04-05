<?php

namespace sisVentas\Http\Controllers;

use Illuminate\Http\Request;

use sisVentas\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use sisVentas\Http\Requests\ProductoFormRequest;
use sisVentas\Producto;
use DB;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
    	if ($request)
    	{
    		$query=trim($request->get('searchText'));
    		$productos=DB::table('producto as pr')
    		->join('categoria as cat','pr.idcategoria','=','cat.idcategoria')
    		->select('pr.idproducto','pr.nombre','pr.codigo','pr.stock','cat.nombre as categoria','pr.descripcion', 'pr.unidad','pr.estado')
    		->where('pr.nombre','LIKE','%'.$query.'%')
            ->orwhere('pr.codigo','LIKE','%'.$query.'%')
    		->orderBy('pr.idproducto','desc')
    		->paginate(7);
    		return view('almacen.producto.index',["productos"=>$productos,"searchText"=>$query]);
    	}
    }
    public function create()
    {
    	$categorias=DB::table('categoria')->where('condicion','=','1')->get();

    	return view("almacen.producto.create",["categorias"=>$categorias]);
    }
    public  function store(ProductoFormRequest $request)
    {
    	$producto= new Producto;
    	$producto->idcategoria=$request->get('idcategoria');
    	$producto->codigo=$request->get('codigo');
    	$producto->nombre=$request->get('nombre');
    	$producto->stock=$request->get('stock');
    	$producto->descripcion=$request->get('descripcion');
    	$producto->estado='Activo';
    	$producto->unidad=$request->get('unidad');
/*     	if (Input::hasFile('imagen')){
    		$file=Input::file('imagen');
    		$file->move(public_path().'/imagenes/productos/',$file->getClientOriginalName());
    		$producto->imagen=$file->getClientOriginalName();
    	} */
    	$producto->save();

    	Return Redirect::to('almacen/producto');

    }
    public function show($id)
    {
        return view("almacen.producto.show",["producto"=>Producto::findOrFail($id)]);

    }
     public function edit($id)
    {
    	$producto=Producto::findOrFail($id);
    	$categorias=DB::table('categoria')->where('condicion','=','1')->get();

    	return view("almacen.producto.edit",["producto"=>$producto,"categorias"=>$categorias]);
    }
     public function update(ProductoFormRequest $request,$id)
    {
    	$producto=Producto::findOrFail($id);
    	
    	$producto->idcategoria=$request->get('idcategoria');
    	$producto->codigo=$request->get('codigo');
    	$producto->nombre=$request->get('nombre');
    	$producto->stock=$request->get('stock');
		$producto->descripcion=$request->get('descripcion');
		$producto->unidad=$request->get('unidad');
    	
    	/*if (Input::hasFile('imagen')){
    		$file=Input::file('imagen');
    		$file->move(public_path().'/imagenes/productos/',$file->getClientOriginalName());
    		$producto->imagen=$files->getClientOriginalName();
    	}*/

    	$producto->update();
    	return Redirect::to('almacen/producto');
    }
     public function destroy($id)
    {
    	$producto=Producto::findOrFail($id);
    	$producto->estado='Inactivo';
    	$producto->update();
    	return Redirect::to('almacen/producto');
    }
}
