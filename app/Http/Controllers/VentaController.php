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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use NumberFormatter;

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
		$comprobantes =DB::table('vw_comprobantes')
			->select('tipo_comprobante', 'serie_comprobante' ,DB::raw('max(num_comprobante) as num_comprobante'))
			->groupBy('tipo_comprobante', 'serie_comprobante')
			->orderBy('tipo_comprobante')
			->get();

    	$personas=DB::table('persona')
            ->where('tipo_persona','=','Cliente')
            ->where('ind_inactivo','=','0')
            ->get();
    	
    	$productos=DB::table('producto as pr')
    		->join('detalle_ingreso as di','pr.idproducto','=','di.idproducto')
    		->select(DB::raw('CONCAT(pr.codigo," ",pr.nombre) as producto'),'pr.idproducto', 'pr.stock',DB::raw('ROUND(avg(di.precio_venta),2) as precio_promedio'))
    		->where('pr.estado','=','Activo')
    		->where('pr.stock','>','0')
    		->groupBy('producto','pr.idproducto','pr.stock')
    		->get();
    	return view("ventas.venta.create",["personas"=>$personas,"productos"=>$productos,"comprobantes"=>$comprobantes]);
    }

    public function store(VentaFormRequest $request)
    {
		DB::beginTransaction();
    	try {
    		
    		$venta = new Venta;
    		$venta->idcliente = $request->get('idcliente');
    		$venta->tipo_comprobante = explode('_',$request->get('tipo_comprobante'))[0];
    		$venta->serie_comprobante = $request->get('serie_comprobante');
    		$venta->num_comprobante = $request->get('num_comprobante');
    		$venta->total_venta = $request->get('total_venta');
    		
    		$mytime = Carbon::now('America/Lima');
    		$venta->fecha_hora = $mytime; //->toDateTimeString()
    		$venta->impuesto = '18';
    		$venta->estado = 'A';
    		$venta->save();

    		$idproducto = $request->get('idproducto');
    		$cantidad = $request->get('cantidad');
    		$descuento = $request->get('descuento');
    		$precio_venta = $request->get('precio_venta');

			$cont = 0;
			// inicializo el array para pasarlo a la generacion de comp electr.
			$detallesVta = array();

    		while($cont < count($idproducto)){

    			$detalle = new DetalleVenta();
    			$detalle->idventa = $venta->idventa;
    			$detalle->idproducto = $idproducto[$cont];
    			$detalle->cantidad = $cantidad[$cont];
    			$detalle->descuento = $descuento[$cont];
    			$detalle->precio_venta = $precio_venta[$cont];
				$detalle->save();

				// voy a llenar del objetos detalle vta
				$detallesVta[$cont] = $detalle;
    			$cont = $cont +1;
    		}

			DB::commit();
			//$ceResp = $this->enviarComprobante('F001-7');
			Log::info($this->enviarComprobante($venta, $detallesVta));
			
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
	
	public function enviarComprobante($venta,$detallesVta){

		switch($venta->tipo_comprobante) {
			case 'Factura':
				$tipComprob = "01";
			break;
			case 'Boleta':
				$tipComprob = "03";
			break;
		};
		
		$detalleArr = array();

		foreach($detallesVta as $det):
			$detalleArr[] = array(
				"unidad" => "NIU",
				"cantidad" => $det->cantidad,
				"codProducto" => $det->idproducto,
				"descripcion" => "PRODUCTO ".$det->idproducto,
				"mtoValorUnitario" => $det->precio_venta,
				"mtoBaseIgv" => 100,
				"porcentajeIgv" => 18.0,
				"igv" => 18,
				"tipAfeIgv" => "10",
				"TotalImpuestos" => 18,
				"mtoPrecioUnitario" => 118,
				"mtoValorVenta" => $det->precio_venta
			);
		endforeach;

		$mtoTexto = new NumberFormatter("es", NumberFormatter::SPELLOUT);
		$mtoDecimal = str_pad((($venta->total_venta)*100)%100, 2,STR_PAD_LEFT);
		error_log($venta->fecha_hora);
		//error_log(substr($venta->fecha_hora->format('Y-m-d\TH:i:sP'),0,19)."-00:00");
		//$valDt = new DateTime('2020-08-24 13:05:00');

		//echo $digit->format(1000);
		$data = array(
			"ublVersion" => "2.1",
			"tipoOperacion" => "0101",
			"tipoDoc" => $tipComprob,
			"serie" => $venta->serie_comprobante,
			"correlativo" => $venta->num_comprobante,
			"fechaEmision" => substr($venta->fecha_hora->format('Y-m-d\TH:i:sP'),0,19)."+01:00", //"2018-06-13T12:34:00+01:00"
			"client" => array(
				"tipoDoc" => "0",
				"numDoc" => $venta->idcliente,
				"rznSocial" => "COMPANY SAC"
			),
			"company" => array(
				"ruc" => "20486743990",
				"razonSocial" => "FIERROS J & R SAN JUAN E.I.R.L.",
				"nombreComercial" => "FIERROS J & R SAN JUAN E.I.R.L.",
				"address" => array(
					"ubigueo" => "120604",
					"codigoPais" => "PE",
					"departamento" => "JUNIN",
					"provincia" => "SATIPO",
					"distrito" => "MAZAMARI",
					"urbanizacion" => "-",
					"direccion" => "JR. SAN JUAN NRO. 285 Mazamari-Satipo-Junín"
				)
			),
			"tipoMoneda" => "PEN",
			"mtoOperGravadas" => $venta->total_venta,
			"mtoIGV" => 18,
			"totalImpuestos" => 18,
			"valorVenta" => $venta->total_venta,
			"mtoImpVenta" => $venta->total_venta,
			"details" => $detalleArr,
			"legends" => array(
				array(
					"code" => "1000",
					"value" => "SON: ".strtoupper($mtoTexto->format($venta->total_venta))." Y ".$mtoDecimal."/100 SOLES"
				)
			)
			);
		
		$vtaEncoded = json_encode($data);

		// RUTA para enviar documentos
		//$ruta = "localhost:8080/api/v1/invoice/send";
		$ruta = "localhost:8080/api/v1/invoice/pdf";
		//TOKEN para enviar documentos
		$token = "123456";

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $ruta.'?token='.$token,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			//CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30000,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $vtaEncoded,
			CURLOPT_HTTPHEADER => array(
				// Set here requred headers
				"accept: */*",
				"accept-language: en-US,en;q=0.8",
				"content-type: application/json",
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return $err;
		} else {
			//print_r(json_decode($response));
			//$resp = json_decode($response);

			//$zipStr = $resp->sunatResponse->cdrZip;
			//$dataZip = base64_decode($zipStr);
			//Storage::disk('local')->put('F001.zip',$dataZip);
			Storage::disk('local')->put('F001.pdf',$response);
			//error_log($resp->sunatResponse->cdrZip);
			return $response;
		};
	}
}
