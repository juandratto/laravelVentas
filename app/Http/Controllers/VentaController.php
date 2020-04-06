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

// Librerias Greenter - Generacion de factura electronica Sunat
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;

//require __DIR__ . 'sisVentas\..\vendor\autoload.php';

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

			//$util = Util::getInstance();
			// Genera factura??
			$client = new Client();
			$client->setTipoDoc('6')
			->setNumDoc('20000000001')
			->setRznSocial('EMPRESA 1');
			
			// Emisor
			$address = new Address();
			$address->setUbigueo('150101')
			->setDepartamento('LIMA')
			->setProvincia('LIMA')
			->setDistrito('LIMA')
			->setUrbanizacion('NONE')
			->setDireccion('AV LS');
			
			$company = new Company();
			$company->setRuc('20000000001')
			->setRazonSocial('EMPRESA SAC')
			->setNombreComercial('EMPRESA')
			->setAddress($address);
			

			$invoice = (new Invoice());
			$invoice->setUblVersion('2.1')
			->setTipoOperacion('0101') // Catalog. 51
			->setTipoDoc('01')
			->setSerie('F001')
			->setCorrelativo('4')
			->setFechaEmision(new DateTime())
			->setTipoMoneda('PEN')
			->setClient($client)
			->setMtoOperGravadas(100.00)
			->setMtoIGV(18.00)
			->setTotalImpuestos(18.00)
			->setValorVenta(100.00)
			->setSubTotal(118.00)
			->setMtoImpVenta(118.00)
			->setCompany($company);

			$cont = 0;
			//items de la factura
			$ceItems = [];
			$item = (new SaleDetail())
			->setCodProducto('P001')
			->setUnidad('NIU')
			->setCantidad(2)
			->setDescripcion('PRODUCTO 1')
			->setMtoBaseIgv(100)
			->setPorcentajeIgv(18.00) // 18%
			->setIgv(18.00)
			->setTipAfeIgv('10')
			->setTotalImpuestos(18.00)
			->setMtoValorVenta(100)
			->setMtoValorUnitario(50.00)
			->setMtoPrecioUnitario(59.00);

			$legend = (new Legend())
			->setCode('1000')
			->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');

			$invoice->setDetails([$item])
					->setLegends([$legend]);

			$see = require __DIR__.'/../../../config.php';
			$result = $see->send($invoice);

			// Guardar XML
			file_put_contents($invoice->getName().'.xml',
							$see->getFactory()->getLastXml());
			if (!$result->isSuccess()) {
				var_dump($result->getError());
				exit();
			}

			echo $result->getCdrResponse()->getDescription();
			// Guardar CDR
			file_put_contents('R-'.$invoice->getName().'.zip', $result->getCdrZip());

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
