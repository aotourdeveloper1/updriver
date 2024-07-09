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
use App\Models\Lote;
use App\Models\Pago;
use App\Models\PagoProveedor;
use App\Models\Prestamo;
use App\Models\PrestamoDetalle;

use Auth;
use Response;
Use DB;
Use Config;
use Hash;

class ContabilidadController extends Controller
{
    
    //prestamos start
    public function newloan(Request $request) {

        $valor = $request->valor;
        $fecha = $request->fecha;
        $proveedor_id = $request->proveedor_id;
        $concepto = $request->concepto;

        if( $request->anticipo==1 ) {
            $anticipo = 1;
        }else{
            $anticipo = null;
        }

        $consulta = DB::table('prestamos')
        ->where('fecha',$fecha)
        ->where('fk_proveedor',$proveedor_id)
        ->first();

        if($consulta!=null){

            return Response::json([
                'response' => false
            ]);

        }else{

            $new = new Prestamo();
            $new->valor_prestado = $valor;
            $new->fecha = $fecha;
            $new->fk_estado = 71;
            $new->fk_proveedor = $proveedor_id;
            $new->anticipo = $anticipo;
            $new->creado_por = Auth::user()->id;

            if($new->save()){

                $detalle = new PrestamoDetalle;
                $detalle->valor = $request->valor;
                $detalle->concepto = strtoupper($request->concepto);
                $detalle->fk_user = Auth::user()->id;
                $detalle->created_at = Date('Y-m-d H:i');
                $detalle->fk_prestamo  = $new->id;
                $detalle->save();

                return Response::json([
                    'response' => true
                ]);
            }

        }

    }

    public function listloan(Request $request) {

        $prestamos = "select p.*, pro.razonsocial, est.nombre as nombre_estado, u.first_name, u.last_name, pa.procesado, pa.auditado from prestamos p left join proveedores pro on pro.id = p.fk_proveedor left join estados est on est.id = p.fk_estado left join users u on u.id = p.creado_por left join pagos pa on pa.id = p.fkv_pago where p.legalizado = 0 and (p.anticipo is null or p.notificado is not null)";
        $prestamos = DB::select($prestamos);

        if($prestamos) {

            return Response::json([
                'response' => true,
                'prestamos' => $prestamos
            ]);

        }else{

            return Response::json([
                'response' => false,
                'prestamos' => $prestamos
            ]);

        }

    }

    public function addloantoexist(Request $request) {

        $valor = $request->valor;
        $fecha = $request->fecha;
        $prestamo_id = $request->prestamo_id;
        $concepto = $request->concepto;

        $query = Prestamo::find($prestamo_id);

        if(isset($query)) {

            if($query->fk_estado==72) { //prestamo con AP
                
                return Response::json([
                    'response' => false,
                    'message' => 'Este préstamo no se puede modificar porque ya tiene AP'
                ]);

            }else{

                if($query!=null){

                    $detalle = new PrestamoDetalle;
                    $detalle->valor = $request->valor;
                    $detalle->concepto = strtoupper($request->concepto);
                    $detalle->fk_user = Auth::user()->id;
                    $detalle->created_at = Date('Y-m-d H:i');
                    $detalle->fk_prestamo  = $prestamo_id;
                    $detalle->save();
        
                    $query->valor_prestado = $query->valor_prestado+$valor;
        
                    if($query->save()){
        
                        return Response::json([
                            'response' => true,
                            'query' => $query
                        ]);
        
                    }
        
                }else{
        
                    return Response::json([
                        'response' => false,
                        'message' => 'Este préstamo no fue encontrado en el sistema.'
                    ]);
        
                }
            }

        }else{

            return Response::json([
                'response' => false,
                'message' => 'Este préstamo no fue encontrado en el sistema. Actuliza la vista y verifica que el préstamo esté en el listado.'
            ]);

        }

    }

    public function listitemsbyloan(Request $request) {

        $id = $request->prestamo_id;

        $query = DB::table('prestamos_detalles')
        ->leftjoin('users', 'users.id', '=', 'prestamos_detalles.fk_user')
        ->select('prestamos_detalles.*', 'users.first_name', 'users.last_name')
        ->where('fk_prestamo',$id)
        ->get();

        if($query){

            return Response::json([
                'response' => true,
                'prestamo' => $query,
                'id' => $id
            ]);

        }else{

            return Response::json([
                'response' => false,
                'prestamo' => $query,
                'id' => $id
            ]);

        }

    }

    //Editar valores de un item de préstamo
    public function edititemloan(Request $request) {

        $item = DB::table('prestamos_detalles')
        ->where('id',$request->id)
        ->first();

        $prestamo = Prestamo::find($item->fk_prestamo);

        if($prestamo->fk_estado==72) { //prestamo con AP
            
            return Response::json([
                'response' => false,
                'message' => 'Este préstamo no se puede modificar porque ya tiene AP'
            ]);

        }else{

            if($request->valor!=null and $request->concepto!=null) {

                $items = DB::table('prestamos_detalles')
                ->where('id',$request->id)
                ->update([
                    'valor' => $request->valor,
                    'concepto' => $request->concepto
                ]);

                $valorViejo = intval($item->valor);
                $resta = intval($prestamo->valor_prestado)-intval($valorViejo);

                $prestamo->valor_prestado = $resta+$request->valor;
                $prestamo->save();
    
            }else if($request->valor!=null or $request->concepto!=null) {
    
                if($request->valor!=null) {
    
                    $item = DB::table('prestamos_detalles')
                    ->where('id',$request->id)
                    ->update([
                        'valor' => $request->valor,
                    ]);

                    $valorViejo = intval($item->valor);
                    $resta = intval($prestamo->valor_prestado)-intval($valorViejo);

                    $prestamo->valor_prestado = $resta+$request->valor;
                    $prestamo->save();

                    //log de cambios
    
                }
                
                if($request->concepto!=null){
    
                    $item = DB::table('prestamos_detalles')
                    ->where('id',$request->id)
                    ->update([
                        'concepto' => $request->concepto
                    ]);
                    //log de cambios
    
                }
                
    
            }
            
    
            return Response::json([
                'response' => true
            ]);

        }

    }

    public function deleteitemloan(Request $request) {

        $id = $request->id;

        $item = PrestamoDetalle::find($id);
        $valor = $item->valor;
        $prestamo = Prestamo::find($item->fk_prestamo);

        if($prestamo->fk_estado==72) { //prestamo con AP
            
            return Response::json([
                'response' => false,
                'message' => 'Este préstamo no se puede modificar porque ya tiene AP'
            ]);

        }else{

            DB::table('prestamos_detalles')
            ->where('id',$id)
            ->delete();

            $detalles = DB::table('prestamos_detalles')
            ->where('id',$id)
            ->delete();

            if($item->delete()) {

                $nuevoValor = $prestamo->valor_prestado-$valor;
                $prestamo->valor_prestado = $nuevoValor;
                $prestamo->save();

                return Response::json([
                    'response' => true,
                ]);

            }else{

                return Response::json([
                    'response' => false,
                    'message' => 'No se pudo eliminar el ítem. Intentalo de nuevo. Si persiste la novedad, comunícate con el administrador del sistema.'
                ]);

            }

        }

    }

    //cambiar el prestamo completo de proveedor ... 27 junio
    public function changeproviderloan(Request $request) {

    }

    //cambiar un item de prestamo ... 27 junio
    public function changeitemofloan(Request $request) {
        
    }
    //prestamos end
    
    public function searchap(Request $request) {

        $fecha_pago = $request->fecha;
        $proveedores = $request->proveedor;

        $consulta = "select pago_proveedores.id, pago_proveedores.consecutivo, pago_proveedores.numero_factura, ".
            "pago_proveedores.fecha_expedicion, pago_proveedores.fecha_inicial, pago_proveedores.fecha_final, pago_proveedores.fecha_pago, ".
            "pago_proveedores.valor,pago_proveedores.fecha_revisado, pago_proveedores.revisado, pago_proveedores.programado, pago_proveedores.anulado, pago_proveedores.motivo_anulacion, ".
            "centrosdecosto.razonsocial, subcentrosdecosto.nombre, proveedores.id as proveedorid, proveedores.razonsocial as proveedor, ".
            "users1.first_name as creado_first_name, users1.last_name as creado_last_name, users2.first_name as revisado_first_name, ".
            "users2.last_name as revisado_last_name ".
            "from pago_proveedores ".
            "left join users as users1 on pago_proveedores.creado_por = users1.id ".
            "left join users as users2 on pago_proveedores.revisado_por = users2.id ".
            "left join proveedores on pago_proveedores.fk_proveedor = proveedores.id ".
            "left join centrosdecosto on pago_proveedores.fk_centrodecosto = centrosdecosto.id ".
            "left join subcentrosdecosto on pago_proveedores.fk_subcentrodecosto = subcentrosdecosto.id ";
        
        $ap = DB::select($consulta."where pago_proveedores.fecha_pago = '".$fecha_pago."' and pago_proveedores.fk_proveedor = ".$proveedores." order by `consecutivo` asc");

        $pago_mostrar = DB::table('pagos')
        ->select('pagos.id','pagos.fk_proveedor','pagos.fecha_pago','pagos.fecha_registro','pagos.total_pagado','pagos.descuento_retefuente',
            'pagos.total_neto','pagos.usuario','users.first_name','users.last_name','pagos.procesado',
            'pagos.auditado','pagos.autorizado', 'pagos.descuento_prestamo')
        ->leftJoin('users','pagos.usuario','=','users.id')
        ->where('pagos.fk_proveedor',$proveedores)
        ->where('pagos.fecha_pago',$fecha_pago)
        ->first();

        if($pago_mostrar) {

            return Response::json([
                'response' => true,
                'ap' => $ap,
                'pago' => $pago_mostrar
            ]);

        }else{

            return Response::json([
                'response' => true,
                'ap' => $ap,
                'pago' => null
            ]);

        }

    }

    public function listapbypayment(Request $request) {

        $id = $request->id;

        $consulta = "select pago_proveedores.id, pago_proveedores.consecutivo, pago_proveedores.numero_factura, ".
            "pago_proveedores.fecha_expedicion, pago_proveedores.fecha_inicial, pago_proveedores.fecha_final, pago_proveedores.fecha_pago, ".
            "pago_proveedores.valor,pago_proveedores.fecha_revisado, pago_proveedores.revisado, pago_proveedores.programado, pago_proveedores.anulado, pago_proveedores.motivo_anulacion, ".
            "centrosdecosto.razonsocial, subcentrosdecosto.nombre, proveedores.id as proveedorid, proveedores.razonsocial as proveedor, ".
            "users1.first_name as creado_first_name, users1.last_name as creado_last_name, users2.first_name as revisado_first_name, ".
            "users2.last_name as revisado_last_name ".
            "from pago_proveedores ".
            "left join users as users1 on pago_proveedores.creado_por = users1.id ".
            "left join users as users2 on pago_proveedores.revisado_por = users2.id ".
            "left join proveedores on pago_proveedores.fk_proveedor = proveedores.id ".
            "left join centrosdecosto on pago_proveedores.fk_centrodecosto = centrosdecosto.id ".
            "left join subcentrosdecosto on pago_proveedores.fk_subcentrodecosto = subcentrosdecosto.id ";
        
        $ap = DB::select($consulta."where pago_proveedores.fkv_pago = '".$id."' order by `consecutivo` asc");

        $pago_mostrar = DB::table('pagos')
        ->select('pagos.id','pagos.fk_proveedor','pagos.fecha_pago','pagos.fecha_registro','pagos.total_pagado','pagos.descuento_retefuente',
            'pagos.total_neto','pagos.usuario','users.first_name','users.last_name','pagos.procesado',
            'pagos.auditado','pagos.autorizado', 'pagos.descuento_prestamo')
        ->leftJoin('users','pagos.usuario','=','users.id')
        ->where('pagos.id',$id)
        ->first();

        if($pago_mostrar) {

            return Response::json([
                'response' => true,
                'ap' => $ap,
                'pago' => $pago_mostrar
            ]);

        }else{

            return Response::json([
                'response' => true,
                'ap' => $ap,
                'pago' => null
            ]);

        }

    }

    public function listtripsbyap(Request $request) {

        $id = $request->id;

        $viajes = DB::table('facturacion_de_viajes')
        ->select('viajes.id','viajes.fecha_viaje', 'viajes.detalle_recorrido', 'facturacion_de_viajes.observacion',
            'facturacion_de_viajes.fk_viaje', 'facturacion_de_viajes.total_pagado', 'pago_proveedores.fecha_pago',
            'pago_proveedores.numero_factura as pfactura', 'ordenes_facturacion.numero_factura', 'ordenes_facturacion.consecutivo',
            'ordenes_facturacion.ingreso', 'ordenes_facturacion.fecha_expedicion','ordenes_facturacion.id as id_orden_factura')
        ->leftJoin('viajes', 'facturacion_de_viajes.fk_viaje', '=', 'viajes.id')
        ->leftJoin('ordenes_facturacion', 'facturacion_de_viajes.fkv_factura_id', '=', 'ordenes_facturacion.id')
        ->leftJoin('pago_proveedores', 'facturacion_de_viajes.pago_proveedor_id','=', 'pago_proveedores.id')
        ->where('facturacion_de_viajes.pago_proveedor_id', $id)
        ->get();

        $pago_proveedores = DB::table('pago_proveedores')
        ->select('centrosdecosto.razonsocial as centrodecostonombre', 'proveedores.razonsocial as proveedornombre',
            'pago_proveedores.id as fkv_pago','pago_proveedores.consecutivo',
            'users.first_name','users.last_name','pago_proveedores.revisado','pagos.procesado',
            'pago_proveedores.fecha_expedicion', 'pago_proveedores.numero_factura','pago_proveedores.fecha_inicial', 'pago_proveedores.observaciones',
            'subcentrosdecosto.nombre','pago_proveedores.valor','pago_proveedores.fecha_final','pago_proveedores.fecha_pago')
        ->leftJoin('centrosdecosto', 'pago_proveedores.fk_centrodecosto', '=', 'centrosdecosto.id')
        ->leftJoin('proveedores', 'pago_proveedores.fk_proveedor', '=', 'proveedores.id')
        ->leftJoin('subcentrosdecosto', 'pago_proveedores.fk_subcentrodecosto', '=', 'subcentrosdecosto.id')
        ->leftJoin('users', 'pago_proveedores.creado_por', '=', 'users.id')
        ->leftJoin('pagos', 'pago_proveedores.fkv_pago', '=', 'pagos.id')
        ->where('pago_proveedores.id',$id)
        ->first();

        return Response::json([
            'response' => true,
            'viajes' => $viajes,
            'ap' => $pago_proveedores
        ]);

    }

    public function haspayment(Request $request) {

        $proveedores = $request->proveedor;
        $fecha_pago = $request->fecha;

        $pago_mostrar = DB::table('pagos')
        ->select('pagos.id','pagos.fk_proveedor','pagos.fecha_pago','pagos.fecha_registro','pagos.total_pagado','pagos.descuento_retefuente',
            'pagos.total_neto','pagos.usuario','users.first_name','users.last_name','pagos.procesado',
            'pagos.auditado','pagos.autorizado', 'pagos.descuento_prestamo')
        ->leftJoin('users','pagos.usuario','=','users.id')
        ->where('pagos.fk_proveedor',$proveedores)
        ->where('pagos.fecha_pago',$fecha_pago)
        ->first();

        if($pago_mostrar) {
            
            return Response::json([
                'response' => true,
                'pago' => $pago_mostrar
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function reviseap(Request $request) {

        $revisar = PagoProveedor::find($request->id);
        $revisar->revisado = 1;
        $revisar->fecha_revisado = date('Y-m-d H:i:s');
        $revisar->revisado_por = Auth::user()->id;
        $revisar->save();

        if ($revisar->save()) {

            return Response::json([
                'response' => true
            ]);

        }

    }

    public function newpayment(Request $request) {

        //VALIDAR SI YA EXISTE UN PAGO DE ESTE PROVEEDOR PARA ESTA FECHA
        $validar = DB::table('pagos')
        ->where('fecha_pago', $request->fecha)
        ->where('fk_proveedor',$request->proveedor)
        ->get();

        if (count($validar)>0) {

            return Response::json([
                'response' => false,
                'message' => 'Existe un pago para este proveedor en la fecha seleccionada'
            ]);

        }else{

            /*$idArrayPrestamo = (explode(',', Input::get('idArrayPrestamo')));
            $valorArrayAbonos = (explode(',', Input::get('valorArrayAbonos')));

            $contar=0;
            $valor_id = '';
            $valor_valor = '';

            for ($i=0; $i <count($idArrayPrestamo); $i++) {

                $valor_actual = DB::table('prestamos')
                ->where('id',$idArrayPrestamo[$i])
                ->pluck('valor_prestado');

                $abonarr = DB::table('prestamos')
                ->where('id',$idArrayPrestamo[$i])
                ->update([
                'abono' => intval($valor_actual)-intval($valorArrayAbonos[$i]),
                'sw_abono' => 1
                ]);

            }*/

            $pago = new Pago;
            $pago->fk_proveedor = $request->proveedor;
            $pago->fecha_registro = date('Y-m-d H:i:s');
            $pago->fecha_pago = $request->fecha;
            $pago->total_pagado = $request->total_cuenta;
            $pago->descuento_retefuente = $request->total_retefuente;;
            $pago->fkv_lote = $request->lote_id;

            /*if( $request->total_prestamos>0 ){
                $pago->descuento_prestamo = $request->total_prestamos;
            }*/

            $pago->total_neto = $request->total_pagado;
            $pago->usuario = Auth::user()->id;

            if ($pago->save()) {

                //Actualizar Lote
                $lote = Lote::find($request->lote_id);
                $valorAntiguo = $lote->valor;
                $lote->valor = intval($valorAntiguo)+intval($pago->total_neto);
                $lote->save();

                $variable = null;

                //DB::update("update prestamos set id_pago = ".$pago->id.", estado_prestamo = 1, detalles = '".$texto."' where proveedor_id = ".Input::get('proveedor')." and estado_prestamo = 0 and fecha='".$pago->fecha_pago."'");
                //hacer un for para actualizar el fk_pago de los préstamos
                //DB::update("update prestamos set fkv_pago = ".$pago->id.", fk_estado = 72 where id in(".Input::get('prestamos').") ");

                $pagoProveedores = $request->ap_id;

                for ($l=0; $l <count($pagoProveedores) ; $l++) {
                    DB::update("update pago_proveedores set fkv_pago = ".$pago->id.", programado = 1 where anulado is null and id in (".$pagoProveedores[$l].")");
                }

                $ano = date('Y');
                $mes = date('m');

                $dates = $ano.$mes.'01';
                $dates2 = $ano.$mes.'31';

                $cantidad = DB::table('pago_proveedores')
                ->whereBetween('fecha_pago',[$dates,$dates2])
                ->whereNull('programado')
                ->get();

                $cantidad = count($cantidad);

                return Response::json([
                    'response' => true,
                    'message' => 'AP pendientes para este mes: '.$cantidad
                ]);

            }else{

                return Response::json([
                    'response' => false,
                ]);

            }
        }
    }

    public function newlot(Request $request) {

        try {

            $lote = new Lote;
            $lote->nombre = $request->nombre;
            $lote->fecha = $request->fecha;
            $lote->creado_por = Auth::user()->id;
            $lote->fk_estado = 67;
            $lote->created_at = date('Y-m-d Hi');
            $lote->save();

            return Response::json([
                'response' => true
            ]);

        } catch (Exception $e) {

            return Response::json([
              'respuesta' => 'error'
            ]);
      
        }

    }

    public function listlots(Request $request) {

        $lotes = DB::table('lotes')
        ->leftjoin('estados', 'estados.id', '=', 'lotes.fk_estado')
        ->leftjoin('users', 'users.id', '=', 'lotes.creado_por')
        ->select('lotes.*', 'estados.nombre as nombre_estado', 'users.first_name', 'users.last_name')
        ->where('fk_estado', $request->estado)
        ->get();

        if($lotes) {

            return Response::json([
                'response' => true,
                'lotes' => $lotes
            ]);

        }else{

            return Response::json([
                'response' => false,
                'lotes' => $lotes
            ]);

        }

    }

    public function listpayments(Request $request) {

        $id = $request->id;

        $pagos = DB::table('pagos')
        ->leftJoin('proveedores', 'proveedores.id', '=', 'pagos.fk_proveedor')
        ->leftJoin('users', 'users.id', '=', 'pagos.usuario')
        ->select('pagos.*', 'proveedores.razonsocial', 'users.first_name', 'users.last_name')
        ->where('fkv_lote', $id)
        ->get();

        if(count($pagos)>0) {

            return Response::json([
                'response' => true,
                'pagos' => $pagos
            ]);

        }else{

            return Response::json([
                'response' => false,
                'pagos' => $pagos
            ]);

        }

    }

    public function changestatus(Request $request) {

        $lote = DB::table('lotes')
        ->where('id',$request->id)
        ->update([
            'fk_estado' => $request->estado
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function deletelot(Request $request) {

        $id = $request->id;
        $nombre = $request->nombre;

        $query = "DELETE FROM lotes WHERE id = ".$id."";
        $delete = DB::delete($query);

        if($delete) {

            return Response::json([
                'response' => true,
                'nombre' => $nombre
            ]);

        }

    }

    //...
    public function findlots(Request $request) {

        $fecha = $request->fecha;
        $lote = $request->lote_id;

        $buscar = DB::table('lotes')
        ->where('fecha',$fecha)
        ->where('id','!=', $lote)
        ->where('fk_estado',69)
        ->first();

        if($buscar!=null) {

            return Response::json([
                'response' => true,
                'lote' => $buscar
            ]);
            
        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    //...
    public function changeexistslot(Request $request) {

        $id = $request->id;
        $fecha = $request->fecha;
        $id_lote = $request->lote_id;

        try {

            $pago = Pago::find($id);

            $lote = Lote::find($id_lote);
            $lote->fk_estado = 69;
            $lote->procesado_por = Auth::user()->id;
            $lote->valor = intval($lote->valor)+intval($pago->total_neto);

            if($lote->save()) { //Actualizar lote existente

                $loteSearch = Lote::find($pago->id_lote);
                $loteSearch->valor = (intval($loteSearch->valor)-intval($pago->total_neto));
                $loteSearch->save(); //Actualización del valor total al lote antiguo del pago

                $pago->id_lote = $lote->id;
                $pago->preparado = 1;
                $pago->preparado_por = Sentry::getUser()->id;
                $pago->fecha_preparacion = $fecha;
                $pago->save(); //Actualizar el id del lote al pago del lote creado

                return Response::json([
                    'response' => true,
                    'nombre' => $lote->nombre
                ]);

            }

        } catch (Exception $e) {

            return Response::json([
                'respuesta' => 'error',
                'error' => $e->getMessage()
            ]);

        }

    }

    //consultar los detalles del proveedor que se va a procesar el pago
    public function consults(Request $request) {

        $id = $request->id;

        $consulta = DB::table('pagos')
        ->leftJoin('proveedores', 'proveedores.id', '=', 'pagos.fk_proveedor')
        ->leftJoin('cuenta_de_cobro', 'cuenta_de_cobro.id', '=', 'pagos.cuenta_de_cobro')
        ->select('pagos.*', 'cuenta_de_cobro.seguridad_social', 'proveedores.razonsocial')
        ->where('pagos.id',$id)
        ->first(); //traer la cuenta bancaria del proveedor - PENDING

        $prestamos = DB::table('prestamos')
        ->where('fk_estado',71) //SIN AP
        ->where('fk_proveedor',$consulta->fk_proveedor)
        ->get();

        if($prestamos){
            $totalPrestamos = count($prestamos);
            $messagePrestamos = 'Este proveedor tiene '.$totalPrestamos.' préstamos sin descontar';
        }else{
            $totalPrestamos = 0;
            $messagePrestamos = 'Este proveedor no tiene ningún préstamo pendiente por descontar';
        }

        $select = "SELECT DISTINCT ordenes_facturacion.id, ordenes_facturacion.numero_factura, ordenes_facturacion.fecha_inicial, ordenes_facturacion.fecha_final, ordenes_facturacion.total_facturado_cliente, ordenes_facturacion.ingreso, pago_proveedores.id as id_ap, pagos.id as id_pago, centrosdecosto.razonsocial FROM ordenes_facturacion LEFT JOIN facturacion_de_viajes ON facturacion_de_viajes.fkv_factura_id =  ordenes_facturacion.id LEFT JOIN viajes ON viajes.id = facturacion_de_viajes.fk_viaje LEFT JOIN pago_proveedores ON pago_proveedores.id = facturacion_de_viajes.pago_proveedor_id LEFT JOIN pagos ON pagos.id = pago_proveedores.fkv_pago LEFT JOIN centrosdecosto ON centrosdecosto.id = ordenes_facturacion.fk_centrodecosto WHERE viajes.fecha_viaje BETWEEN '20230801' AND '20241231' AND ordenes_facturacion.ingreso IS NULL and pago_proveedores.id is not null and pagos.id = ".$id." ";

        $query = DB::select($select);

        if($query){
            $totalFacturas = count($query);
            $messageFacturas = 'Este pago tiene '.$totalFacturas.' facturas que no tienen ingreso!';
        }else{
            $totalFacturas = 0;
            $messageFacturas = 'Todas las facturas de este pago tienen ingreso!';
        }

        if($consulta->descuento_retefuente>0) {
            $messageRetefuente = 'Este pago tiene descuento retefuente por valor de: $'.number_format($consulta->descuento_retefuente);
        }else{
            $messageRetefuente = 'Este pago no tiene descuento retefuente';
        }

        $messageSs1 = 'Este pago NO cuenta con Seguridad Social';
        $messageSs2 = '¿Deseas procesar este pago sin este documento?';
        $messageSs3 = 'Recuerda que es obligatoria para el proceso de pago...';

        $code = "SELECT cb.id as id_cuenta_bancaria, p.razonsocial , p.id as id_proveedor , cb.fk_tipo_cuenta , cb.numero_cuenta , cb.fk_banco , cb.certificacion_pdf , cb.poder_pdf , b.nombre , t.nombre as nombre_tipo_cuenta  FROM  cuenta_bancaria cb left join bancos b ON b.id = cb.fk_banco LEFT JOIN tipos t on t.id = cb.fk_tipo_cuenta left join proveedores p on p.fk_cuenta_bancaria = cb.id where p.id = ".$consulta->fk_proveedor."";
        $data = DB::select($code);

        return Response::json([
            'response' => true,
            'cuentaBancaria' => $data,
            'pago' => $consulta,
            'messageFacturas' => $messageFacturas,
            'messageSs1' => $messageSs1,
            'messageSs2' => $messageSs2,
            'messageSs3' => $messageSs3,
            'messagePrestamos' => $messagePrestamos,
            'messageRetefuente' => $messageRetefuente
        ]);

    }

    //procesar pagos betty carrillo
    public function proccesspayment(Request $request) {

        $pagos = $request->id;
        $sw = 0;

        for ($e=0; $e <count($pagos) ; $e++) { 
            
            $cn = "update pagos set procesado = 1, comentario = '".strtoupper($request->comentario)."', fecha_preparacion = '".$request->fecha."', fecha_estimada = '".$request->fecha."', procesado_por = ".Auth::user()->id." where id in (".$pagos[$e].")";
            $update = DB::update($cn);
            $sw++;

        }

        if ($sw>0) {

            $sql = DB::table('pagos')
            ->where('fkv_lote',$request->lote_id)
            ->whereNull('procesado')
            ->get();

            $total = count($sql);

            if( $total>0 ) {

                return Response::json([
                    'response' => true,
                    'pendientes' => $total
                ]);

            }else{

                $message = 'Pago procesado con éxito!';

                $updateLote = DB::table('lotes')
                ->where('id',$request->lote_id)
                ->update([
                    'fk_estado' => 69,
                    'procesado_por' => Auth::user()->id,
                    'message' => $message
                ]);

                $message = 'Todos los pagos de este lote han sido procesados. Este lote pasará automáticamente a PENDIENTE POR APROBACIÓN DE GERENCIA.';

                return Response::json([
                    'response' => true,
                    'pendientes' => 0,
                    'message' => $message
                ]);

            }

        }else{

            return Response::json([
                'response' => false,
                'message' => 'No has seleccionado ningún pago. Intenta recargar la página para que verifiques si hay pagos en este lote...'
            ]);

        }

    }
    //aprobar pagos david coba
    public function approvepayments(Request $request) {

        $fecha_pago_real = $request->fecha_pago_real;

        $pagos = $request->pagos;
        $sw = 0;
        
        for ($a=0; $a <count($pagos) ; $a++) {

            $sw++;
            $update = DB::update("update pagos set autorizado = 1, fecha_preparacion = '".$fecha_pago_real."', autorizado_por = ".Auth::user()->id." where id in (".$pagos[$a].")");
            $updatePrestamo = DB::update("update prestamos set legalizado = 1 where fkv_pago in (".$pagos[$a].")");

        }

        if ($sw>0) {

            $proveedores = $request->pagos;

            for($i=0; $i<count($proveedores); $i++){

                $pago = Pago::find($proveedores[$i]);// DB::table('pagos')->where('id',)->first();

                $payment = DB::table('prestamos')
                ->where('fkv_pago',$pago->id)
                ->whereNotNull('anticipo')
                ->first();

                if( $payment!=null ) { //no notificar

                }else{ //modificar el celular del proveedor a mi número para poder probar

                    $proveedor = Proveedor::find($pago->fk_proveedor);// DB::table('proveedores')->where('id',$pago->fk_proveedor)->pluck('razonsocial');

                    $response = Proveedor::NotificarPago($proveedor->celular, $proveedor->razonsocial);

                }

            }

            $sql = DB::table('pagos')
            ->where('fkv_lote', $request->lote_id)
            ->whereNull('autorizado')
            ->get();

            $total = count($sql);

            if( $total>0 ) {

                return Response::json([
                    'response' => true,
                    'message' => 'Pagos aprobados, pero quedaron '.$total.' pendientes de aprobar...',
                    'pendientes' => $total,
                    'lote' => $request->lote_id
                ]);

            }else{

                $searcLote = Lote::find($request->lote_id);
                $fecha = $searcLote->fecha;

                $updateLote = DB::table('lotes')
                ->where('id',$request->lote_id)
                ->update([
                    'fk_estado' => 70,
                    'aprobado_por' => Auth::user()->id,
                    'fecha' => $fecha_pago_real,
                    'fecha_inicial' => $fecha
                ]);

                return Response::json([
                    'response' => true,
                    'message' => '¡Se ha aprobado el lote por completo!'
                ]);

            }

        }

    }
}
