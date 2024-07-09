<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Viaje;
use App\Models\Destino;
use App\Models\PasajeroEjecutivo;
use App\Models\Gps;
use App\Models\NovedadViaje;
use App\Models\User;
use App\Models\Proveedor;
use App\Models\Centrosdecosto;
use App\Models\Liquidacionservicios;
use App\Models\Vehiculo;
use App\Models\Facturacion;
use App\Models\Ordenfactura;

use Auth;
use Response;
Use DB;
Use Config;
use Hash;

class FacturacionController extends Controller
{
    public function listtrips(Request $request) {

        $viajes = "SELECT
		v.*,
        c.razonsocial, 
        sub.nombre, 
        p.razonsocial as nombre_proveedor, 
        cond.primer_nombre, 
        cond.segundo_nombre, 
        cond.primer_apellido, 
        cond.segundo_apellido, 
        veh.placa, 
        veh.marca, 
        veh.modelo, 
        veh.ano, 
        veh.capacidad, 
        veh.color, 
        ciu.nombre as nombre_ciudad, 
        tras.nombre as nombre_traslado,
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        us.first_name, us.last_name,
        fac.fk_estado as estado_facturacion, fac.fk_viaje as numero_planilla,
        esta.nombre as nombre_estado_facturacion, esta.codigo as codigo_estado_facturacion,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT JSON_ARRAYAGG(JSON_OBJECT('nombre', pax.nombre, 'celular', pax.celular)) FROM viajes v2 left join pasajeros_ejecutivos pax on pax.fk_viaje = v2.id where v2.id = v.id) as pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join subcentrosdecosto sub on sub.id = v.fk_subcentrodecosto
        left join proveedores p on p.id = v.fk_proveedor
        left JOIN conductores cond on cond.id = v.fk_conductor
        left join vehiculos veh on veh.id = v.fk_vehiculo
        left join ciudades ciu on ciu.id = v.fk_ciudad
        left join traslados tras on tras.id = v.fk_traslado
        left join destinos d on d.fk_viaje = v.id 
        left join users us on us.id = v.creado_por
        left join facturacion_de_viajes fac on fac.fk_viaje = v.id
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        left join estados esta on esta.id = fac.fk_estado 
        where v.estado_eliminacion is null and v.estado_papelera is null and fac.fk_estado is null
        GROUP BY v.id order by hora_viaje";

        $viajes = DB::select($viajes);

        $proveedores = Proveedor::activoFinanciero();

        $centrosdecosto = Centrosdecosto::activoFinanciero();

        //$subcentros = DB::table('subcentrosdecosto')->get(); REVISAR

        $ciudades = DB::table('ciudades')->get();

        $usuarios = User::AdministrativosActivos();

        return Response::json([
            'response' => true,
            'viajes' => $viajes,
            'proveedores' => $proveedores,
            'centrosdecosto' => $centrosdecosto,
            'ciudades' => $ciudades,
            'usuarios' => $usuarios
        ]);
        
    }

    public function revisar(Request $request) {

        $checkArray = $request->viajes;

        for ($i=0; $i < count($checkArray); $i++) {

            $viaje = Viaje::find($checkArray[$i]['id']);
            //$servicio->recoger_en = $recogerenArray[$i];
            //$servicio->dejar_en = $dejarenArray[$i];
            $viaje->detalle_recorrido = $checkArray[$i]['detalle'];
            $viaje->fecha_viaje = $checkArray[$i]['fecha'];
            $viaje->hora_viaje = $checkArray[$i]['hora'];
            $viaje->save();

            //BUSCAR TIPO DE VEHICULO, PARA CONSULTAR LA TARIFA Y AGREGARLA AUTOMATICAMENTE A LA TB FACTURACION
            $vehiculo_id = $viaje->fk_vehiculo;
            $serv_vehiculo = Vehiculo::find($vehiculo_id);

            $tipo_vehiculo_ser = $serv_vehiculo->fk_tipo_vehiculo;

            $tipo_vehiculo_ser = DB::table('tipos')
            ->where('id',$tipo_vehiculo_ser)
            ->first();

            $tipo_vehiculo_ser = $tipo_vehiculo_ser->nombre;

            $ciudad_serv = $viaje->fk_ciudad;
            
            $swRecargo = 0;


            if($tipo_vehiculo_ser === 'AUTOMOVIL' || $tipo_vehiculo_ser === 'CAMIONETA'){
                
                $tarifa_cliente = DB::table('tarifas')
                ->where('trayecto_id',$viaje->fk_traslado)
                ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                ->first();
                $tarifa_cliente = $tarifa_cliente->cliente_auto;

                $tarifa_proveedor = DB::table('tarifas')
                ->where('trayecto_id',$viaje->fk_traslado)
                ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                ->first();
                $tarifa_proveedor = $tarifa_proveedor->proveedor_auto;

            }

            //SGS BAQ-BOG - RUTAS
            if(1>2){
            //if($viaje->ruta==1 and ($viaje->centrodecosto_id==287 or $viaje->centrodecosto_id==19)){

                $cantidad = $viaje->cantidad;

                if( $tipo_vehiculo_ser!='AUTOMOVIL' and $tipo_vehiculo_ser!='CAMIONETA' ){ //si es van
                    
                    if(intval($viaje->localidad)!=1){ //BAQ
                        $sear = 'cliente_van';
                    }else{ //BOG
                        if($cantidad<5){
                            $sear = 'cliente_auto';
                        }else{
                            $sear = 'cliente_van';
                        }
                    }

                    if($cantidad<5){ //Auto
                        $searp = 'proveedor_auto';
                    }else{ //Van
                        $sear = 'cliente_van';
                        $searp = 'proveedor_van';
                    }

                }else{ //auto

                    $sear = 'cliente_auto';
                    $searp = 'proveedor_auto';

                }

                if(intval($servicio->localidad)==1){
                    $tarifa_cliente = DB::table('tarifas')->where('trayecto_id',$servicio->ruta_id)->where('centrodecosto_id',$servicio->centrodecosto_id)->whereNotNull('localidad')->pluck(''.$sear.'');
                    $tarifa_proveedor = DB::table('tarifas')->where('trayecto_id',$servicio->ruta_id)->where('centrodecosto_id',$servicio->centrodecosto_id)->whereNotNull('localidad')->pluck(''.$searp.'');
                }else{
                    $tarifa_cliente = DB::table('tarifas')->where('trayecto_id',$servicio->ruta_id)->where('centrodecosto_id',$servicio->centrodecosto_id)->whereNull('localidad')->pluck(''.$sear.'');
                    $tarifa_proveedor = DB::table('tarifas')->where('trayecto_id',$servicio->ruta_id)->where('centrodecosto_id',$servicio->centrodecosto_id)->whereNull('localidad')->pluck(''.$searp.'');
                }

                $sw = $sear;

            //}else if($viaje->ruta!=1){ //Servicios Ejecutivos
            }else if(1==1){ //Servicios Ejecutivos

                if($tipo_vehiculo_ser === 'AUTOMOVIL' || $tipo_vehiculo_ser === 'CAMIONETA'){
                        
                    $tarifa_cliente = DB::table('tarifas')
                    ->leftjoin('traslados', 'traslados.id', '=', 'tarifas.trayecto_id')
                    ->select('tarifas.*', 'traslados.fk_sede')
                    ->where('trayecto_id',$viaje->fk_traslado)
                    ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                    ->where('traslados.fk_sede',$viaje->fk_sede)
                    ->first();

                    if($tarifa_cliente){
                        $tarifa_cliente = $tarifa_cliente->cliente_auto;
                    }else{
                        $tarifa_cliente = null;
                    }

                    $recargo = DB::table('centrosdecosto')->where('id',$viaje->fk_centrodecosto)->first();

                    return Response::json([
                        'res' => $recargo
                    ]);

                    if($recargo->recargo_nocturno==1 and ($viaje->hora_viaje>=$recargo->desde or $viaje->hora_viaje<$recargo->hasta)){
                        $tarifa_cliente = $tarifa_cliente+($tarifa_cliente*0.20);
                        $swRecargo = 1;
                    }

                    $tarifa_proveedor = DB::table('tarifas')
                    ->leftjoin('traslados', 'traslados.id', '=', 'tarifas.trayecto_id')
                    ->select('tarifas.*', 'traslados.fk_sede')
                    ->where('trayecto_id',$viaje->fk_traslado)
                    ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                    ->where('traslados.fk_sede',$viaje->fk_sede)
                    ->first();

                    if($tarifa_proveedor){
                        $tarifa_proveedor = $tarifa_proveedor->proveedor_auto;
                    }else{
                        $tarifa_proveedor = null;
                    }

                }else{

                    $tarifa_cliente = DB::table('tarifas')
                    ->leftjoin('traslados', 'traslados.id', '=', 'tarifas.trayecto_id')
                    ->select('tarifas.*', 'traslados.fk_sede')
                    ->where('trayecto_id',$viaje->fk_traslado)
                    ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                    ->where('traslados.fk_sede',$viaje->fk_sede)
                    ->first();

                    if($tarifa_cliente){
                        $tarifa_cliente = $tarifa_cliente->cliente_van;
                    }else{
                        $tarifa_cliente = null;
                    }

                    $recargo = DB::table('centrosdecosto')->where('id',$viaje->fk_centrodecosto)->first();

                    if($recargo->recargo_nocturno==1 and ($viaje->hora_viaje>=$recargo->desde or $viaje->hora_viaje<$recargo->hasta)){
                        
                        $tarifa_cliente = $tarifa_cliente+($tarifa_cliente*0.20);

                    }

                    $tarifa_proveedor = DB::table('tarifas')
                    ->leftjoin('traslados', 'traslados.id', '=', 'tarifas.trayecto_id')
                    ->select('tarifas.*', 'traslados.fk_sede')
                    ->where('trayecto_id',$viaje->fk_traslado)
                    ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                    ->where('traslados.fk_sede',$viaje->fk_sede)
                    ->first();

                    if($tarifa_proveedor){
                        $tarifa_proveedor = $tarifa_proveedor->proveedor_van;
                    }else{
                        $tarifa_proveedor = null;
                    }
                }

            }else if($servicio->ruta==1){

                if($tipo_vehiculo_ser === 'AUTOMOVIL' || $tipo_vehiculo_ser === 'CAMIONETA'){

                    $tarifa_cliente = DB::table('tarifas')
                    ->leftjoin('traslados', 'traslados.id', '=', 'tarifas.trayecto_id')
                    ->select('tarifas.*', 'traslados.fk_sede')
                    ->where('trayecto_id',$viaje->fk_traslado)
                    ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                    ->where('traslados.fk_sede',$viaje->fk_sede)
                    ->first();

                    $tarifa_cliente = $tarifa_cliente->cliente_auto;

                    $tarifa_proveedor = DB::table('tarifas')
                    ->leftjoin('traslados', 'traslados.id', '=', 'tarifas.trayecto_id')
                    ->select('tarifas.*', 'traslados.fk_sede')
                    ->where('trayecto_id',$viaje->fk_traslado)
                    ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                    ->where('traslados.fk_sede',$viaje->fk_sede)
                    ->first();

                    $tarifa_proveedor = $tarifa_proveedor->proveedor_auto;

                }else{

                    $tarifa_cliente = DB::table('tarifas')
                    ->leftjoin('traslados', 'traslados.id', '=', 'tarifas.trayecto_id')
                    ->select('tarifas.*', 'traslados.fk_sede')
                    ->where('trayecto_id',$viaje->fk_traslado)
                    ->where('centrodecosto_id',$viaje->fk_centrodecosto)
                    ->where('traslados.fk_sede',$viaje->fk_sede)
                    ->first();

                    $tarifa_cliente = $tarifa_cliente->cliente_van;

                    $tarifa_proveedor = DB::table('tarifas')
                    ->where('trayecto_id',$servicio->ruta_id)
                    ->where('centrodecosto_id',$servicio->centrodecosto_id)
                    ->whereNotNull('localidad')
                    ->first();

                    $tarifa_proveedor = $tarifa_proveedor->proveedor_van;

                }

            }

            //BUSCA EL REGISTRO DE LA FACTURA SEGUN EL ID
            $factura = DB::table('facturacion_de_viajes')->where('fk_viaje',$checkArray[$i]['id'])->first();

            //SI LA CONSULTA ESTA VACIA ENTONCES NO HAY UN REGISTRO EXISTENTE PARA ESA ID
            if ($factura===null) {

                //GUARDAR EL REGISTRO NUEVO
                try {

                    //NUEVO REGISTRO PARA LA FACTURACION

                    $facturacion = new Facturacion();
                    if(isset($checkArray[$i]['comentario'])){
                        $facturacion->info = $checkArray[$i]['comentario'];
                    }
                    $facturacion->observacion = $checkArray[$i]['observacion'];
                    $facturacion->fk_estado = 64; //Validar que sea el estado revisado
                    
                    if($swRecargo==1){
                        $facturacion->recargo = 1;
                    }

                    if($tarifa_cliente!=null and $tarifa_proveedor!=null) {
                        //$facturacion->fk_estado = 65;
                        $facturacion->utilidad = floatval($tarifa_cliente)-floatval($tarifa_proveedor);
                        $facturacion->total_cobrado = $tarifa_cliente;
                        $facturacion->total_pagado = $tarifa_proveedor;
                    }

                    $facturacion->fk_viaje = $checkArray[$i]['id'];
                    $facturacion->creado_por = Auth::user()->id;

                    //se valida q las variables tengan valores para agregar
                    if($tarifa_cliente != null || $tarifa_proveedor != null){
                        $facturacion->unitario_cobrado = $tarifa_cliente;
                        $facturacion->unitario_pagado = $tarifa_proveedor;
                    }

                    $facturacion->save();

                    $ediciones_servicios = DB::table('edicion_de_facturacion')
                    ->insert([
                        'cambios' => 'OBSERVACION: '.$checkArray[$i]['observacion'].', NUMERO DE CONSTANCIA: '.$checkArray[$i]['id'].'',
                        'created_at' => date('Y-m-d H:i:s'),
                        'creado_por' => Auth::user()->id,
                        'fk_viaje' => $checkArray[$i]['id']
                    ]);


                }catch (Exception $e) {

                    return Response::json([
                        'response'=>'error',
                        'e'=>$e
                    ]);

                }

            }
        }

        return Response::json([
            'response' => true
        ]);


    }

    public function listtripsliq(Request $request) {

        $viajes = "SELECT
		v.*,
        c.razonsocial, 
        sub.nombre, 
        p.razonsocial as nombre_proveedor, 
        cond.primer_nombre, 
        cond.segundo_nombre, 
        cond.primer_apellido, 
        cond.segundo_apellido, 
        veh.placa, 
        veh.marca, 
        veh.modelo, 
        veh.ano, 
        veh.capacidad, 
        veh.color, 
        ciu.nombre as nombre_ciudad, 
        tras.nombre as nombre_traslado,
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        us.first_name, us.last_name,
        fac.fk_estado as estado_facturacion, fac.fk_viaje as numero_planilla, fac.observacion, fac.liquidado_autorizado,
        esta.nombre as nombre_estado_facturacion, esta.codigo as codigo_estado_facturacion, fac.total_cobrado, fac.total_pagado, fac.utilidad,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT JSON_ARRAYAGG(JSON_OBJECT('nombre', pax.nombre, 'celular', pax.celular)) FROM viajes v2 left join pasajeros_ejecutivos pax on pax.fk_viaje = v2.id where v2.id = v.id) as pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join subcentrosdecosto sub on sub.id = v.fk_subcentrodecosto
        left join proveedores p on p.id = v.fk_proveedor
        left JOIN conductores cond on cond.id = v.fk_conductor
        left join vehiculos veh on veh.id = v.fk_vehiculo
        left join ciudades ciu on ciu.id = v.fk_ciudad
        left join traslados tras on tras.id = v.fk_traslado
        left join destinos d on d.fk_viaje = v.id 
        left join users us on us.id = v.creado_por
        left join facturacion_de_viajes fac on fac.fk_viaje = v.id
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        left join estados esta on esta.id = fac.fk_estado 
        where v.estado_eliminacion is null and v.estado_papelera is null and fac.fk_estado in(64,65)
        GROUP BY v.id order by hora_viaje";

        $viajes = DB::select($viajes);

        $proveedores = Proveedor::activoFinanciero();

        $centrosdecosto = Centrosdecosto::activoFinanciero();

        //$subcentros = DB::table('subcentrosdecosto')->get(); REVISAR

        $ciudades = DB::table('ciudades')->get();

        $usuarios = User::AdministrativosActivos();

        return Response::json([
            'response' => true,
            'viajes' => $viajes,
            'proveedores' => $proveedores,
            'centrosdecosto' => $centrosdecosto,
            'ciudades' => $ciudades,
            'usuarios' => $usuarios
        ]);
        
    }

    public function liquidar(Request $request) {

        $coment_totalc = '';
        $coment_totalp = '';
        $coment_totalu = '';

        $tcobradoActual = DB::table('facturacion_de_viajes')
        ->where('fk_viaje', $request->id)
        ->pluck('total_cobrado');

        $tpagadoActual = DB::table('facturacion_de_viajes')
        ->where('fk_viaje', $request->id)
        ->pluck('total_pagado');

        //TOMAR LOS VALORES DE EL CAMPO CAMBIOS PARA CONOCER SI ESTA O NO VACIO
        $liquidado = DB::table('facturacion_de_viajes')->where('fk_viaje',$request->id)->first();

        //SI EL CAMPO DE UTILIDAD NO ESTA VACIO QUIERE DECIR QUE YA TIENE UN REGISTRO
        if ($liquidado->utilidad!=null) {

            if (floatval($liquidado->total_cobrado)!=floatval($request->total_cobrado)) {
                $coment_totalc = 'TOTAL COBRADO SE CAMBIO DE: $'.number_format($liquidado->total_cobrado).' A: $'.number_format($request->total_cobrado).'';
            }else{
                $coment_totalc = 'TOTAL COBRADO SE MANTUVO EN: $'.number_format($liquidado->total_cobrado).'';
            }

            if (floatval($liquidado->total_pagado)!=floatval($request->total_pagado)) {
                $coment_totalp = 'TOTAL PAGADO SE CAMBIO DE: $'.number_format($liquidado->total_pagado).' A: $'.number_format($request->total_pagado).'';
            }else{
                $coment_totalp = 'TOTAL PAGADO SE MANTUVO EN: $'.number_format($liquidado->total_pagado).'';
            }

            if (floatval($liquidado->utilidad)!=floatval($request->utilidad)) {
                $coment_totalu = 'UTILIDAD SE CAMBIO DE: $'.number_format($liquidado->utilidad).' A: $'.number_format($request->utilidad).'';
            }else{
                $coment_totalu = 'UTILIDAD SE MANTUVO EN: $'.number_format($liquidado->utilidad).'';
            }

            $liquidar = DB::table('facturacion_de_viajes')
            ->where('fk_viaje', $request->id)
            ->update([
                'total_cobrado'=> $request->total_cobrado,
                'total_pagado'=> $request->total_pagado,
                'utilidad'=> $request->utilidad,
                'fk_estado' => 65, //liquidado
            ]);
            
            if ($liquidar!=null) {

                $ediciones_servicios = DB::table('edicion_de_facturacion')
                ->insert([
                    'cambios' => $coment_totalc.', '.$coment_totalp.', '.$coment_totalu,
                    'created_at' => date('Y-m-d H:i:s'),
                    'creado_por' => Auth::user()->id,
                    'fk_viaje' => $request->id
                ]);

                $query = DB::table('facturacion_de_viajes')
                ->where('fk_viaje', $request->id)
                ->first();

                if($query->liquidacion_id!=null){ //estaba sin el null 

                    $liqui = DB::table('liquidacion_servicios')
                    ->where('id',$query->liquidacion_id)
                    ->first();

                    $total_nuevo = intval($liqui->total_facturado_cliente)-intval($tcobradoActual);
                    $total_nuevo = $total_nuevo+intval($request->total_cobrado);

                    $totalp_nuevo = intval($liqui->total_costo)-intval($tpagadoActual);
                    $totalp_nuevo = $totalp_nuevo+intval($request->total_pagado);

                    $utilidad_nuevo = intval($total_nuevo)-intval($totalp_nuevo);

                    $updateLiq = DB::table('liquidacion_servicios')
                    ->where('id', $query->liquidacion_id)
                    ->update([
                        'total_facturado_cliente' => $total_nuevo,
                        'total_costo' => $totalp_nuevo,
                        'total_utilidad' => $utilidad_nuevo
                    ]);
                }

                return Response::json([
                    'response' => true,
                    'liquidar' => $liquidado
                ]);

            }else{
                
                return Response::json([
                    'response'=>false
                ]);

            }
            
        }else{

            if (floatval($liquidado->total_cobrado)!=floatval($request->total_cobrado)) {
                $coment_totalc = 'TOTAL COBRADO SE CAMBIO DE: $'.number_format($liquidado->total_cobrado).' A: $'.number_format($request->total_cobrado).'';
            }else{
                $coment_totalc = 'TOTAL COBRADO SE MANTUVO EN: $'.number_format($liquidado->total_cobrado).'';
            }

            if (floatval($liquidado->total_pagado)!=floatval($request->total_pagado)) {
                $coment_totalp = 'TOTAL PAGADO SE CAMBIO DE: $'.number_format($liquidado->total_pagado).' A: $'.number_format($request->total_pagado).'';
            }else{
                $coment_totalp = 'TOTAL PAGADO SE MANTUVO EN: $'.number_format($liquidado->total_pagado).'';
            }

            if (floatval($liquidado->utilidad)!=floatval($request->utilidad)) {
                $coment_totalu = 'UTILIDAD SE CAMBIO DE: $'.number_format($liquidado->utilidad).' A: $'.number_format($request->utilidad).'';
            }else{
                $coment_totalu = 'UTILIDAD SE MANTUVO EN: $'.number_format($liquidado->utilidad).'';
            }
            
            $liquidar = DB::table('facturacion_de_viajes')
            ->where('fk_viaje', $request->id)
            ->update([
                'total_cobrado'=> $request->total_cobrado,
                'total_pagado'=> $request->total_pagado,
                'utilidad'=> $request->utilidad,
                'fk_estado' => 65, //liquidado
            ]);

            if ($liquidar!=null) {

                $ediciones_servicios = DB::table('edicion_de_facturacion')
                ->insert([
                    'cambios' => $coment_totalc.', '.$coment_totalp.', '.$coment_totalu,
                    'created_at' => date('Y-m-d H:i:s'),
                    'creado_por' => Auth::user()->id,
                    'fk_viaje' => $request->id
                ]);

                $query = DB::table('facturacion_de_viajes')
                ->where('fk_viaje', $request->id)
                ->first();

                if($query->liquidacion_id!=null){ //estaba solo null

                    $liqui = DB::table('liquidacion_servicios')
                    ->where('id',$query->liquidacion_id)
                    ->first();

                    $total_nuevo = intval($liqui->total_facturado_cliente)-intval($tcobradoActual);
                    $total_nuevo = $total_nuevo+intval($request->total_cobrado);

                    $totalp_nuevo = intval($liqui->total_costo)-intval($tpagadoActual);
                    $totalp_nuevo = $totalp_nuevo+intval($request->total_pagado);

                    $utilidad_nuevo = intval($total_nuevo)-intval($totalp_nuevo);

                    $updateLiq = DB::table('liquidacion_servicios')
                    ->where('id', $query->liquidacion_id)
                    ->update([
                        'total_facturado_cliente' => $total_nuevo,
                        'total_costo' => $totalp_nuevo,
                        'total_utilidad' => $utilidad_nuevo
                    ]);

                }

                return Response::json([
                    'response' => true,
                    'liquidar' => $liquidado
                ]);

            }else{
                
                return Response::json([
                    'response' => false
                ]);

            }

        }

    }

    public function generarliquidacion(Request $request) {

        //CONTAR ORDENES DE FACTURACION PARA SABER SI YA EXISTE UNA ORDEN DE FACTURACION CON ESTOS VALORES
        $ordenes_facturacion = DB::table('ordenes_facturacion')
        ->where('fk_centrodecosto',$request->centrodecosto)
        ->where('fk_subcentrodecosto',$request->subcentrodecosto)
        ->where('tipo_orden',1)
        ->where('fk_ciudad',$request->ciudad)
        ->where('anulado',null)
        ->whereBetween('fecha_inicial', array($request->fecha_inicial, $request->fecha_final))
        ->whereBetween('fecha_final', array($request->fecha_inicial, $request->fecha_final))
        ->get();

        $liquidaciones = DB::table('liquidacion_servicios')
        ->where('fk_centrodecosto',$request->centrodecosto)
        ->where('fk_subcentrodecosto',$request->subcentrodecosto)
        ->where('fk_ciudad',$request->ciudad)
        ->whereBetween('fecha_inicial', array($request->fecha_inicial, $request->fecha_final))
        ->whereBetween('fecha_final', array($request->fecha_inicial, $request->fecha_final))
        ->where('anulado',null)
        ->get();


        if (count($ordenes_facturacion) > 0  ) {

            return Response::json([
                'response'=>'AT',
                'ordenes_facturacion' => $ordenes_facturacion,
                'message' => 'YA EXISTE UNA FACTURA: '.$ordenes_facturacion[0]->numero_factura
            ]);

        }else if(count($liquidaciones) > 0){

            return Response::json([
                'response'=>'OF',
                'liquidaciones' => $liquidaciones,
                'message' => 'YA EXISTE UNA LIQUIDACIÓN: '.$liquidaciones[0]->consecutivo
            ]);

        }else{

            $liquidacion_servicios = new Liquidacionservicios;
            $liquidacion_servicios->fk_centrodecosto = $request->centrodecosto;
            $liquidacion_servicios->fk_subcentrodecosto = $request->subcentrodecosto;
            $liquidacion_servicios->fk_ciudad = $request->ciudad;
            $liquidacion_servicios->fecha_registro = date('Y-m-d H:i:s');
            $liquidacion_servicios->fecha_inicial = $request->fecha_inicial;
            $liquidacion_servicios->fecha_final = $request->fecha_final;
            $liquidacion_servicios->total_facturado_cliente = floatval($request->total_generado_cobrado)+floatval($request->otros_ingresos)-floatval($request->otros_costos);
            $liquidacion_servicios->total_costo = $request->total_generado_pagado;
            $liquidacion_servicios->total_utilidad = $request->total_generado_utilidad;
            $liquidacion_servicios->otros_ingresos = $request->otros_ingresos;
            $liquidacion_servicios->otros_costos = $request->otros_costos;
            $liquidacion_servicios->creado_por = Auth::user()->id;
            $liquidacion_servicios->observaciones = $request->observaciones;

            if ($liquidacion_servicios->save()) {

                $id = $liquidacion_servicios->id;
                $liquidacion = Liquidacionservicios::find($id);

                if (strlen(intval($id))===1) {
                    $liquidacion->consecutivo = 'OF000'.$id;
                    $liquidacion->save();
                }elseif (strlen(intval($id))===2) {
                    $liquidacion->consecutivo = 'OF00'.$id;
                    $liquidacion->save();
                }elseif(strlen(intval($id))===3){
                    $liquidacion->consecutivo = 'OF0'.$id;
                    $liquidacion->save();
                }elseif(strlen(intval($id))===4){
                    $liquidacion->consecutivo = 'OF'.$id;
                    $liquidacion->save();
                }else{
                    $liquidacion->consecutivo = 'OF'.$id;
                    $liquidacion->save();
                }

                $viajes = $request->viajes;
                $contar=0;

                //CICLO PARA ENLAZAR LOS SERVICIOS A UNA LIQUIDACION
                for ($i=0; $i <count($viajes); $i++) {

                    $ediciones_servicios = DB::table('edicion_de_facturacion')
                    ->insert([
                        'cambios' => 'SERVICIO PREPARADO PARA AUTORIZAR EN LA PRE LIQUIDACION CON NUMERO CONSECUTIVO: '.$liquidacion->consecutivo.'',
                        'created_at' => date('Y-m-d H:i:s'),
                        'creado_por' => Auth::user()->id,
                        'fk_viaje' => $viajes[$i]
                    ]);

                    DB::table('facturacion_de_viajes')
                    ->where('fk_viaje', $viajes[$i])
                    ->update([
                        'liquidacion_id' => $id,
                    ]);

                    $contar++;
                }

                return Response::json([
                    'response' => true,
                    'contar' => $contar,
                    'ordenes_facturacion' => $ordenes_facturacion,
                    'id' => $id,
                    'viajes' => $request->viajes
                    //'expediente' => $servicioEXP
                ]);

                /*if ($contar>0) {

                    //guardar km
                    $consultaServicios = "select anulado_por from servicios where id in(".Input::get('id_facturaArray').") and anulado_por is not null and ruta is null";
                    $consult = DB::select($consultaServicios);

                    $valor = 0;
                    $valorRutas = 0;

                    foreach ($consult as $serv) {
                        $valor = floatval($valor)+floatval($serv->anulado_por);
                    }

                    $consultaRutas = "select anulado_por from servicios where id in(".Input::get('id_facturaArray').") and anulado_por is not null and ruta is not null";
                    $consultr = DB::select($consultaRutas);

                    foreach ($consultr as $rut) {
                        $valorRutas = floatval($valorRutas)+floatval($rut->anulado_por);
                    }

                    //cantidad de ejecutivos
                    $cantidadEjecutivos = "select id from servicios where id in(".Input::get('id_facturaArray').") and ruta is null";
                    $cantEje = DB::select($cantidadEjecutivos);
                    $cantiEje = count($cantEje);
                    //Cantidad de Rutas
                    $cantidadRutas = "select id from servicios where id in(".Input::get('id_facturaArray').") and ruta is not null";
                    $cantRut = DB::select($cantidadRutas);
                    $cantiRut = count($cantRut);

                    $update = DB::table('liquidacion_servicios')
                    ->where('id', $id)
                    ->update([
                        'kilometraje' => round($valor, 1),
                        'kilometraje_rutas' => round($valorRutas, 1),
                        'cantidad_ejecutivos' => $cantiEje,
                        'cantidad_rutas' => $cantiRut
                    ]);
                    //guardar km

                    return Response::json([
                        'respuesta'=>true,
                        'contar'=>$contar,
                        'ordenes_facturacion'=>$ordenes_facturacion,
                        'id'=>$id,
                        'id_facturaArray' => Input::get('id_facturaArray')
                        //'expediente' => $servicioEXP
                    ]);

                }else{
                    return Response::json([
                        'respuesta'=>'error',
                    ]);
                }*/

            }
        }
    }

    public function ofporautorizar(Request $request) {
        
        $query = "select ls.id, ls.consecutivo , ls.fecha_registro , ls.fecha_inicial , ls.fecha_final , ls.total_facturado_cliente , ls.total_costo , ls.total_utilidad , ls.otros_ingresos , ls.otros_costos , ls.observaciones , c.razonsocial , s.nombre as nombre_subcentro , c2.nombre as nombre_ciudad from liquidacion_servicios ls left join centrosdecosto c on c.id = ls.fk_centrodecosto left join subcentrosdecosto s on s.id = ls.fk_subcentrodecosto  left join ciudades c2 on c2.id  = ls.fk_ciudad WHERE ls.autorizado is null and anulado is null";
        $ordenes = DB::select($query);

        if(count($ordenes)>0) {

            return Response::json([
                'response' => true,
                'ordenes' => $ordenes
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function autorizarliquidacion(Request $request) {

        //ARRAY DE SERVICIOS
        $arrayS = $request->viajes;

        $count = 0;

        //IDENTIFICAR SI LA LIQUIDACION FUE DIVIDIDA
        $divide = false; //PENDIENTE POR REVISAR CON VANESSA

        //ID DE LA LIQUIDACION
        $id = $request->liquidacion_id;

        //FOR PARA RECORRER LOS IDS
        for ($i=0; $i < count($arrayS) ; $i++) {

            $ediciones_servicios = DB::table('edicion_de_facturacion')
            ->insert([
                'cambios' => 'SERVICIO AUTORIZADO PARA FACTURAR',
                'created_at' => date('Y-m-d H:i:s'),
                'creado_por' => Auth::user()->id,
                'fk_viaje' => $arrayS[$i]
            ]);

            $guardarLiq = DB::table('facturacion_de_viajes')
            ->where('fk_viaje',$arrayS[$i])
            ->update([
                'liquidado_autorizado' => 1,
            ]);

            if ($guardarLiq!=null) {
                $count++;
            }

        }

        $hola = null;

        if($divide==='true'){

            $aut = DB::update("update liquidacion_servicios set autorizado = ".$autorizado.", autorizado_por = ".Auth::user()->id.", fecha_autorizado = '".date('Y-m-d H:i:s')."' where id_detalle = ".$id_detalle);
            $hola = true;

        }else{

            $centro = DB::table('liquidacion_servicios')
            ->where('id',$id)
            ->first();

            //SI NO ES AVIATUR
            if($centro->fk_centrodecosto!=329){

                $hola = false;
                $aut = DB::update("update liquidacion_servicios set autorizado = 1, autorizado_por = ".Auth::user()->id.", fecha_autorizado = '".date('Y-m-d H:i:s')."' where id = ".$id);
                $total_cobrado = 0;
                $total_costo = 0;
                $utilidad = 0;

                //CONSULTA PARA TOMAR LOS SERVICIOS ENLAZADOS A ESTA PRELIQUIDACION
                $selLiq = DB::table('facturacion_de_viajes')->where('liquidacion_id',$id)->get();

                //CICLO PARA REALIZAR LA SUMA DE LOS CAMBIOS EN LOS VALORES Y ACTUALIZARLOS EN LA PRELIQUIDACION
                foreach ($selLiq as $key => $value) {
                    $total_cobrado = $total_cobrado+floatval($value->total_cobrado);
                    $total_costo = $total_costo+floatval($value->total_pagado);
                    $utilidad = $utilidad+floatval($value->utilidad);
                }

                $otros_ingresos = DB::table('liquidacion_servicios')->where('id', $id)->first();

                $liquidacion = DB::table('liquidacion_servicios')
                ->where('id',$id)
                ->update([
                    'total_facturado_cliente'=>$total_cobrado+$otros_ingresos->otros_ingresos,
                    'total_costo'=>$total_costo,
                    'total_utilidad'=>$utilidad
                ]);

            }else{

                //SI ES AVIATUR

                $liquidacion_servicios = DB::table('liquidacion_servicios')
                ->where('centrodecosto_id',329)
                ->whereNull('autorizado')
                ->whereNull('anulado')
                ->whereNull('nomostrar')
                ->get();

                foreach ($liquidacion_servicios as $liquidaciones) {

                    $hola = false;
                    $aut = DB::update("update liquidacion_servicios set autorizado = ".$autorizado.", autorizado_por = ".Auth::user()->id.", fecha_autorizado = '".date('Y-m-d H:i:s')."' where id = ".$liquidaciones->id);
                    $total_cobrado = 0;
                    $total_costo = 0;
                    $utilidad = 0;

                    //CONSULTA PARA TOMAR LOS SERVICIOS ENLAZADOS A ESTA PRELIQUIDACION
                    $selLiq = DB::table('facturacion_de_viajes')->where('liquidacion_id',$liquidaciones->id)->get();

                    //CICLO PARA REALIZAR LA SUMA DE LOS CAMBIOS EN LOS VALORES Y ACTUALIZARLOS EN LA PRELIQUIDACION
                    foreach ($selLiq as $key => $value) {
                        $total_cobrado = $total_cobrado+floatval($value->total_cobrado);
                        $total_costo = $total_costo+floatval($value->total_pagado);
                        $utilidad = $utilidad+floatval($value->utilidad);
                    }

                    $otros_ingresos = DB::table('liquidacion_servicios')->where('id', $liquidaciones->id)->first();

                    $liquidacion = DB::table('liquidacion_servicios')
                    ->where('id',$liquidaciones->id)
                    ->update([
                        'total_facturado_cliente'=>$total_cobrado+$otros_ingresos->otros_ingresos,
                        'total_costo'=>$total_costo,
                        'total_utilidad'=>$utilidad
                    ]);

                }

            }

        }

        if ($count!=0) {

            return Response::json([
                'response' => true,
                'hola' => $hola
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function ofautorizadas(Request $request) {
        
        $query = "select ls.id, ls.consecutivo , ls.fecha_registro , ls.fecha_inicial , ls.fecha_final , ls.total_facturado_cliente , ls.total_costo , ls.total_utilidad , ls.otros_ingresos , ls.otros_costos , c.razonsocial , s.nombre as nombre_subcentro , c2.nombre as nombre_ciudad , c.nit, ls.observaciones, c.id as id_centro, s.id as id_subcentro from liquidacion_servicios ls left join centrosdecosto c on c.id = ls.fk_centrodecosto left join subcentrosdecosto s on s.id = ls.fk_subcentrodecosto  left join ciudades c2 on c2.id = ls.fk_ciudad WHERE ls.autorizado is not null and ls.facturado is null and ls.anulado is null";
        $ordenes = DB::select($query);

        if(count($ordenes)>0) {

            return Response::json([
                'response' => true,
                'ordenes' => $ordenes
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function anularliquidacion(Request $request) {

        $liquidacion_servicios = Liquidacionservicios::find($request->liquidacion_id);
        $liquidacion_servicios->anulado = 1;
        $liquidacion_servicios->anulado_por = Auth::user()->id;

        if ($liquidacion_servicios->save()) {

            $facturacion = DB::table('facturacion_de_viajes')->where('liquidacion_id',$request->liquidacion_id)->get();

            foreach ($facturacion as $key => $value) {

                $ediciones_servicios = DB::table('edicion_de_facturacion')
                ->insert([
                    'cambios' => 'SERVICIO DESVINCUNLADO POR ANULACION DE LA PRELIQUIDACION CON NUMERO CONSECUTIVO: '.$liquidacion_servicios->consecutivo.'',
                    'created_at' => date('Y-m-d H:i:s'),
                    'creado_por' => Auth::user()->id,
                    'fk_viaje' => $value->fk_viaje
                ]);

                $guardarLiq = DB::table('facturacion_de_viajes')
                ->where('fk_viaje',$value->fk_viaje)
                ->update([
                    'liquidacion_id' => null,
                    'liquidado_autorizado' => null,
                ]);

            }

            return Response::json([
                'response' => true,
                'liquidacion_servicios' => $liquidacion_servicios
            ]);

        }else{

            return Response::json([
                'response' => false,
                'liquidacion_servicios'=>$liquidacion_servicios
            ]);

        }

    }

    public function validarfacturacion(Request $request) {

        $liquidacion_servicios = DB::table('liquidacion_servicios')->where('id',$request->id)->first();
        $facturacion = DB::table('facturacion_de_viajes')->where('liquidacion_id',$liquidacion_servicios->id)->get();

        $total_generado_cobrado = 0;
        $total_generado_pagado = 0;
        $total_generado_utilidad = 0;
        $arrayId = [];

        foreach ($facturacion as $key => $value) {
            array_push($arrayId,$value->fk_viaje);
            $total_generado_cobrado = $total_generado_cobrado+floatval($value->total_cobrado);
            $total_generado_pagado = $total_generado_pagado+floatval($value->total_pagado);
            $total_generado_utilidad = $total_generado_utilidad+floatval($value->utilidad);
        }

        if ($liquidacion_servicios!=null) {

            return Response::json([
                'response' => true,
                'viajes' => $arrayId,
                'liquidacion_servicios' => $liquidacion_servicios,
                'total_generado_cobrado' => $total_generado_cobrado,
                'total_generado_pagado' => $total_generado_pagado,
                'total_generado_utilidad' => $total_generado_utilidad,
                'otros_ingresos' => $liquidacion_servicios->otros_ingresos,
                'otros_costos' => $liquidacion_servicios->otros_costos
            ]);

        }else{

            return Response::json([
                'response' => false,
                'liquidacion_servicios' => $liquidacion_servicios
            ]);

        }

    }

    public function generarfactura(Request $request) {

        //CONTAR ORDENES DE FACTURACION PARA SABER SI YA EXISTE UNA ORDEN DE FACTURACION CON ESTOS VALORES
        $ordenes_facturacion = DB::table('ordenes_facturacion')
        ->where('fk_centrodecosto',$request->centrodecosto)
        ->where('fk_subcentrodecosto',$request->subcentrodecosto)
        ->where('tipo_orden',1)
        ->where('fk_ciudad',$request->ciudad)
        ->where('anulado',null)
        ->whereBetween('fecha_inicial', array($request->fecha_inicial, $request->fecha_final))
        ->whereBetween('fecha_final', array($request->fecha_inicial, $request->fecha_final))
        ->count();

        if ($ordenes_facturacion>0) {

            return Response::json([
                'response' => false,
                'ordenes_facturacion' => $ordenes_facturacion
            ]);

        }else{

            try {

                if($request->fecha_inicial==$request->fecha_final){
                    
                    $fecha = explode('-', $request->fecha_final);

                    $dia = $fecha[2];
                    $mes = $fecha[1];
                    $ano = $fecha[0];

                    if($mes==='01'){
                        $mes = 'ENERO';
                    }else if($mes==='02'){
                        $mes = 'FEBRERO';
                    }else if($mes==='03'){
                        $mes = 'MARZO';
                    }else if($mes==='04'){
                        $mes = 'ABRIL';
                    }else if($mes==='05'){
                        $mes = 'MAYO';
                    }else if($mes==='06'){
                        $mes = 'JUNIO';
                    }else if($mes==='07'){
                        $mes = 'JULIO';
                    }else if($mes==='08'){
                        $mes = 'AGOSTO';
                    }else if($mes==='09'){
                        $mes = 'SEPTIEMBRE';
                    }else if($mes==='10'){
                        $mes = 'OCTUBRE';
                    }else if($mes==='11'){
                        $mes = 'NOVIEMBRE';
                    }else if($mes==='12'){
                        $mes = 'DICIEMBRE';
                    }

                    $dias = "DEL DIA ".$dia." DE ".$mes." DEL ".$ano."";

                }else{ //Fechas diferentes

                    $fecha_inicial = explode('-', $request->fecha_inicial);
                    $fecha_final = explode('-', $request->fecha_final);

                    $diauno = $fecha_inicial[2];
                    $diados = $fecha_final[2];

                    $mesuno = $fecha_inicial[1];
                    if($mesuno==='01'){
                        $mesuno = 'ENERO';
                    }else if($mesuno==='02'){
                        $mesuno = 'FEBRERO';
                    }else if($mesuno==='03'){
                        $mesuno = 'MARZO';
                    }else if($mesuno==='04'){
                        $mesuno = 'ABRIL';
                    }else if($mesuno==='05'){
                        $mesuno = 'MAYO';
                    }else if($mesuno==='06'){
                        $mesuno = 'JUNIO';
                    }else if($mesuno==='07'){
                        $mesuno = 'JULIO';
                    }else if($mesuno==='08'){
                        $mesuno = 'AGOSTO';
                    }else if($mesuno==='09'){
                        $mesuno = 'SEPTIEMBRE';
                    }else if($mesuno==='10'){
                        $mesuno = 'OCTUBRE';
                    }else if($mesuno==='11'){
                        $mesuno = 'NOVIEMBRE';
                    }else if($mesuno==='12'){
                        $mesuno = 'DICIEMBRE';
                    }
                    $mesdos = $fecha_final[1];
                    if($mesdos==='01'){
                        $mesdos = 'ENERO';
                    }else if($mesdos==='02'){
                        $mesdos = 'FEBRERO';
                    }else if($mesdos==='03'){
                        $mesdos = 'MARZO';
                    }else if($mesdos==='04'){
                        $mesdos = 'ABRIL';
                    }else if($mesdos==='05'){
                        $mesdos = 'MAYO';
                    }else if($mesdos==='06'){
                        $mesdos = 'JUNIO';
                    }else if($mesdos==='07'){
                        $mesdos = 'JULIO';
                    }else if($mesdos==='08'){
                        $mesdos = 'AGOSTO';
                    }else if($mesdos==='09'){
                        $mesdos = 'SEPTIEMBRE';
                    }else if($mesdos==='10'){
                        $mesdos = 'OCTUBRE';
                    }else if($mesdos==='11'){
                        $mesdos = 'NOVIEMBRE';
                    }else if($mesdos==='12'){
                        $mesdos = 'DICIEMBRE';
                    }

                    $anouno = $fecha_inicial[0];
                    $anodos = $fecha_final[0];

                    if($anouno==$anodos){ //Servicios del mismo año

                        if($mesuno==$mesdos){ //Servicios del mismo mes
                            $dias = "DEL DIA ".$diauno." AL ".$diados." DE ".$mesuno." DEL ".$anouno."";
                        }else{ //Servicios de diferentes meses
                            $dias = "DEL DIA ".$diauno." DE ".$mesuno." AL ".$diados." DE ".$mesdos." DEL ".$anouno."";
                        }

                    }else{ //Servicios de dic y ene

                        $dias = "DEL DIA ".$diauno." DE ".$mesuno." DEL ".$anouno." AL ".$diados." DE ".$mesdos." DEL ".$anodos."";
                    }
                    //$dias = "DEL DIA 05 AL 06 DE DICIEMBRE DEL 2022";
                }

                $fecha = date('Y-m-d');

                if($request->fp=='Credito'){

                    if($request->rango=='15 días'){
                        $treintadias = strtotime ('+15 day', strtotime($fecha));
                        $treintadias = date('Y-m-d' , $treintadias);
                    }else if($request->rango=='30 días'){
                        $treintadias = strtotime ('+30 day', strtotime($fecha));
                        $treintadias = date('Y-m-d' , $treintadias);
                    }else if($request->rango=='Rango'){
                        $treintadias = $request->fecha_vencimiento;
                    }

                }else{
                    $treintadias = $fecha;
                }

                //$ciudad = DB::table('ciudades')->where('id',$request->ciudad)->first();//$request->ciudad;
                $ciudad = $request->ciudad;

                $valor = floatval($request->total_generado_cobrado)+floatval($request->otros_ingresos);

                if($request->centrodecosto==100){

                    $subcent = DB::table('subcentrosdecosto')
                    ->where('id',$request->subcentrodecosto)
                    ->first();

                    $identificacion = $subcent->identificacion;
                    $cliente = $subcent->nombresubcentro;

                    $totalfactura = $valor;

                    if($ciudad=='CARTAGENA'){
                        $centrodeCosto = 213;
                        $itemValue = 3;
                    }else if($ciudad=='CALI'){
                        $centrodeCosto = 215;
                        $itemValue = 4;
                    }else if($ciudad=='MALAMBO'){
                        $centrodeCosto = 219;
                        $itemValue = 6;
                    }else if($ciudad=='BARRANQUILLA'){
                        $centrodeCosto = 209;
                        $itemValue = 1;
                    }else if($ciudad=='BOGOTA'){
                        $centrodeCosto = 211;
                        $itemValue = 2;
                    }else if($ciudad=='MEDELLIN'){
                        $centrodeCosto = 217;
                        $itemValue = 5;
                    }

                }else{

                    $clientt = DB::table('centrosdecosto')
                    ->where('id',$request->centrodecosto)
                    ->first();

                    $identificacion = $clientt->nit;
                    $cliente = $clientt->razonsocial;

                    $ica = 0;

                    //Condicional de NO ICA -PENDIENTE-

                    $noica = $request->no_ica;

                    if($noica==true){

                        if($ciudad=='CARTAGENA'){
                            $procentaje = 5;
                            //$reteICA = 1;
                            $reteICA = 17954; //Producción
                            $centrodeCosto = 213;
                            $itemValue = 3;
                        }else if($ciudad=='CALI'){
                            $procentaje = 3.3;
                            //$reteICA = 1;
                            $reteICA = 17955; //Producción
                            $centrodeCosto = 215;
                            $itemValue = 4;
                        }else if($ciudad=='MALAMBO'){
                            $procentaje = 4;
                            //$reteICA = 1;
                            $reteICA = 34042; //Producción
                            $centrodeCosto = 219;
                            $itemValue = 6;
                        }else if($ciudad=='BARRANQUILLA'){
                            $procentaje = 8;
                            //$reteICA = 13167;
                            $reteICA = 34759; //Producción
                            $centrodeCosto = 209;
                            $itemValue = 1;
                        }else if($ciudad=='BOGOTA'){
                            $procentaje = 4.14;
                            //$reteICA = 13169;
                            $reteICA = 17951; //Producción
                            $centrodeCosto = 211;
                            $itemValue = 2;
                        }else if($ciudad=='MEDELLIN'){
                            $procentaje = 7;
                            //$reteICA = 1;
                            $reteICA = 17957; //Producción
                            $centrodeCosto = 217;
                            $itemValue = 5;
                        }

                        $retenciones = ",\"retentions\": [{\"id\": ".$reteICA."}]";
                        $ica = $valor*$procentaje/1000;

                    }else{

                        $ica = 0;
                        $retenciones = "";

                        if($ciudad=='CARTAGENA'){
                            $centrodeCosto = 213;
                            $itemValue = 3;
                        }else if($ciudad=='CALI'){
                            $centrodeCosto = 215;
                            $itemValue = 4;
                        }else if($ciudad=='MALAMBO'){
                            $centrodeCosto = 219;
                            $itemValue = 6;
                        }else if($ciudad=='BARRANQUILLA'){
                            $centrodeCosto = 209;
                            $itemValue = 1;
                        }else if($ciudad=='BOGOTA'){
                            $centrodeCosto = 211;
                            $itemValue = 2;
                        }else if($ciudad=='MEDELLIN'){
                            $centrodeCosto = 217;
                            $itemValue = 5;
                        }
                    }

                    //NO RETEFUENTE
                    $norete = $request->no_retefuente;

                    if($norete==true){ //Si va impuesto

                        $retef = "\"taxes\": [{\"id\": 17961}]";

                        $retefuente = $valor*0.035;
                    }else{
                        $retefuente = 0;
                        $retef = "";
                    }
                    //NO RETEFUENTE

                    $ica = round($ica, 2);
                    $retefuente = round($retefuente, 2);

                    $totalfactura = $valor-$ica-$retefuente;

                }

                $descripcion = "".$cliente." \n\n SERVICIO DE OPERACIÓN Y LOGISTICA DE TRANSPORTE PRESTADOS EN LA CIUDAD DE ".$ciudad." ".$dias." \n\n"; //Descrición de la factura

                $observa = "Sírvase cancelar esta factura cambiaría de compraventa con transferencia o consignaciones en la cuenta corriente Bancolombia No. 10798859768 a nombre de AUTO OCASIONAL TOUR S.A.S. \nDe conformidad con el Art 16 de la ley 679 del 2001, AUTO OCASIONAL TOUR S.A.S advierte al turista que la Explotación y el Abuso Sexual con menores de edad en el país son sancionadas Penal y Administrativamente conforme a las leyes vigentes. La agencia se acoge en su totalidad a la cláusula de responsabilidad establecida en el Art 3 del Dec 053 del 2002 y sus posteriores reformas.";

                $url = "https://private-anon-3e8aca8745-siigoapi.apiary-proxy.com/v1/invoices";

                if($request->centrodecosto==100){

                    $response = Facturacion::facturaSandbox(); //Factura de prueba
                    //$response = Facturacion::crearFacturaSiigoPersonaNatural(36782, 1005, $fecha, $identificacion, $centrodeCosto, $observa, $itemValue, $request->observaciones, $valor, $request->forma_pago, $totalfactura, $treintadias, $url);

                }else{

                    if($request->centrodecosto==287){
                        $identificacion = 900641706;
                    }else if($request->centrodecosto==311){
                        $identificacion = 860007738;
                    }

                    $response = Facturacion::facturaSandbox();//Factura de prueba
                    //$response = Facturacion::crearFacturaSiigoEmpresa(36782, 1005, $fecha, $identificacion, $centrodeCosto, $retenciones, $observa, $itemValue, strtoupper($request->observaciones), $valor, $retef, $request->forma_pago, round($totalfactura, 2), $treintadias, $url);

                }

                
                //guardar en siigo

                /*return Response::json([
                    'respuesta' => false,
                    'response' => json_decode($response),
                    'id' => $identificacion,
                    'valor' => $valor,
                    'ciudad' => $ciudad,
                    'ica' => $ica,
                    'retefuente' => $retefuente
                ]);*/

                //SABER EL ULTIMO NUMERO DE FACTURA QUE SE AGREGO PARA AUTOMATIZAR EL CONTADOR CABE RESALTAR QUE ESTO SOLO FUNCIONARA CUANDO HAYA ALGUN NUMERO DE FACTURA,
                //EN EL CASO DE QUE HAYA QUE COMENZAR DESDE 0 TOCARIA CAMBIAR PARTE DE ESTE CODIGO
                $ultimo_id = DB::table('ordenes_facturacion')
                ->select('id','numero_factura')
                ->orderBy('id','desc')
                ->first();

                if($ultimo_id) {
                    $numeroF = intval($ultimo_id->numero_factura)+1;
                }else{
                    $numeroF = 1;
                }

                //NUEVA ORDEN DE FACTURA
                $orden_facturacion = new Ordenfactura;
                $orden_facturacion->fk_centrodecosto = $request->centrodecosto;
                $orden_facturacion->fk_subcentrodecosto = $request->subcentrodecosto;
                $orden_facturacion->fk_ciudad = $request->ciudad;
                $orden_facturacion->fecha_expedicion = date('Y-m-d H:i:s');
                $orden_facturacion->fecha_inicial = $request->fecha_inicial;
                $orden_facturacion->fecha_final = $request->fecha_final;
                $orden_facturacion->tipo_orden = 1;
                $orden_facturacion->total_facturado_cliente = floatval($request->total_generado_cobrado)+floatval($request->otros_ingresos);
                $orden_facturacion->total_costo = $request->total_generado_pagado;
                $orden_facturacion->total_utilidad = $request->total_generado_utilidad;
                $orden_facturacion->numero_factura = $numeroF;
                $orden_facturacion->creado_por = Auth::user()->id;
                $orden_facturacion->fecha_factura = date('Y-m-d');
                $orden_facturacion->otros_ingresos = $request->otros_ingresos;
                $orden_facturacion->otros_costos = $request->otros_costos;
                $orden_facturacion->observaciones = $request->observaciones_liq;
                $orden_facturacion->facturado = 1;
                $orden_facturacion->id_siigo = json_decode($response)->id;
                $orden_facturacion->totalfactura = round($totalfactura, 2);
                $orden_facturacion->fecha_vencimiento = $treintadias;

                $centrodecosto = Centrosdecosto::find($request->centrodecosto);

                if ($orden_facturacion->save()) {

                    $id = $orden_facturacion->id;
                    $orden = Ordenfactura::find($id);

                    if (strlen(intval($id))===1) {
                        $orden->consecutivo = 'AT000'.$id;
                        $orden->save();
                    }elseif (strlen(intval($id))===2) {
                        $orden->consecutivo = 'AT00'.$id;
                        $orden->save();
                    }elseif(strlen(intval($id))===3){
                        $orden->consecutivo = 'AT0'.$id;
                        $orden->save();
                    }elseif(strlen(intval($id))===4){
                        $orden->consecutivo = 'AT'.$id;
                        $orden->save();
                    }elseif(strlen(intval($id))===5){
                        $orden->consecutivo = 'AT'.$id;
                        $orden->save();
                    }

                    $id_facturaArray = $request->viajes;
                    $contar=0;

                    //COLOCAR LOS NUMEROS DE FACTURA Y EL ESTADO FACTURADO A LOS SERVICIOS
                    for ($i=0; $i <count($id_facturaArray); $i++) { 

                        $ediciones_servicios = DB::table('edicion_de_facturacion')
                        ->insert([
                            'cambios' => 'SERVICIO VINCULADO A LA FACTURA: '.$orden_facturacion->numero_factura.'',
                            'created_at' => date('Y-m-d H:i:s'),
                            'creado_por' => Auth::user()->id,
                            'fk_viaje' => $id_facturaArray[$i]
                        ]);

                        $guardarLiq = DB::table('facturacion_de_viajes')
                        ->where('fk_viaje',$id_facturaArray[$i])
                        ->update([
                            'fkv_factura_id' => $id,
                            'fk_estado' => 66,
                        ]);

                        $contar++;
                    }

                    $liquidacion_facturado = DB::table('liquidacion_servicios')
                    ->where('id',$request->id_liquidado_servicio)
                    ->update([
                        'facturado'=>1
                    ]);

                    if ($contar>0) {

                        if(json_decode($response)->number==$orden_facturacion->numero_factura) {
                            $texts = "Se ha generado la factura N° ".$orden_facturacion->numero_factura." con éxito";
                        }else{
                            $texts = "¡Acción Inmediata! Se ha generado la factura, pero el consecutivo de Siigo no concuerda con el de AUTONET... N° Siigo: ".json_decode($response)->number." - N° AUTONET: ".$orden_facturacion->numero_factura."";
                        }

                        return Response::json([
                            'response' => true,
                            'message' => $texts,
                            'contar' => $contar,
                            'ordenes_facturacion' => $ordenes_facturacion,
                            'ultimo_id' => $ultimo_id,
                            'id' => $id,
                            'consecutivo' => json_decode($response)->number,
                            'numero_factura' => $orden_facturacion->numero_factura
                        ]);

                    }else{
                        return Response::json([
                            'respuesta'=>'error',
                        ]);
                    }

                }

            } catch (Exception $e) {

                $texts = "¡Se ha generado un error de conectividad entre Autonet y Siigo! El código del error es: ".json_decode($response)->Errors[0]->Code.'. Detalles del error: '.json_decode($response)->Errors[0]->Message;

                return Response::json([
                    'response'=> false,
                    'respons' => $response,
                    'code' => json_decode($response)->Errors[0]->Code,
                    'message' => $texts,//json_decode($response)->Errors[0]->Message,
                    'errores' => $e->getMessage(),
                    'totalfactura' => round($totalfactura, 2),
                    'valor' => $valor,
                    'ciudad' => $ciudad
                ]);

            }
        }
    }

    public function generarpdffactura(Request $request) {

        $id = $request->id;

        $factura = Ordenfactura::find($id);

        if($factura){

            //Generación de PDF
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://private-anon-3e8aca8745-siigoapi.apiary-proxy.com/v1/invoices/{".$factura->id_siigo."}/pdf");
            //curl_setopt($ch, CURLOPT_URL, "https://api.siigo.com/v1/invoices/{".$factura->id_siigo."}/pdf");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);

            $token = DB::table('siigo')->where('id',1)->first();
            $token = $token->token;

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: ".$token."",
                "Partner-Id: AUTONET"
            ));

            $response = curl_exec($ch);
            curl_close($ch);

            $bin = base64_decode(json_decode($response)->base64, true);

            $filepath = "images/facturacion/siigo/".$factura->id.".pdf";

            file_put_contents($filepath, $bin);

            $update = DB::table('ordenes_facturacion')
            ->where('id',$id)
            ->update([
                'pdf' => 1
            ]);

            if($update){

                return Response::json([
                    'response' => true
                ]);

            }else{

                return Response::json([
                    'response' => false
                ]);

            }

        }

    }

    public function listinvoices(Request $request) {
        
        $ordenes = DB::table('ordenes_facturacion')
        ->leftJoin('subcentrosdecosto', 'ordenes_facturacion.fk_subcentrodecosto', '=', 'subcentrosdecosto.id')
        ->leftJoin('centrosdecosto', 'ordenes_facturacion.fk_centrodecosto', '=', 'centrosdecosto.id')
        ->leftJoin('users', 'ordenes_facturacion.revisado_por', '=', 'users.id')
        ->leftJoin('ciudades', 'ciudades.id', '=', 'ordenes_facturacion.fk_ciudad')
        ->select('ordenes_facturacion.id', 'ordenes_facturacion.pdf', 'ordenes_facturacion.created_at', 'ordenes_facturacion.consecutivo','ordenes_facturacion.fk_ciudad', 'ordenes_facturacion.id_siigo', 'ordenes_facturacion.fk_centrodecosto',
            'ordenes_facturacion.fecha_expedicion', 'ordenes_facturacion.fecha_expedicion as fecha_registro','ordenes_facturacion.fecha_inicial', 'ordenes_facturacion.fecha_vencimiento', 'ordenes_facturacion.fecha_final',
            'ordenes_facturacion.dividida', 'ordenes_facturacion.fecha_ingreso', 'ordenes_facturacion.id_detalle',
            'ordenes_facturacion.numero_factura','ordenes_facturacion.total_facturado_cliente','ordenes_facturacion.fecha_factura',
            'ordenes_facturacion.total_otros_ingresos','ordenes_facturacion.anulado', 'ciudades.nombre as nombre_ciudad', 'ordenes_facturacion.total_costo', 'ordenes_facturacion.total_utilidad', 'ordenes_facturacion.total_otros_ingresos as otros_ingresos', 'ordenes_facturacion.observaciones',
            'ordenes_facturacion.tipo_orden','ordenes_facturacion.ingreso', 'ordenes_facturacion.rc', 'ordenes_facturacion.diferencia', 'ordenes_facturacion.nota_file', 'ordenes_facturacion.revision_ingreso', 'ordenes_facturacion.totalfactura',
            'centrosdecosto.razonsocial',
            'subcentrosdecosto.nombre')
        ->whereNull('nomostrar')
        ->orderBy('created_at', 'desc')
        //->orderBy('consecutivo', 'asc')
        ->get();

        //$query = "select ls.id, ls.consecutivo , ls.fecha_registro , ls.fecha_inicial , ls.fecha_final , ls.total_facturado_cliente , ls.total_costo , ls.total_utilidad , ls.otros_ingresos , ls.otros_costos , c.razonsocial , s.nombre as nombre_subcentro , c2.nombre as nombre_ciudad from liquidacion_servicios ls left join centrosdecosto c on c.id = ls.fk_centrodecosto left join subcentrosdecosto s on s.id = ls.fk_subcentrodecosto  left join ciudades c2 on c2.id  = ls.fk_ciudad WHERE ls.autorizado is null";
        //$ordenes = DB::select($query);

        if($ordenes) {

            return Response::json([
                'response' => true,
                'ordenes' => $ordenes
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function listtripsbyinvoice(Request $request) {

        $id = $request->id;

        /*$viajes = DB::table('facturacion_de_viajes')
        ->select('facturacion_de_viajes.observacion','facturacion_de_viajes.fk_viaje',
            'facturacion_de_viajes.total_cobrado','facturacion_de_viajes.total_pagado','facturacion_de_viajes.utilidad',
            'viajes.fecha_viaje','pago_proveedores.consecutivo','pago_proveedores.revisado',
            'vehiculos.placa','vehiculos.fk_tipo_vehiculo','vehiculos.marca','vehiculos.modelo','pago_proveedores.programado',
            'proveedores.razonsocial',
            'pagos.preparado','pagos.auditado','pagos.autorizado',
            'conductores.celular','conductores.primer_nombre', 'conductores.primer_apellido')
        ->leftJoin('viajes', 'facturacion_de_viajes.fk_viaje', '=', 'viajes.id')
        ->leftJoin('conductores', 'viajes.fk_conductor', '=', 'conductores.id')
        ->leftJoin('vehiculos', 'viajes.fk_vehiculo', '=', 'vehiculos.id')
        ->leftJoin('proveedores', 'viajes.fk_proveedor', '=', 'proveedores.id')
        ->leftJoin('pago_proveedores','facturacion_de_viajes.pago_proveedor_id','=','pago_proveedores.id')
        ->leftJoin('pagos','pago_proveedores.fkv_pago','=','pagos.id')
        ->where('fkv_factura_id',$id)
        ->orderBy('fk_viaje')
        ->get();*/

        $viajes = "SELECT
		v.*,
        c.razonsocial, 
        sub.nombre, 
        p.razonsocial as nombre_proveedor, 
        cond.primer_nombre, 
        cond.segundo_nombre, 
        cond.primer_apellido, 
        cond.segundo_apellido, 
        ciu.nombre as nombre_ciudad, 
        tras.nombre as nombre_traslado,
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        us.first_name, us.last_name, fac.total_cobrado, fac.total_pagado, fac.observacion, fac.utilidad, fac.liquidado_autorizado, 
        fac.fk_estado as estado_facturacion, fac.fk_viaje as numero_planilla, v.detalle_recorrido,
        esta.nombre as nombre_estado_facturacion, esta.codigo as codigo_estado_facturacion,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT JSON_ARRAYAGG(JSON_OBJECT('nombre', pax.nombre, 'celular', pax.celular)) FROM viajes v2 left join pasajeros_ejecutivos pax on pax.fk_viaje = v2.id where v2.id = v.id) as pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join subcentrosdecosto sub on sub.id = v.fk_subcentrodecosto
        left join proveedores p on p.id = v.fk_proveedor
        left JOIN conductores cond on cond.id = v.fk_conductor
        left join ciudades ciu on ciu.id = v.fk_ciudad
        left join traslados tras on tras.id = v.fk_traslado
        left join destinos d on d.fk_viaje = v.id 
        left join users us on us.id = v.creado_por
        left join facturacion_de_viajes fac on fac.fk_viaje = v.id
        left join estados est on est.id = v.fk_estado 
        left join estados esta on esta.id = fac.fk_estado 
        where fac.fkv_factura_id = ".$id."
        GROUP BY v.id order by fecha_viaje";

        $facturacion = DB::select($viajes);

        return Response::json([
            'response' => true,
            //'ordenes' => $ordenes_facturacion,
            'facturacion' => $facturacion
        ]);

    }

    public function listtripsbyof(Request $request) { //colocar novedades

        $id = $request->id;

        $viajes = "SELECT
		v.*,
        c.razonsocial, 
        sub.nombre, 
        p.razonsocial as nombre_proveedor, 
        cond.primer_nombre, 
        cond.segundo_nombre, 
        cond.primer_apellido, 
        cond.segundo_apellido, 
        ciu.nombre as nombre_ciudad, 
        tras.nombre as nombre_traslado,
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        us.first_name, us.last_name, fac.total_cobrado, fac.total_pagado, fac.observacion, fac.utilidad, fac.liquidado_autorizado, 
        fac.fk_estado as estado_facturacion, fac.fk_viaje as numero_planilla, v.detalle_recorrido,
        esta.nombre as nombre_estado_facturacion, esta.codigo as codigo_estado_facturacion,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT JSON_ARRAYAGG(JSON_OBJECT('nombre', pax.nombre, 'celular', pax.celular)) FROM viajes v2 left join pasajeros_ejecutivos pax on pax.fk_viaje = v2.id where v2.id = v.id) as pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join subcentrosdecosto sub on sub.id = v.fk_subcentrodecosto
        left join proveedores p on p.id = v.fk_proveedor
        left JOIN conductores cond on cond.id = v.fk_conductor
        left join ciudades ciu on ciu.id = v.fk_ciudad
        left join traslados tras on tras.id = v.fk_traslado
        left join destinos d on d.fk_viaje = v.id 
        left join users us on us.id = v.creado_por
        left join facturacion_de_viajes fac on fac.fk_viaje = v.id
        left join estados est on est.id = v.fk_estado 
        left join estados esta on esta.id = fac.fk_estado 
        where fac.liquidacion_id = ".$id."
        GROUP BY v.id order by fecha_viaje";

        $facturacion = DB::select($viajes);

        return Response::json([
            'response' => true,
            'facturacion' => $facturacion
        ]);

    }

    public function listinvoiceswithoutin(Request $request) {

        $select = "SELECT DISTINCT ordenes_facturacion.id, ordenes_facturacion.numero_factura, ordenes_facturacion.fecha_inicial, ordenes_facturacion.fecha_final, ordenes_facturacion.total_facturado_cliente, ordenes_facturacion.ingreso, pago_proveedores.id as id_ap, pagos.id as id_pago, centrosdecosto.razonsocial FROM ordenes_facturacion LEFT JOIN facturacion_de_viajes ON facturacion_de_viajes.fkv_factura_id =  ordenes_facturacion.id LEFT JOIN viajes ON viajes.id = facturacion_de_viajes.fk_viaje LEFT JOIN pago_proveedores ON pago_proveedores.id = facturacion_de_viajes.pago_proveedor_id LEFT JOIN pagos ON pagos.id = pago_proveedores.fkv_pago LEFT JOIN centrosdecosto ON centrosdecosto.id = ordenes_facturacion.fk_centrodecosto WHERE viajes.fecha_viaje BETWEEN '20240501' AND '20241031' AND ordenes_facturacion.ingreso IS NULL and ordenes_facturacion.anulado is null and pago_proveedores.id is not null";
        $select = DB::select($select);

        return Response::json([
            'response' => true,
            'ordenes' => $select
        ]);

    }

}
