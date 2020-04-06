<?php

namespace sisVentas\Http\Controllers;

use Illuminate\Http\Request;

use sisVentas\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use sisVentas\Http\Requests\VentaFormRequest;
use sisVentas\Venta;
use sisVentas\DetalleVenta;
use DB;

use Carbon\Carbon;
use Response;
use Illuminate\Support\Collention;
use DateTime;

class VentaController extends Controller
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
    		$ventas=DB::table('venta as v')
    		->join('persona as p','v.idcliente','=','p.idpersona')
    		->join('detalle_venta as dv','v.idventa','=','dv.idventa')
    		->select('v.idventa','v.fecha_hora','p.nombre','v.tipo_comprobante','v.serie_comprobante','v.num_comprobante','v.impuesto','v.estado','v.total_venta')
    		->where('v.num_comprobante','LIKE','%'.$query.'%')
    		->groupBy('v.idventa','v.fecha_hora','p.nombre','v.tipo_comprobante','v.serie_comprobante','v.num_comprobante','v.impuesto','v.estado')
    		->orderBy('v.idventa','desc')
    		->paginate(7);
    		return view('ventas.venta.index',["ventas"=>$ventas,"searchText"=>$query]);
    	}
    }
    
    public function create()
    {
    	$personas=DB::table('persona')
            ->where('tipo_persona','=','Cliente')
            ->where('ind_inactivo','=','0')
            ->get();
    	
    	$productos=DB::table('producto as pr')
    		->join('detalle_ingreso as di','pr.idproducto','=','di.idproducto')
    		->select(DB::raw('CONCAT(pr.codigo," ",pr.nombre) as producto'),'pr.idproducto', 'pr.stock',DB::raw('avg(di.precio_venta) as precio_promedio'))
    		->where('pr.estado','=','Activo')
    		->where('pr.stock','>','0')
    		->groupBy('producto','pr.idproducto','pr.stock')
    		->get();
    	return view("ventas.venta.create",["personas"=>$personas,"productos"=>$productos]);
    }

    public function store(VentaFormRequest $request)
    {
    	try {
    		DB::beginTransaction();
    		
    		$venta = new Venta;
    		$venta->idcliente = $request->get('idcliente');
    		$venta->tipo_comprobante = $request->get('tipo_comprobante');
    		$venta->serie_comprobante = $request->get('serie_comprobante');
    		$venta->num_comprobante = $request->get('num_comprobante');
    		$venta->total_venta = $request->get('total_venta');
    		
    		$mytime = Carbon::now('America/Lima');
    		$venta->fecha_hora = $mytime->toDateTimeString();
    		$venta->impuesto = '18';
    		$venta->estado = 'A';
    		$venta->save();

    		$idproducto = $request->get('idproducto');
    		$cantidad = $request->get('cantidad');
    		$descuento = $request->get('descuento');
    		$precio_venta = $request->get('precio_venta');

			$cont = 0;

    		while($cont < count($idproducto)){

    			$detalle = new DetalleVenta();
    			$detalle->idventa = $venta->idventa;
    			$detalle->idproducto = $idproducto[$cont];
    			$detalle->cantidad = $cantidad[$cont];
    			$detalle->descuento = $descuento[$cont];
    			$detalle->precio_venta = $precio_venta[$cont];
				$detalle->save();
				

				//$ceItems[] = $item;

    			$cont = $cont +1;
			}

    		DB::commit();
    	} catch (Exception $e) {

    		DB::rollback();

    	}

    	return Redirect::to('ventas/venta');
    }

    public function show($id)
    {
    	$venta = DB::table('venta as v')
    		->join('persona as p','v.idcliente','=','p.idpersona')
    		->join('detalle_venta as dv','v.idventa','=','dv.idventa')
    		->select('v.idventa','v.fecha_hora','p.nombre','v.tipo_comprobante','v.serie_comprobante','v.num_comprobante','v.impuesto','v.estado','total_venta')
    		->where('v.idventa','=',$id)
    		->first();

    	$detalles = DB::table('detalle_venta as det')
    		->join('producto as pr', 'det.idproducto','=','pr.idproducto')
    		->select('pr.nombre as producto','det.cantidad','det.descuento','det.precio_venta')
    		->where('det.idventa','=',$id)
    		->get();
    	return view("ventas.venta.show",["venta"=>$venta,"detalles"=>$detalles]);
    }

    public function destroy($id)
    {
    	$venta = Ingreso::findOrFail($id);
    	$venta->estado = 'C';
    	$venta->update();

    	return Redirect::to('ventas/venta');
    }
}
