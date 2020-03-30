<?php

namespace sisVentas\Http\Controllers;

use Illuminate\Http\Request;

use sisVentas\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use sisVentas\Http\Requests\IngresoFormRequest;
use sisVentas\Ingreso;
use sisVentas\DetalleIngreso;
use DB;

use Carbon\Carbon;
use Response;
use Illuminate\Support\Collention;


class IngresoController extends Controller
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
    		$ingresos=DB::table('ingreso as i')
    		->join('persona as p','i.idproveedor','=','p.idpersona')
    		->join('detalle_ingreso as di','i.idingreso','=','di.idingreso')
    		->select('i.idingreso','i.fecha_hora','p.nombre','i.tipo_comprobante','i.serie_comprobante','i.num_comprobante','i.impuesto','i.estado',DB::raw('sum(di.cantidad*precio_compra) as total'))
    		->where('i.num_comprobante','LIKE','%'.$query.'%')
    		->groupBy('i.idingreso','i.fecha_hora','p.nombre','i.tipo_comprobante','i.serie_comprobante','i.num_comprobante','i.impuesto','i.estado')
    		->orderBy('i.idingreso','desc')
    		->paginate(7);
    		return view('compras.ingreso.index',["ingresos"=>$ingresos,"searchText"=>$query]);
    	}
    }
    
    public function create()
    {
    	$personas=DB::table('persona')
            ->where('tipo_persona','=','Proveedor')
            ->where('ind_inactivo','=','0')
            ->get();
    	$productos=DB::table('producto as pr')
    		->select(DB::raw('CONCAT(pr.codigo," ",pr.nombre) as producto'),'pr.idproducto')
    		->where('pr.estado','=','Activo')
    		->get();
    	return view("compras.ingreso.create",["personas"=>$personas,"productos"=>$productos]);
    }

    public function store(IngresoFormRequest $request)
    {
    	try {
    		DB::beginTransaction();
    		
    		$ingreso = new Ingreso;
    		$ingreso->idproveedor = $request->get('idproveedor');
    		$ingreso->tipo_comprobante = $request->get('tipo_comprobante');
    		$ingreso->serie_comprobante = $request->get('serie_comprobante');
    		$ingreso->num_comprobante = $request->get('num_comprobante');
    		$mytime = Carbon::now('America/Lima');
    		$ingreso->fecha_hora = $mytime->toDateTimeString();
    		$ingreso->impuesto = '18';
    		$ingreso->estado = 'A';
    		$ingreso->save();

    		$idproducto = $request->get('idproducto');
    		$cantidad = $request->get('cantidad');
    		$precio_compra = $request->get('precio_compra');
    		$precio_venta = $request->get('precio_venta');

    		$cont = 0;

    		while($cont < count($idproducto)){

    			$detalle = new DetalleIngreso();
    			$detalle->idingreso = $ingreso->idingreso;
    			$detalle->idproducto = $idproducto[$cont];
    			$detalle->cantidad = $cantidad[$cont];
    			$detalle->precio_compra = $precio_compra[$cont];
    			$detalle->precio_venta = $precio_venta[$cont];
    			$detalle->save();
    			$cont = $cont +1;
    		}

    		DB::commit();
    	} catch (Exception $e) {

    		DB::rollback();

    	}

    	return Redirect::to('compras/ingreso');
    }

    public function show($id)
    {
    	$ingreso = DB::table('ingreso as i')
    		->join('persona as p','i.idproveedor','=','p.idpersona')
    		->join('detalle_ingreso as di','i.idingreso','=','di.idingreso')
    		->select('i.idingreso','i.fecha_hora','p.nombre','i.tipo_comprobante','i.serie_comprobante','i.num_comprobante','i.impuesto','i.estado',DB::raw('sum(di.cantidad*precio_compra) as total'))
    		->where('i.idingreso','=',$id)
    		->first();

    	$detalles = DB::table('detalle_ingreso as det')
    		->join('producto as pr', 'det.idproducto','=','pr.idproducto')
    		->select('pr.nombre as producto','det.cantidad','det.precio_compra','det.precio_venta')
    		->where('det.idingreso','=',$id)
    		->get();
    	return view("compras.ingreso.show",["ingreso"=>$ingreso,"detalles"=>$detalles]);
    }

    public function destroy($id)
    {
    	$ingreso = Ingreso::findOrFail($id);
    	$ingreso->estado = 'C';
    	$ingreso->update();

    	return Redirect::to('compras/ingreso');
    }
}
