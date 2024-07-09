<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Viaje;
use App\Models\Destino;
use App\Models\PasajeroEjecutivo;
use App\Models\Gps;
use App\Models\NovedadViaje;
use App\Models\Conductor;
use App\Models\PasajeroRutaQr;
use Auth;
use Response;
Use DB;
Use Config;
use Hash;

class ViajesController extends Controller
{

    public function viajesporentendido(Request $request) {

        $id = intval($request->id);

        $fecha = date('Y-m-d');
        $diaanterior = strtotime ('-1 day', strtotime($fecha));
        $diaanterior = date ('Y-m-d' , $diaanterior);

        $diasiguiente = strtotime ('+1 day', strtotime($fecha));
        $diasiguiente = date('Y-m-d' , $diasiguiente);

        $consulta = "SELECT
		v.id,
		v.fk_estado,
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
		v.fecha_viaje as fecha_servicio,
		v.hora_viaje as hora_servicio,
        v.detalle_recorrido,
        c.razonsocial,  
        v.cantidad,
        v.tipo_traslado,
        t.nombre as nombre_tipo_traslado,
        t.codigo as codigo_tipo_traslado,
        v.tipo_ruta,
        t2.nombre as tipo_de_ruta,
        t2.codigo as codigo_tipo_ruta,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        left join tipos t on t.id = v.tipo_traslado 
        left join tipos t2 on t2.id = v.tipo_ruta
        WHERE `fk_conductor` = ".$id." AND v.fecha_viaje between '".$diaanterior."' and '".$diasiguiente."' AND v.fk_estado = 57 and v.estado_eliminacion is null and v.estado_papelera is null
        GROUP BY v.id order by v.fecha_viaje asc, v.hora_viaje asc";

        $viajes = DB::select($consulta);

        //update servicios vencidos
        $feecha = date('Y-m-d');
        $hacequincedias = strtotime ('-15 day', strtotime($feecha));
        $hacequincedias = date ('Y-m-d' , $hacequincedias);

        $ayer = strtotime ('-1 day', strtotime($feecha));
        $ayer = date ('Y-m-d' , $ayer);

        $servicio_activo = DB::table('viajes')
        ->select('id', 'fk_conductor', 'fecha_viaje', 'fk_estado')
        ->where('fk_conductor', $id)
        ->whereBetween('fecha_viaje', [$diaanterior, $diasiguiente])
        ->where('fk_estado',59)
        ->first();

        if($servicio_activo) {
            $viajeActivo = $servicio_activo->id;
        }else{
            $viajeActivo = null;
        }

        if ($viajes) {

            return Response::json([
                'response' => true,
                'servicios' => $viajes,
                'viaje_activo' => $viajeActivo,
                'id_conductor' => $id
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function proximosviajes(Request $request) {

        $conductor_id = $request->conductor_id;

        $fecha = date('Y-m-d');
        $diaanterior = strtotime ('-1 day', strtotime($fecha));
        $diaanterior = date ('Y-m-d' , $diaanterior);

        $diasiguiente = strtotime ('+1 day', strtotime($fecha));
        $diasiguiente = date('Y-m-d' , $diasiguiente);

        $consulta = "SELECT
		v.id,
		v.fk_estado,
		v.detalle_recorrido,
		v.fecha_viaje,
		v.hora_viaje,
        c.razonsocial,  
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        WHERE `fk_conductor` = ".$conductor_id." AND v.fecha_viaje between '".$diaanterior."' and '".$diasiguiente."' AND v.fk_estado = 58 and v.estado_eliminacion is null and v.estado_papelera is null
        GROUP BY v.id order by v.fecha_viaje asc, v.hora_viaje asc";

        $viajes = DB::select($consulta);

        $servicio_activo = DB::table('viajes')
        ->select('id', 'fk_conductor', 'fecha_viaje', 'fk_estado')
        ->whereBetween('fecha_viaje', [$diaanterior, $diasiguiente])
        ->where('fk_conductor', $conductor_id)
        ->where('fk_estado', 59)
        ->first();

        if ($viajes) {

            if($servicio_activo) {
                $idViaje = $servicio_activo->id;
            }else{
                $idViaje = null;
            }

            return Response::json([
                'response' => true,
                'viajes' => $viajes,
                'servicio_activo' => $idViaje
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function servicioentendido(Request $request) {

        $id = $request->viaje_id;

        $servicioaceptado = DB::table('viajes')
        ->where('id', $id)
        ->update([
            'fk_estado' => 58
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function listarpasajeros(Request $request) {

        $viaje_id = $request->viaje_id;

        $pasajeros = "SELECT pr.nombre, pr.celular, pr.direccion, pr.barrio, pr.localidad, pr.estado_ruta, t.nombre as nombre_estado FROM pasajeros_rutas_qr pr left join tipos t on t.id = pr.estado_ruta where fk_viaje = ".$viaje_id."";
        $pasajeros = DB::select($pasajeros);

        if (count($pasajeros)){

            return Response::json([
                'response' => true,
                'usuarios' => $pasajeros
            ]);

        }else{

            return Response::json([
                'response' => false,
                'usuarios' => $pasajeros
            ]);

        }

    }

    public function escanearqr(Request $request) {

        $codigo = $request->codigo;
        $id = $request->id;

        $pasajero = DB::table('pasajeros_rutas_qr')
        ->select('id', 'estado_ruta', 'nombre')
        ->where('id', $id)
        ->where('code', $codigo)
        ->first();

        if(isset($pasajero)) {

            if($pasajero->estado_ruta==87){

                return Response::json([
                    'response' => false,
                    'message' => 'El pasajero '.$pasajero->nombre.' ya fue escaneado o registrado como transortado!'
                ]);
    
            }else{
                
                $update = DB::table('pasajeros_rutas_qr')
                ->where('id',$id)
                ->update([
                    'estado_ruta' => 87
                ]);

                return Response::json([
                    'response' => true,
                    'nombre' => $pasajero->nombre,
                    'message' => 'Pasajero escaneado exitosamente!'
                ]);

            }

        }else{

            return Response::json([
                'response' => false,
                'message' => '¡El pasajero escaneado no pertenece a esta ruta!'
            ]);

        }

    }

    public function iniciarviaje(Request $request) {

        $id = $request->viaje_id;
        $nombreConductor = $request->nombre_conductor;

        $viaje = Viaje::find($id);

        $horaServicio = $viaje->hora_viaje;

        $horaActual = date('H:i');
        $fechaActual = date('Y-m-d');
        $horaMenosseis = date('H:i',strtotime('+360 minute',strtotime($horaServicio)));
        $horaMenostres = date('H:i',strtotime('+180 minute',strtotime($horaServicio)));

        if(2>1){ //Validación para servicio vencido

            $viaje->hora_inicio = date('Y-m-d H:i:s');
            $viaje->fk_estado = 59; //servicio iniciado
            
            //gps de conductores activos
            /*$conductor_id = $viaje->fk_conductor;
            $query = DB::table('conductores')
            ->where('id', $conductor_id)
            ->update([
                'estado_aplicacion' => 0
            ]);*/

            if ($viaje->save()) {

                if($viaje->app_user_id!=null){

                    return Response::json([
                        'response' => true,
                        'id' => $viaje->app_user_id
                    ]);

                    $notifications = Servicio::ServicioIniciado($id, $viaje->app_user_id);

                }else if($viaje->tipo_traslado==70){ //Ruta

                    $users = DB::table('pasajeros_rutas_qr')
                    ->where('fk_viaje', $id)
                    ->get();

                    if($users){

                        $name = $nombreConductor;

                        foreach ($users as $user) {

                            if($viaje->tipo_ruta==67) { //Ruta de entrada

                                if($user->celular!=null and $user->celular!='' and $user->celular!=0){

                                    $number = '57'.$user->celular;

                                    $notifyIn = Viaje::notificarInicioRutaEntrada($number, $user->direccion, $user->id);

                                }

                                $empleadoUser = DB::table('users')
                                ->select('id', 'idregistrationdevice', 'idioma')
                                ->where('id_empleado',$user->id_empleado)
                                ->first();

                                if($empleadoUser) {
                                    
                                    $idregistrationdevice = $empleadoUser->idregistrationdevice;
                                    $idioma = $empleadoUser->idioma;
                                    $notificationss = Viaje::RutaIniciada($id, $idregistrationdevice, $idioma);

                                }

                            }else{ //Ruta de salida

                                $empleadoUser = DB::table('users')
                                ->select('id', 'idregistrationdevice', 'idioma')
                                ->where('id_empleado',$user->id_empleado)
                                ->first();

                                if($empleadoUser) {
                                    
                                    $idregistrationdevice = $empleadoUser->idregistrationdevice;
                                    $idioma = $empleadoUser->idioma;
                                    $notificationss = Viaje::RutaIniciada($id, $idregistrationdevice, $idioma);

                                }

                            }

                        }

                    }

                }else if($viaje->tipo_traslado!=70){ //Viaje ejecutivo

                    $pax = "select id, nombre, indicativo, celular, correo from pasajeros_ejecutivos where fk_viaje = ".$id."";
                    $paxs = DB::select($pax);

                    foreach ($paxs as $pass) {
                        
                        //envío de whatsapp
                        if($pass->celular!='' and $pass->celular!=null){

                            Viaje::ServicioIniciadoWhatsApp($id, $pass->indicativo, $pass->celular, $viaje);

                        }

                        //envío de correo
                        if($pass->correo!='' and $pass->correo!=null){

                            if (filter_var($pass->correo, FILTER_VALIDATE_EMAIL)) {
                                
                                $data = [
                                    'servicio' => $viaje
                                ];
    
                                $emailcc = ['aotourdeveloper@gmail.com'];
                                $email = $pass->correo;
    
                                Mail::send('emails.servicio_iniciado', $data, function($message) use ($email, $emailcc){
                                    $message->from('no-reply@aotour.com.co', 'AOTOUR');
                                    $message->to($email)->subject('Tracking disponible');
                                    $message->cc($emailcc);
                                });

                            }

                        }

                    }

                }

                return Response::json([
                    'response' => true,
                    'viaje' => $viaje
                ]);

            }

        }

    }

    public function viajeactivo(Request $request) {

        $id = intval($request->id);

        $fecha = date('Y-m-d');
        $diaanterior = strtotime ('-1 day', strtotime($fecha));
        $diaanterior = date ('Y-m-d' , $diaanterior);

        $diasiguiente = strtotime ('+1 day', strtotime($fecha));
        $diasiguiente = date('Y-m-d' , $diasiguiente);

        $consulta = "SELECT
		v.id,
		v.fk_estado,
		v.detalle_recorrido,
		v.fecha_viaje,
		v.hora_viaje,
        c.razonsocial,  
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        WHERE `fk_conductor` = ".$id." AND v.fecha_viaje between '".$diaanterior."' and '".$diasiguiente."' AND v.fk_estado = 59 and v.estado_eliminacion is null and v.estado_papelera is null
        GROUP BY v.id order by v.fecha_viaje asc, v.hora_viaje asc";

        $viajes = DB::select($consulta);

        if ($viajes) {

            return Response::json([
                'response' => true,
                'viajes' => $viajes,
                'conductor_id' => $id
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function usuarioactual(Request $request) {

        $id = $request->id;
        $viaje_id = $request->viaje_id;
        $nombreConductor = $request->nombre_conductor;

        $usuario = usuarioRutaQr::find($id);
        $usuario->recoger_a = 1;
        $usuario->save();

        $service = DB::table('viajes')
        ->select('id', 'tipo_ruta')
        ->where('id', $viaje_id)
        ->first();

        if($service->tipo_ruta==67) { //Entrada

            $number = '57'.$usuario->celular;
            $recogerEn = $usuario->direccion;

            $notify = Viaje::usuarioActualWhatsapp($number, $recogerEn, $usuario->id);

            $empleadoUser = DB::table('users')
            ->select('id', 'idregistrationdevice', 'idioma')
            ->where('id_empleado',$usuario->id_empleado)
            ->first();

            if($empleadoUser) {
                
                $idregistrationdevice = $empleadoUser->idregistrationdevice;
                $idioma = $empleadoUser->idioma;
                $notificationss = Viaje::usuarioActual($servicio_id, $idregistrationdevice, $idioma);

            }

        }else{ //salida

            $empleadoUser = DB::table('users')
            ->select('id', 'idregistrationdevice', 'idioma')
            ->where('id_empleado',$usuario->id_empleado)
            ->first();

            if($empleadoUser) {
                
                $idregistrationdevice = $empleadoUser->idregistrationdevice;
                $idioma = $empleadoUser->idioma;
                $notificationss = Viaje::usuarioActual($servicio_id, $idregistrationdevice, $idioma);

            }

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function esperaejecutivo(Request $request) {

        $viaje_id = $request->viaje_id;
        $nombreConductor = $request->nombre_conductor;

        $viaje = Viaje::find($viaje_id);
        $viaje->recoger_pasajero = 0;

        if($viaje->save()){

            //Notificar esperando por WhatsApp
            if($viaje->app_user_id!=null){ //ejecutivo de aplicación
                $notifications = Viaje::Enespera($viaje_id, $viaje->app_user_id);
            }else if($viaje->tipo_traslado!=70){

                $pax = "select id, nombre, indicativo, celular, correo from pasajeros_ejecutivos where fk_viaje = ".$id."";
                $paxs = DB::select($pax);

                foreach ($paxs as $pass) {
                    
                    //envío de whatsapp
                    if($pass->celular!='' and $pass->celular!=null){

                        $numero = $pass->celular;
                        
                        $nombreConductor = explode(' ', $nombreConductor);
                        
                        $cond = DB::table('conductores')
                        ->select('id', 'celular')
                        ->where('id', $viaje->fk_conductor)
                        ->first();

                        Viaje::esperaEjecutivo($viaje, $numero, $nombreConductor[0], $cond->celular, $pass->indicativo);

                    }

                    //envío de correo
                    if($pass->correo!='' and $pass->correo!=null){

                        if (filter_var($pass->correo, FILTER_VALIDATE_EMAIL)) {

                            $cond = DB::table('conductores')
                            ->select('id', 'celular')
                            ->where('id', $viaje->fk_conductor)
                            ->first();

                            $nom = explode(' ', $nombreConductor);

                            $data = [
                                'servicio' => $viaje,
                                'numero' => $cond->celular,
                                'nombre' => $nom[0]
                            ];

                            $emailcc = ['aotourdeveloper@gmail.com'];
                            $email = $pass->correo;

                            Mail::send('emails.servicio_esperando', $data, function($message) use ($email, $emailcc){
                                $message->from('no-reply@aotour.com.co', 'AOTOUR');
                                $message->to($email)->subject('Tu conductor ha llegado');
                                $message->cc($emailcc);
                            });

                        }

                    }

                }

            }

            return Response::json([
                'response' => true
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function esperaruta(Request $request) {

        $viaje_id = $request->viaje_id;
        $id = $request->id;
        $nombreConductor = $request->nombre_conductor;

        $usuario = usuarioRutaQr::find($id);
        $usuario->recoger_a = 0;
        $usuario->save();

        $viaje = DB::table('viajes')
        ->select('id', 'tipo_ruta', 'fk_conductor')
        ->where('id',$viaje_id)
        ->first();

        $conductor = DB::table('conductores')
        ->select('id', 'celular')
        ->where('id', $viaje->fk_conductor)
        ->first();

        if($viaje->tipo_ruta==67) { //entrada

            $name = explode(' ', $nombreConductor);

            $number = '57'.$usuario->celular;
            $recogerEn = $usuario->direccion;
            $contacto = $conductor->celular;

            $notify = Viaje::esperaRutaWhatsapp($number, $name[0], $recogerEn, $contacto, $usuario->id);

            $empleadoUser = DB::table('users')
            ->select('id', 'idregistrationdevice', 'idioma')
            ->where('id_empleado',$usuario->id_empleado)
            ->first();

            if($empleadoUser) {
                
                $idregistrationdevice = $empleadoUser->idregistrationdevice;
                $idioma = $empleadoUser->idioma;
                $notificationss = Viaje::esperaRutaUp($viaje_id, $idregistrationdevice, $idioma);

            }

        }else{

            $empleadoUser = DB::table('users')
            ->select('id', 'idregistrationdevice', 'idioma')
            ->where('id_empleado',$usuario->id_empleado)
            ->first();

            if($empleadoUser) {
                
                $idregistrationdevice = $empleadoUser->idregistrationdevice;
                $idioma = $empleadoUser->idioma;
                $notificationss = Viaje::esperaRutaUp($viaje_id, $idregistrationdevice, $idioma);

            }

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function dejarpasajero(Request $request) {

        $id = $request->id;

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $pasajero_location = json_encode([
            'latitude' => strval($latitude),
            'longitude' => strval($longitude),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $query = DB::table('pasajeros_rutas_qr')
        ->where('id', $id)
        ->update([
            'location' => $pasajero_location,
            'recoger_a' => 2
        ]);

        if($query){

            return Response::json([
                'response' => true
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function historialdia(Request $request) {

        $conductor_id = $request->conductor_id;
        $fecha = $request->fecha;

        $consulta = "SELECT
		v.id,
		v.fk_estado,
		v.detalle_recorrido,
		v.fecha_viaje,
		v.hora_viaje,
        c.razonsocial,  
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        WHERE `fk_conductor` = ".$conductor_id." AND v.fecha_viaje = '".$fecha."' AND v.fk_estado = 60 and v.estado_eliminacion is null and v.estado_papelera is null
        GROUP BY v.id order by v.fecha_viaje asc, v.hora_viaje asc";

        $viajes = DB::select($consulta);

        if (count($viajes)>0) {

            return Response::json([
                'response' => true,
                'viajes' => $viajes
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function historialmes(Request $request) {

        $conductor_id = $request->conductor_id;
        $mes = $request->mes;

        $ano = date('Y');

        $fechaInicial = $mes.'-01';
        $fechaFinal = $mes.'-31';

        $consulta = "SELECT
		v.id,
		v.fk_estado,
		v.detalle_recorrido,
		v.fecha_viaje,
		v.hora_viaje,
        c.razonsocial,  
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        WHERE `fk_conductor` = ".$conductor_id." AND v.fecha_viaje between '".$fechaInicial."' AND '".$fechaFinal."' AND v.fk_estado = 60 and v.estado_eliminacion is null and v.estado_papelera is null
        GROUP BY v.id order by v.fecha_viaje asc, v.hora_viaje asc";

        $viajes = DB::select($consulta);

        if (count($viajes)>0) {

            return Response::json([
                'response' => true,
                'viajes' => $viajes
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function listarnovedades(Request $request) {

        $viaje_id = $request->viaje_id;

        $novedades = "SELECT  n.*, t.nombre as nombre_tipo, e.nombre as nombre_estado from novedades_de_viajes n left join tipos t on t.id = n.tipo left join estados e on e.id = n.fk_estado where n.fk_viaje = ".$viaje_id."";
        $novedades = DB::select($novedades);

        if ($novedades) {

            return Response::json([
                'response' => true,
                'novedades' => $novedades
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function registrarnovedad(Request $request) {

        $tipo = $request->tipo;
        $viaje_id = $request->viaje_id;
        $detalles = $request->detalles;
        $nombreConductor = $request->nombre_conductor;

        $novedad = new NovedadViaje;
        $novedad->tipo = $tipo;
        $novedad->detalles = $detalles;
        $novedad->fk_viaje = $viaje_id;
        $novedad->fk_estado = 54;
        $novedad->fk_conductor = Auth::user()->id;
        $novedad->save();

        $facturacion = "SELECT id, fk_viaje from facturacion_de_viajes WHERE fk_viaje = ".$viaje_id."";
        $facturacion = DB::select($facturacion);

        if (count($facturacion)) {

            return Response::json([
                'response' => false,
                'message' => 'No es posible registrar una novedad en este viaje porque se encuentra en revisión.'
            ]);

        }else {

            if ($novedad->save()) {

                $viaje = Viaje::find($viaje_id);

                /*if($viaje->fk_sede!=1){ //Bogotá
                    $email = 'transportebogota@aotour.com.co';
                }else{ //Barranquilla
                    $email = 'transportebarranquilla@aotour.com.co';
                }

                $data = [
                    'servicio' => $viaje_id
                ];

                Mail::send('emails.novedad', $data, function($message) use ($email){
                    $message->from('no-reply@aotour.com.co', 'AUTONET');
                    $message->to($email)->subject('Novedad de Servicio');
                    $message->cc('aotourdeveloper@gmail.com');
                });*/

                //WhatsApp
                $fecha = $viaje->fecha_viaje;

                $cliente = DB::table('centrosdecosto')
                ->select('id', 'razonsocial')
                ->where('id', $viaje->fk_centrodecosto)
                ->first();

                $cliente = $cliente->razonsocial;

                if($viaje->fk_sede!=1){ //Bogotá
                    $numero = 3012633287;
                }else{ //Barranquilla
                    $numero = 3012030290;
                }

                $number = '57'.$numero; //Concatenación del indicativo con el número

                $number = intval($number);

                $notify = Viaje::notificarNovedadRegistrada($number, $nombreConductor, $fecha, $cliente);

                return Response::json([
                    'response' => true,
                    'novedad' => $novedad,
                    'message' => '¡Novedad registrada con éxito!'
                ]);

            }else{

                return Response::json([
                    'response' => false,
                    'message' => 'No se puedo registrar la novedad. Comunícate con el administrador de la app.'
                ]);

            }

        }

    }

    public function registrarnovedadruta(Request $request) {

        $id = $request->id;
        $novedad = $request->novedad;
        $viaje_id = $request->viaje_id;

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        if($latitude!=null){

            $pasajero_location = json_encode([
                'latitude' => strval($latitude),
                'longitude' => strval($longitude),
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            $update = DB::table('pasajeros_rutas_qr')
            ->where('id', $id)
            ->update([
                'estado_ruta' => $novedad,
                'location' => $pasajero_location,
            ]);

        }else{

            $update = DB::table('pasajeros_rutas_qr')
            ->where('id', $id)
            ->update([
                'estado_ruta' => $novedad
            ]);

        }

        if($update) {

            $usuarioRec = DB::table('pasajeros_rutas_qr')
            ->select('id', 'nombre', 'id_empleado')
            ->where('id', $id)
            ->first();

            if($novedad==87){

                $empleadoUser = DB::table('users')
                ->select('id', 'idregistrationdevice', 'idioma')
                ->where('id_empleado',$usuarioRec->id_empleado)
                ->first();

                if($empleadoUser) {
                    
                    $idregistrationdevice = $empleadoUser->idregistrationdevice;
                    $idioma = $empleadoUser->idioma;
                    $notificationss = Viaje::bienvenidoaBordoUp($viaje_id, $idregistrationdevice, $idioma);

                }

            }

            return Response::json([
                'response' => true
            ]);

        }

    }

    public function descargarconstancia(Request $request) {

        ini_set('max_execution_time', 300);

    	$id = $request->id;

    	$viaje = Viaje::find($id);

        $filepath = null;

        $view = View::make('servicios.plantilla_constancia_vieja')->with([
            'servicio' => $viaje,
            'filepath' => $filepath
        ])->render();

    	$view = preg_replace('/>\s+</', '><', $view);

    	$pdf = PDF::load($view, 'A4', 'portrait')->output();

    	return Response::json([
    		'response' => true,
    		'pdf' => base64_encode($pdf),
    		'option' => 3
    	]);

    }

    public function pasajerorecogido(Request $request) {

        $viaje_id = $request->viaje_id;
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $viaje = Viaje::find($viaje_id);

        $viaje->recoger_pasajero = 1;
        $viaje->recoger_pasajero_location = json_encode([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        if ($viaje->save()) {

            if($viaje->app_user_id!=null){
                
                $notifications = Viaje::pasajeroRecogidoUp($viaje_id, $viaje->app_user_id);

                return Response::json([
                    'response' => true
                ]);

            }

            //Notificación a los usuarios recogidos que nos dirigimos al punto de destino
            $serv = DB::table('viajes')
            ->select('id', 'tipo_ruta')
            ->where('id', $viaje_id)
            ->first();

            if($serv->tipo_ruta==67) { //entrada

                $users = DB::table('pasajeros_rutas_qr')
                ->where('fk_viaje', $viaje_id)
                ->where('estado_ruta', 87)
                ->get();

                if($users){

                    $destino = DB::table('destinos')
                    ->where('fk_viaje', $viaje_id)
                    ->where('orden', 2)
                    ->first();

                    $dejarEn = $destino->direccion;

                    foreach ($users as $user) {

                        $number = '57'.$user->celular;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v15.0/109529185312847/messages");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HEADER, FALSE);
                        curl_setopt($ch, CURLOPT_POST, TRUE);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                            \"messaging_product\": \"whatsapp\",
                            \"to\": \"".$number."\",
                            \"type\": \"template\",
                            \"template\": {
                            \"name\": \"recorrido_finalizado\",
                            \"language\": {
                                \"code\": \"es\",
                            },
                            \"components\": [{
                                \"type\": \"body\",
                                \"parameters\": [{
                                \"type\": \"text\",
                                \"text\": \"".$dejarEn."\",
                                }]
                            }]
                            }
                        }");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                            "Authorization: Bearer ".ConfigController::KEY_WHATSAPP.""
                        ));

                        $response = curl_exec($ch);
                        curl_close($ch);

                    }

                }

            }
            //Notificación a los usuarios recogidos que nos dirigimos al lugar de destino

            return Response::json([
                'response' => true,
                'viaje' => $viaje
            ]);

        }

    }

    public function finalizarviaje(Request $request) {

        $viaje_id = $request->viaje_id;
        $calificacion = $request->calificacion;
        $comentario = $request->comentario;

        $viaje = Viaje::find($viaje_id);

        $viaje->fk_estado = 60;
        $viaje->hora_finalizado = date('Y-m-d H:i:s');

        //if($calificacion!=null){
            //$servicio->calificacion_app_conductor_calidad = $calificacion;
        //}

        if ($viaje->save()) {

            /*if($comentario!=null and $comentario!=''){
                
                $coment = new Coment;
                $coment->servicio_id = $viaje->id;
                $coment->comentario = $comentario;
                $coment->save();

            }*/

            if($viaje->app_user_id!=null){

                $finalizarServicio = Viaje::finalizaciondeviajeUp($viaje_id, $viaje->app_user_id);

            }else if($viaje->tipo_traslado==70){ //Viaje de Ruta

                $users =  DB::table('pasajeros_rutas_qr')
                ->where('fk_viaje', $viaje->id)
                ->where('estado_ruta', 87)
                ->get();

                foreach ($users as $user) {

                    if($user->celular!=null and $user->celular!=''){

                        $number = '57'.$user->celular;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v15.0/109529185312847/messages");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HEADER, FALSE);
                        curl_setopt($ch, CURLOPT_POST, TRUE);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                            \"messaging_product\": \"whatsapp\",
                            \"to\": \"".$number."\",
                            \"type\": \"template\",
                            \"template\": {
                            \"name\": \"ruta_finalizada\",
                            \"language\": {
                                \"code\": \"es\",
                            },
                            \"components\": [{

                                \"type\": \"button\",
                                \"sub_type\": \"url\",
                                \"index\": \"0\",
                                \"parameters\": [{
                                \"type\": \"payload\",
                                \"payload\": \"".$user->id."\"
                                }]
                            }]
                            }
                        }");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                            "Authorization: Bearer ".ConfigController::KEY_WHATSAPP.""
                        ));

                        $response = curl_exec($ch);
                        curl_close($ch);

                    }

                    $empleadoUser = DB::table('users')
                    ->select('id', 'idregistrationdevice', 'idioma')
                    ->where('id_empleado', $user->id_usuario)
                    ->first();

                    if($empleadoUser) {
                        
                        $idregistrationdevice = $empleadoUser->idregistrationdevice;
                        $idioma = $empleadoUser->idioma;
                        $notificationss = Viaje::finalizacionderutaUp($viaje_id, $idregistrationdevice, $idioma);

                    }

                }

            }else{ //ejecutivo

                $passengers = DB::table('pasajeros_ejecutivos')->where('fk_viaje',$viaje_id)->get();

                foreach ($passengers as $pass) {
                    
                    if($pass->correo!='' and $pass->correo!=null){

                        if (filter_var($pass->correo, FILTER_VALIDATE_EMAIL)) {
                            
                            $data = [
                                'servicio' => $viaje
                            ];
        
                            $emailcc = ['aotourdeveloper@gmail.com'];
                            $email = $pass->correo;
        
                            Mail::send('emails.servicio_calificar', $data, function($message) use ($email, $emailcc){
                                $message->from('no-reply@aotour.com.co', 'AOTOUR');
                                $message->to($email)->subject('Califica tu viaje');
                                $message->cc($emailcc);
                            });

                        }

                    }

                    if($pass->celular!='' and $pass->celular!=null){

                        $number = $pass->celular;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v15.0/109529185312847/messages");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HEADER, FALSE);
                        curl_setopt($ch, CURLOPT_POST, TRUE);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                            \"messaging_product\": \"whatsapp\",
                            \"to\": \"".$number."\",
                            \"type\": \"template\",
                            \"template\": {
                            \"name\": \"calificacion\",
                            \"language\": {
                                \"code\": \"es\",
                            },
                            \"components\": [{

                                \"type\": \"button\",
                                \"sub_type\": \"url\",
                                \"index\": \"0\",
                                \"parameters\": [{
                                \"type\": \"payload\",
                                \"payload\": \"".$viaje_id."\"
                                }]
                            }]
                            }
                        }");

                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                            "Authorization: Bearer ".ConfigController::KEY_WHATSAPP.""
                        ));

                        $response = curl_exec($ch);
                        curl_close($ch);

                    }
                    
                }

            }

            $coordenadas = DB::table('gps')
            ->select('id', 'coordenadas')
            ->where('fk_viaje', $viaje_id)
            ->first();

            if($coordenadas) {

                $ubicaciones = json_decode($coordenadas->coordenadas);
                $totales = 0;
                $latOld = 0;
                $lonOld = 0;
                $sw = 0;

                if(count($ubicaciones)>0){

                    foreach ($ubicaciones as $ubi) {

                        if($sw!=0){

                            $lat2 = $ubi->latitude; //latitud coord 2
                            $lon2 = $ubi->longitude; //longitud coord 2

                            $theta = $lonOld - $lon2;
                            $dist = sin(deg2rad($latOld)) * sin(deg2rad($lat2)) +  cos(deg2rad($latOld)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                            $dist = acos($dist);
                            $dist = rad2deg($dist);
                            $miles = $dist * 60 * 1.1515;

                            $nuevoValor = $miles * 1.609344;
                            $totales = $totales+$nuevoValor;

                        }else{
                            $sw = 1;
                        }

                        $latOld = $ubi->latitude; //latitud coord 1
                        $lonOld = $ubi->longitude; //longitud coord 1

                    }

                }

                if($totales>0){

                    $updateServ = DB::table('viajes')->where('id', $viaje->id)
                    ->update([
                        'kilometraje' => round($totales, 3)
                    ]);

                }

            }

            return Response::json([
                'response'=>true,
            ]);

        }

    }
    


















    
    public function createtrip(Request $request) {

        $viajes = $request->viajes;

        $actualDate = date('Y-m-d');
        $actualTime = date('H:i');

        for ($a=0; $a < count($viajes); $a++){

            $horaMaxima = date('H:i',strtotime('+30 minute',strtotime($viajes[$a]['hora_viaje'])));

            $viaje = new Viaje;

            $code = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($o = 0; $o < 2; $o++) {
                $rand = mt_rand(0, $max);
                $code .= $characters[$rand];
            }

            $viaje->fk_centrodecosto = $request->centrodecosto;
            $viaje->fk_subcentrodecosto = $request->subcentrodecosto;

            if ($request->user_id!=null) {
                $viaje->app_user_id = $request->user_id;
            }

            $ciudad = DB::table('ciudades')->where('id',$request->ciudad)->first();
            $viaje->fk_departamento = $ciudad->fk_departamento;
            $viaje->fk_ciudad = $request->ciudad;
            $viaje->solicitante = $request->solicitante;
            $viaje->email_solicitante = $request->email_solicitante;
            $viaje->celular_solicitante = $request->celular_solicitante;
            $viaje->fk_sede = $request->sede;
            $viaje->fecha_solicitud = date('Y-m-d');

            $viaje->tipo_servicio = 44;

            $viaje->fk_traslado = $viajes[$a]['traslado'];

            $viaje->detalle_recorrido = $viajes[$a]['detalle_recorrido'];

            $conductor = DB::table('conductores')->where('id',$viajes[$a]['conductor'])->first();
            $viaje->fk_proveedor = $conductor->fk_proveedor;

            $viaje->fk_conductor = $viajes[$a]['conductor'];
            $viaje->fk_vehiculo = $viajes[$a]['vehiculo'];

            $viaje->fecha_viaje = $viajes[$a]['fecha_viaje'];
            $viaje->hora_viaje = $viajes[$a]['hora_viaje'];

            if( $viajes[$a]['vuelo']!=null ) {
                $viaje->vuelo = $viajes[$a]['vuelo'];
            }

            $viaje->creado_por = Auth::user()->id;
            
            if( $viajes[$a]['expediente']!=null ) {
                $viaje->expediente = $viajes[$a]['expediente'];
            }
            
            if( Auth::user()->id_perfil == 8 ){
                $viaje->control_facturacion = 1;
            }
            $viaje->fk_estado = 57;

            $viaje->save();

            $destinos = $viajes[$a]['destino'];
            
            $trayecto = '';

            for ($i=0; $i < count($destinos); $i++){

                //Guardar los destinos del viaje START FOREACH
                $destino = new Destino;
                $destino->direccion = $destinos[$i]['direccion'];
                $destino->coordenadas = json_encode([
                    'latitude' => $destinos[$i]['latitude'],
                    'longitude' => $destinos[$i]['longitude']
                ]);
                $destino->fk_viaje = $viaje->id;
                $destino->orden = $i+1;
                $destino->save();

                $trayecto = $trayecto.$destinos[$i]['direccion'].' | ';

                //Guardar los destinos del viaje END FOREACH

            }

            $pasajeros = $viajes[$a]['pasajeros'];

            for ($i=0; $i < count($pasajeros); $i++){
                
                //Guardar los pasajeros del viaje START - FOREACH
                $pasajero = new PasajeroEjecutivo;
                $pasajero->nombre = $pasajeros[$i]['nombre'];
                $pasajero->indicativo = $pasajeros[$i]['indicativo'];
                $pasajero->celular = $pasajeros[$i]['celular'];
                $pasajero->correo = $pasajeros[$i]['correo'];
                $pasajero->fk_viaje = $viaje->id;
                $pasajero->save();
                //Guardar los pasajeros del viaje END

                if($actualDate<=$viajes[$a]['fecha_viaje']){

                    if($actualTime<=$horaMaxima){

                        if($pasajeros[$i]['celular']!=null){

                            $vehiculo = DB::table('vehiculos')->where('id',$viaje->fk_vehiculo)->first();
                            $conductor = DB::table('conductores')->where('id',$viaje->fk_conductor)->first();

                            $number = intval($pasajeros[$i]['indicativo'].$pasajeros[$i]['celular']);

                            $nombre = $conductor->primer_nombre;

                            $fecha = $viaje->fecha_viaje;
                            $hora = $viaje->hora_viaje;

                            $cliente = DB::table('centrosdecosto')->where('id',$request->centrodecosto)->pluck('razonsocial');

                            if($vehiculo->placa=='ABC-123'){
                                $placaVehiculo = 'POR CONFIRMAR';
                            }else{
                                $placaVehiculo = $vehiculo->placa;
                            }

                            $res = Viaje::notificarViajeEjecutivo($number, $pasajero->nombre, $fecha, $hora, $nombre, $placaVehiculo, $trayecto, $viaje->id);

                        }

                    }

                }

            }

            //si la fecha y la hora actuales son menores a la fecha y hora del servicio
            if($actualDate<=$viajes[$a]['fecha_viaje']){

                if($actualTime<=$horaMaxima){
                    $number = rand(10000000, 99999999);
                    $res2 = Viaje::notificarConductor($viaje->fk_conductor, $viaje->fecha_viaje, $viaje->hora_viaje, $number, $viaje->id);
                }

            }

            //Parte REVERSO
            if($viajes[$a]['reverso']==1) { 
                
                $viaje = new Viaje;

                $code = "";
                $characters = array_merge(range('0','9'));
                $max = count($characters) - 1;
                for ($o = 0; $o < 2; $o++) {
                    $rand = mt_rand(0, $max);
                    $code .= $characters[$rand];
                }

                $viaje->fk_centrodecosto = $request->centrodecosto;
                $viaje->fk_subcentrodecosto = $request->subcentrodecosto;

                if ($request->user_id!=null) {
                    $viaje->app_user_id = $request->user_id;
                }

                $ciudad = DB::table('ciudades')->where('id',$request->ciudad)->first();
                $viaje->fk_departamento = $ciudad->fk_departamento;
                $viaje->fk_ciudad = $request->ciudad;
                $viaje->solicitante = $request->solicitante;
                $viaje->email_solicitante = $request->email_solicitante;
                $viaje->celular_solicitante = $request->celular_solicitante;
                
                $viaje->fecha_solicitud = date('Y-m-d');
                $viaje->tipo_servicio = 44;

                //$viaje->resaltar = $resaltarArray[$i]; //pending
                //$viaje->pago_directo = $pago_directoArray[$i]; //pending
                //$viaje->codigo_viaje = $code; //pending
                //$viaje->cantidad = Input::get('cantidad'); //pending
                $viaje->fk_sede = $request->sede;

                $viaje->fk_traslado = $viajes[$a]['traslado'];

                $viaje->detalle_recorrido = $viajes[$a]['detalle_recorrido'];

                $conductor = DB::table('conductores')->where('id',$viajes[$a]['conductor'])->first();
                $viaje->fk_proveedor = $conductor->fk_proveedor;

                $viaje->fk_conductor = $viajes[$a]['conductor'];
                $viaje->fk_vehiculo = $viajes[$a]['vehiculo'];

                $viaje->fecha_viaje = $viajes[$a]['fechados']; //$request->fechados;
                $viaje->hora_viaje = $viajes[$a]['horados'];

                if( $viajes[$a]['vuelo']!=null ) {
                    $viaje->vuelo = $viajes[$a]['vuelo'];
                }

                $viaje->creado_por = Auth::user()->id;
                
                if( $viajes[$a]['expediente']!=null ) {
                    $viaje->expediente = $viajes[$a]['expediente'];
                }
                
                if( Auth::user()->id_perfil == 8 ){
                    $viaje->control_facturacion = 1;
                }
                $viaje->fk_estado = 57;

                $viaje->save();

                $destinos = $viajes[$a]['destinodos'];

                //for ($i=0; $i < count($destinos); $i++){
                
                $conta = 0;
                
                for ($i=0; $i < count($destinos); $i++){

                    //Guardar los destinos del viaje START FOREACH
                    $destino = new Destino;
                    $destino->direccion = $destinos[$i]['direccion'];
                    $destino->coordenadas = json_encode([
                        'latitude' => $destinos[$i]['latitude'],
                        'longitude' => $destinos[$i]['longitude']
                    ]);
                    $destino->fk_viaje = $viaje->id;
                    $destino->orden = $i+1;
                    $destino->save();
                    //Guardar los destinos del viaje END FOREACH

                }

                $pasajeros = $viajes[$a]['pasajeros'];

                for ($i=0; $i < count($pasajeros); $i++){
                    
                    //Guardar los pasajeros del viaje START - FOREACH
                    $pasajero = new PasajeroEjecutivo;
                    $pasajero->nombre = $pasajeros[$i]['nombre'];
                    $pasajero->indicativo = $pasajeros[$i]['indicativo'];
                    $pasajero->celular = $pasajeros[$i]['celular'];
                    $pasajero->correo = $pasajeros[$i]['correo'];
                    $pasajero->fk_viaje = $viaje->id;
                    $pasajero->save();
                    //Guardar los pasajeros del viaje END

                    if($actualDate<=$viajes[$a]['fecha_viaje']){

                        if($actualTime<=$horaMaxima){

                            if($pasajeros[$i]['celular']!=null){

                                $vehiculo = DB::table('vehiculos')->where('id',$viaje->fk_vehiculo)->first();
                                $conductor = DB::table('conductores')->where('id',$viaje->fk_conductor)->first();
    
                                $number = intval($pasajeros[$i]['indicativo'].$pasajeros[$i]['celular']);
    
                                $nombre = $conductor->primer_nombre;
    
                                $fecha = $viaje->fecha_viaje;
                                $hora = $viaje->hora_viaje;
    
                                $cliente = DB::table('centrosdecosto')->where('id',$request->centrodecosto)->pluck('razonsocial');
    
                                if($vehiculo->placa=='ABC-123'){
                                    $placaVehiculo = 'POR CONFIRMAR';
                                }else{
                                    $placaVehiculo = $vehiculo->placa;
                                }
    
                                $res = Viaje::notificarViajeEjecutivo($number, $pasajero->nombre, $fecha, $hora, $nombre, $placaVehiculo, $trayecto, $viaje->id);
    
                            }

                        }

                    }

                }

                //si la fecha y la hora actuales son menores a la fecha y hora del servicio
                if($actualDate<=$viajes[$a]['fecha_viaje']){

                    if($actualTime<=$horaMaxima){
                        $number = rand(10000000, 99999999);
                        $res2 = Viaje::notificarConductor($viaje->fk_conductor, $viaje->fecha_viaje, $viaje->hora_viaje, $number, $viaje->id);
                    }

                }
            }

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function createmultipletrip(Request $request) {

        $fechas = $request->fecha_viaje;

        for ($u=0; $u < count($fechas); $u++){

            $viaje = new Viaje;

            $code = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($o = 0; $o < 2; $o++) {
                $rand = mt_rand(0, $max);
                $code .= $characters[$rand];
            }

            $viaje->fk_centrodecosto = $request->centrodecosto;
            $viaje->fk_subcentrodecosto = $request->subcentrodecosto;

            if ($request->user_id!=null) {
                $viaje->app_user_id = $request->user_id;
            }

            $ciudad = DB::table('ciudades')->where('id',$request->ciudad)->first();
            $viaje->fk_departamento = $ciudad->fk_departamento;
            $viaje->fk_ciudad = $request->ciudad;
            $viaje->solicitante = $request->solicitante;
            $viaje->email_solicitante = $request->email_solicitante;
            $viaje->celular_solicitante = $request->celular_solicitante;
            
            $viaje->fecha_solicitud = date('Y-m-d');
            $viaje->tipo_servicio = 45;

            $viaje->fk_traslado = $request->traslado;
            $viaje->fk_sede = $request->sede;

            $viaje->detalle_recorrido = $request->detalle_recorrido;

            $conductor = DB::table('conductores')->where('id',$request->conductor)->first();
            $viaje->fk_proveedor = $conductor->fk_proveedor;

            $viaje->fk_conductor = $request->conductor;
            $viaje->fk_vehiculo = $request->vehiculo;

            $viaje->fecha_viaje = $fechas[$u];
            $viaje->hora_viaje = $request->hora_viaje;

            if( $request->vuelo!=null ) {
                $viaje->vuelo = $request->vuelo;
            }

            $viaje->creado_por = Auth::user()->id;
            
            if( $request->expediente!=null ) {
                $viaje->expediente = $request->expediente;
            }
            
            if( Auth::user()->id_perfil == 8 ){
                $viaje->control_facturacion = 1;
            }

            $viaje->fk_estado = 57;

            $viaje->save();

            $destinos = $request->destino;

            for ($i=0; $i < count($destinos); $i++){

                //Guardar los destinos del viaje START FOREACH
                $destino = new Destino;
                $destino->direccion = $destinos[$i]['direccion'];
                $destino->coordenadas = json_encode([
                    'latitude' => $destinos[$i]['latitude'],
                    'longitude' => $destinos[$i]['longitude']
                ]);
                $destino->fk_viaje = $viaje->id;
                $destino->orden = $i+1;
                $destino->save();
                //Guardar los destinos del viaje END FOREACH

            }

            $pasajeros = $request->pasajeros;

            for ($i=0; $i < count($pasajeros); $i++){
                
                //Guardar los pasajeros del viaje START - FOREACH
                $pasajero = new PasajeroEjecutivo;
                $pasajero->nombre = $pasajeros[$i]['nombre'];
                $pasajero->indicativo = $pasajeros[$i]['indicativo'];
                $pasajero->celular = $pasajeros[$i]['celular'];
                $pasajero->correo = $pasajeros[$i]['correo'];
                $pasajero->fk_viaje = $viaje->id;
                $pasajero->save();
                //Guardar los pasajeros del viaje END

            }

            //Parte REVERSO
            if($request->reverso==1) { //reverso de servicios múltiples

                $viaje = new Viaje;

                $code = "";
                $characters = array_merge(range('0','9'));
                $max = count($characters) - 1;
                for ($o = 0; $o < 2; $o++) {
                    $rand = mt_rand(0, $max);
                    $code .= $characters[$rand];
                }

                $viaje->fk_centrodecosto = $request->centrodecosto;
                $viaje->fk_subcentrodecosto = $request->subcentrodecosto;

                if ($request->user_id!=null) {
                    $viaje->app_user_id = $request->user_id;
                }

                $ciudad = DB::table('ciudades')->where('id',$request->ciudad)->first();
                $viaje->fk_departamento = $ciudad->fk_departamento;
                $viaje->fk_ciudad = $request->ciudad;
                $viaje->solicitante = $request->solicitante;
                $viaje->email_solicitante = $request->email_solicitante;
                $viaje->celular_solicitante = $request->celular_solicitante;
                
                $viaje->fecha_solicitud = date('Y-m-d');
                $viaje->tipo_servicio = 45;

                //$viaje->resaltar = $resaltarArray[$i]; //pending
                //$viaje->pago_directo = $pago_directoArray[$i]; //pending
                //$viaje->codigo_viaje = $code; //pending
                //$viaje->cantidad = Input::get('cantidad'); //pending

                $viaje->fk_traslado = $request->traslado;
                $viaje->fk_sede = $request->sede;

                $viaje->detalle_recorrido = $request->detalle_recorrido;

                $conductor = DB::table('conductores')->where('id',$request->conductor)->first();
                $viaje->fk_proveedor = $conductor->fk_proveedor;

                $viaje->fk_conductor = $request->conductor;
                $viaje->fk_vehiculo = $request->vehiculo;

                $viaje->fecha_viaje = $fechas[$u];
                $viaje->hora_viaje = $request->horados;

                if( $request->vuelo!=null ) {
                    $viaje->vuelo = $request->vuelo;
                }

                $viaje->creado_por = Auth::user()->id;
                
                if( $request->expediente!=null ) {
                    $viaje->expediente = $request->expediente;
                }
                
                if( Auth::user()->id_perfil == 8 ){
                    $viaje->control_facturacion = 1;
                }

                $viaje->fk_estado = 57;

                $viaje->save();

                $destinos = $request->destinodos;

                //for ($i=0; $i < count($destinos); $i++){
                
                $conta = 0;
                
                for ($i=0; $i < count($destinos); $i++){

                    //Guardar los destinos del viaje START FOREACH
                    $destino = new Destino;
                    $destino->direccion = $destinos[$i]['direccion'];
                    $destino->coordenadas = json_encode([
                        'latitude' => $destinos[$i]['latitude'],
                        'longitude' => $destinos[$i]['longitude']
                    ]);
                    $destino->fk_viaje = $viaje->id;
                    $destino->orden = $i+1;
                    $destino->save();
                    //Guardar los destinos del viaje END FOREACH

                }

                $pasajeros = $request->pasajeros;

                for ($i=0; $i < count($pasajeros); $i++){
                    
                    //Guardar los pasajeros del viaje START - FOREACH
                    $pasajero = new PasajeroEjecutivo;
                    $pasajero->nombre = $pasajeros[$i]['nombre'];
                    $pasajero->indicativo = $pasajeros[$i]['indicativo'];
                    $pasajero->celular = $pasajeros[$i]['celular'];
                    $pasajero->correo = $pasajeros[$i]['correo'];
                    $pasajero->fk_viaje = $viaje->id;
                    $pasajero->save();
                    //Guardar los pasajeros del viaje END

                }

            }

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function createtripdispo(Request $request) {

        $viajes = $request->viajes;

        $actualDate = date('Y-m-d');
        $actualTime = date('H:i');

        for ($a=0; $a < count($viajes); $a++){

            $horaMaxima = date('H:i',strtotime('+30 minute',strtotime($viajes[$a]['hora_viaje'])));

            $viaje = new Viaje;

            $code = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($o = 0; $o < 2; $o++) {
                $rand = mt_rand(0, $max);
                $code .= $characters[$rand];
            }

            $viaje->fk_centrodecosto = $request->centrodecosto;
            $viaje->fk_subcentrodecosto = $request->subcentrodecosto;

            if ($request->user_id!=null) {
                $viaje->app_user_id = $request->user_id;
            }

            $ciudad = DB::table('ciudades')->where('id',$request->ciudad)->first();
            $viaje->fk_departamento = $ciudad->fk_departamento;
            $viaje->fk_ciudad = $request->ciudad;
            $viaje->solicitante = $request->solicitante;
            $viaje->email_solicitante = $request->email_solicitante;
            $viaje->celular_solicitante = $request->celular_solicitante;
            $viaje->fk_sede = $request->sede;
            $viaje->fecha_solicitud = date('Y-m-d');

            $viaje->tipo_servicio = 46;

            $viaje->fk_traslado = $viajes[$a]['traslado'];

            $viaje->detalle_recorrido = $viajes[$a]['detalle_recorrido'];

            $conductor = DB::table('conductores')->where('id',$viajes[$a]['conductor'])->first();
            $viaje->fk_proveedor = $conductor->fk_proveedor;

            $viaje->fk_conductor = $viajes[$a]['conductor'];
            $viaje->fk_vehiculo = $viajes[$a]['vehiculo'];

            $viaje->fecha_viaje = $viajes[$a]['fecha_viaje'];
            $viaje->hora_viaje = $viajes[$a]['hora_viaje'];

            if( $viajes[$a]['vuelo']!=null ) {
                $viaje->vuelo = $viajes[$a]['vuelo'];
            }

            $viaje->creado_por = Auth::user()->id;
            
            if( $viajes[$a]['expediente']!=null ) {
                $viaje->expediente = $viajes[$a]['expediente'];
            }
            
            if( Auth::user()->id_perfil == 8 ){
                $viaje->control_facturacion = 1;
            }

            $viaje->fk_estado = 57;

            $viaje->save();

            $destinos = $viajes[$a]['destino'];
            
            $trayecto = '';

            for ($i=0; $i < count($destinos); $i++){

                //Guardar los destinos del viaje START FOREACH
                $destino = new Destino;
                $destino->direccion = $destinos[$i]['direccion'];
                $destino->coordenadas = json_encode([
                    'latitude' => $destinos[$i]['latitude'],
                    'longitude' => $destinos[$i]['longitude']
                ]);
                $destino->fk_viaje = $viaje->id;
                $destino->orden = $i+1;
                $destino->save();

                $trayecto = $trayecto.$destinos[$i]['direccion'].' | ';

                //Guardar los destinos del viaje END FOREACH

            }

            $pasajeros = $viajes[$a]['pasajeros'];

            for ($i=0; $i < count($pasajeros); $i++){
                
                //Guardar los pasajeros del viaje START - FOREACH
                $pasajero = new PasajeroEjecutivo;
                $pasajero->nombre = $pasajeros[$i]['nombre'];
                $pasajero->indicativo = $pasajeros[$i]['indicativo'];
                $pasajero->celular = $pasajeros[$i]['celular'];
                $pasajero->correo = $pasajeros[$i]['correo'];
                $pasajero->fk_viaje = $viaje->id;
                $pasajero->save();
                //Guardar los pasajeros del viaje END

                if($actualDate<=$viajes[$a]['fecha_viaje']){

                    if($actualTime<=$horaMaxima){

                        if($pasajeros[$i]['celular']!=null){

                            $vehiculo = DB::table('vehiculos')->where('id',$viaje->fk_vehiculo)->first();
                            $conductor = DB::table('conductores')->where('id',$viaje->fk_conductor)->first();

                            $number = intval($pasajeros[$i]['indicativo'].$pasajeros[$i]['celular']);

                            $nombre = $conductor->primer_nombre;

                            $fecha = $viaje->fecha_viaje;
                            $hora = $viaje->hora_viaje;

                            $cliente = DB::table('centrosdecosto')->where('id',$request->centrodecosto)->pluck('razonsocial');

                            if($vehiculo->placa=='ABC-123'){
                                $placaVehiculo = 'POR CONFIRMAR';
                            }else{
                                $placaVehiculo = $vehiculo->placa;
                            }

                            $res = Viaje::notificarViajeEjecutivo($number, $pasajero->nombre, $fecha, $hora, $nombre, $placaVehiculo, $trayecto, $viaje->id);

                        }

                    }

                }

            }

            //si la fecha y la hora actuales son menores a la fecha y hora del servicio
            if($actualDate<=$viajes[$a]['fecha_viaje']){

                if($actualTime<=$horaMaxima){
                    $number = rand(10000000, 99999999);
                    $res2 = Viaje::notificarConductor($viaje->fk_conductor, $viaje->fecha_viaje, $viaje->hora_viaje, $number, $viaje->id);
                }

            }

        }

        return Response::json([
            'response' => true
        ]);

    }

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
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        where v.estado_eliminacion is null
        GROUP BY v.id";
        
        $viajes = DB::select($viajes);

        return Response::json([
            'response' => true,
            'viajes' => $viajes
        ]);

    }

    public function showtripdetails(Request $request) {

        /*$viajes = "SELECT
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
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos
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
        where v.id = ".$request->id."
        GROUP BY v.id";*/

        $viaje = "SELECT
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
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT JSON_ARRAYAGG(JSON_OBJECT('nombre', pax.nombre, 'correo', pax.correo, 'celular', pax.celular, 'indicativo', pax.indicativo)) FROM viajes v2 left join pasajeros_ejecutivos pax on pax.fk_viaje = v2.id where v2.id = v.id) as pasajeros_ejecutivos
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
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        where v.id = ".$request->id."
        GROUP BY v.id limit 1";
        
        $viaje = DB::select($viaje);
        
        return Response::json([
            'response' => true,
            'viaje' => $viaje
        ]);

    }

    public function edittrip(Request $request) {

        $viaje = Viaje::find($request->id);

        $actualDate = date('Y-m-d');
        $actualTime = date('H:i');

        $conductorOld = $viaje->fk_conductor;
        $clienteOld = $request->fk_centrodecosto;
        $fechaOld = $request->fecha_viaje;

        //$consulta = "SELECT * FROM liquidacion_servicios WHERE '".Input::get('fecha_servicio')."' BETWEEN fecha_inicial AND fecha_final and centrodecosto_id = '".Input::get('centrodecosto_id')."' and subcentrodecosto_id = '".Input::get('subcentrodecosto_id')."' and ciudad = '".Input::get('ciudad')."' and anulado is null and nomostrar is null";
        //$consulta_orden = "SELECT * FROM ordenes_facturacion WHERE '".Input::get('fecha_servicio')."' BETWEEN fecha_inicial AND fecha_final and centrodecosto_id = '".Input::get('centrodecosto_id')."' and subcentrodecosto_id = '".Input::get('subcentrodecosto_id')."' and ciudad = '".Input::get('ciudad')."' and anulado is null and nomostrar is null and tipo_orden = 1";

        //$liquidacion = DB::select($consulta);
        //$ordenes_facturacion = DB::select($consulta_orden);

        $ordenes_facturacion = null; //quitar cuando se haga el módulo de facturación
        $liquidacion = null; //quitar cuando se haga el módulo de facturación

        if($ordenes_facturacion!=null or $liquidacion!=null){

            return Response::json([
                'response'=>'rechazado',
                'liquidacion'=>$liquidacion,
                'ordenes_facturacion'=>$ordenes_facturacion
            ]);

        }else{

            //Si el campo app user id es diferente de null
            /*if (Input::get('app_user_id')!=null) {
                //Asignarle valor a la variable app_user_id 228
                $app_user_id = $viaje->app_user_id;

            }else {
                //Si el campo es null o tiene otro valor
                $app_user_id = null;

            }*/

            $viaje->fk_centrodecosto = $request->centrodecosto;
            $viaje->fk_subcentrodecosto = $request->subcentrodecosto;
            $ciudad = DB::table('ciudades')->where('id',$request->ciudad)->first();
            $viaje->fk_departamento = $ciudad->fk_departamento;
            $viaje->fk_ciudad = $request->ciudad;
            $viaje->cantidad = count($request->pasajeros);
            $viaje->solicitante = $request->solicitante;
            $viaje->email_solicitante = $request->email_solicitante;

            //Edición de destinos - PENDING

            $viaje->detalle_recorrido = $request->detalle_recorrido;

            if ($request->conductorCambiado==1) {
                
                $driver = DB::table('conductores')
                ->where('id',$request->conductor)
                ->first();

                $viaje->fk_proveedor = $driver->fk_proveedor;
                $viaje->fk_conductor = $request->conductor;
                $viaje->fk_vehiculo = $request->vehiculo;

            }

            $viaje->fecha_viaje = $request->fecha_viaje;
            $viaje->hora_viaje = $request->hora_viaje;

            //$viaje->resaltar = Input::get('resaltar');
            //$viaje->pago_directo = Input::get('pago_directo');

            if($request->vuelo!=null) {
                $viaje->vuelo = $request->vuelo;
            }

            //$viaje->servicio_up_id = $request->usuario_aplicación;

            if ($viaje->save()) {

                if ($request->conductorCambiado==1) {

                    $horaMaxima = date('H:i',strtotime('+30 minute',strtotime($viaje->hora_viaje)));

                    if($actualDate<=$viaje->fecha_viaje){

                        if($actualTime<=$horaMaxima){

                            //cuando se cambia de conductor toca iniciar aceptado = 0, por si el conductor habia aceptado antes y tiene que aceptar otro conductor
                            //$servicio = Viaje::find($viaje->id);
                            //$servicio->fk_estado = 1; //Colocar el estado de PENDIENTE POR ACEPTAR
                            //$servicio->save();

                            $centrodecosto = Centrosdecosto::find($clienteOld);

                            $notificacionConductorAntiguo = 'Tu viaje de '.$centrodecosto->razonsocial.' del '.$fechaOld.'  fue reasignado a otro conductor.';

                            $notificacionConductorNuevo = 'Tienes un nuevo viaje para el '.$request->fecha_viaje.', a las: '.$request->hora_viaje.'. Presiona aquí para ver más detalles.';

                            //Notificacion de viaje a conductor que lo tenía
                            Viaje::NotificacionConductorAntiguo($notificacionConductorAntiguo, $conductorOld);

                            //Notificacion de viaje asignado a nuevo conductor
                            Viaje::NotificacionConductorNuevo($nuevo_servicio, $request->conductor);
                        }
                        
                    }

                }

                if($viaje->app_user_id!=null && $viaje->app_user_id!=0){ //Si es servicio de la app

                    $actualDate = date('Y-m-d');
                    $actualTime = date('H:i');

                    $horaMaxima = date('H:i',strtotime('+30 minute',strtotime($viaje->hora_viaje)));

                    if($actualDate<=$viaje->fecha_viaje){

                        if($actualTime<=$horaMaxima){

                            if ($request->conductorCambiado==1 || $request->fechaCambiada==1 || $request->horaCambiada==1) {

                                $messageCliente = "Le informamos cambio de ";
                                $messageClienteEn = "AOTOUR informs you change of ";

                                if($request->conductorCambiado==1){
                                    $messageCliente = $messageCliente.'CONDUCTOR, ';
                                    $messageClienteEn = $messageClienteEn.'DRIVER, ';
                                }
                                if($request->horaCambiada==1){
                                    $messageCliente = $messageCliente.'HORA, ';
                                    $messageClienteEn = $messageClienteEn.'TIME, ';
                                }
                                if($request->fechaCambiada==1){
                                    $messageCliente = $messageCliente.'FECHA, ';
                                    $messageClienteEn = $messageClienteEn.'DATE, ';
                                }

                                $messageCliente = $messageCliente.'en su traslado programado para el '.$fechaOld.'.';
                                $messageClienteEn = $messageClienteEn.'on your scheduled transfer on '.$fechaOld.'.';

                                Viaje::NotificacionCambiosUp($messageCliente, $messageClienteEn, $viaje->app_user_id);

                            }

                        }

                    }

                }else{//sino, confirmar por correo a los pasajeros y whatsapp

                    $actualDate = date('Y-m-d');
                    $actualTime = date('H:i');

                    //si la fecha y la hora actuales son menores a la fecha y hora del servicio
                    $horaMaxima = date('H:i',strtotime('+30 minute',strtotime($viaje->hora_viaje)));

                    if($actualDate<=$viaje->fecha_viaje){

                        if($actualTime<=$horaMaxima){
                            //si hubo cambios en el conductor, fecha u hora.
                            //if ($conductor!=$servicios->conductor_id || $hora_servicio!=$servicios->hora_servicio || $fecha_servicio!=$servicios->fecha_servicio || $recoger_en!=$servicios->recoger_en || $dejar_en!=$servicios->dejar_en) {
                            if(1>4){

                                $passengers = DB::table('pasajeros_ejecutivos')->where('fk_viaje',$request->id)->get();

                                foreach ($passengers as $pass) {
                                    
                                    $nombre = $pass->nombre;
                                    $correo = $pass->correo;

                                    /*Mail::send('ruta', $data, function($message) use ($correo){
                                        $message->from('no-reply@aotour.com.co', 'Notificaciones Aotour');
                                        $message->to($correo)->subject('Actualización de Servicio');
                                    });*/

                                    if($pass->celular!=null) {

                                        $celular = intval($pass->indicativo.$pass->celular);

                                        $fecha = $request->fecha_viaje;
                                        $hora = $request->hora_viaje;

                                        $vehiculo = DB::table('vehiculos')
                                        ->where('id',$request->fk_vehiculo)
                                        ->first();

                                        $conductor = DB::table('conductores')
                                        ->where('id',$request->fk_conductor)
                                        ->first();

                                        $trayecto = 'Colocar aquí los destinos';

                                        Viaje::ActualizacionViaje($celular, $nombre, $fecha, $hora, $conductor->primer_nombre, $vehiculo->placa, $trayecto, $viaje->id);

                                    }

                                }

                            }

                        }

                    }

                }

                if ($request->cambios!=null) {

                    for ($t=0; $t < count($request->cambios) ; $t++) {
                        
                        $ediciones_servicios = DB::table('edicion_de_servicios')
                        ->insert([
                            'cambios' => $request->cambios[$t],
                            'created_at' => date('Y-m-d H:i:s'),
                            'creado_por' => Auth::user()->id,
                            'fk_viaje' => $viaje->id
                        ]);
                        
                    }

                    return Response::json([
                        'response' => true,
                        'message' => 'Se ha modificado el viaje exitosamente!'
                    ]);

                }

                return Response::json([
                    'response' => true,
                    'message' => 'Se ha modificado el viaje exitosamente!'
                ]);

            }else{

                return Response::json([
                    'response' => false,
                    'message' => 'Opps! Parece que ocurrió un error al intentar modificar este viaje. Comunícate con el administrador del sistema e indícale el número de viaje: '.$viaje->id
                ]);

            }
        }

    }

    public function scheduletripremoval(Request $request) {

        #TOMAR ID DEL SERVICIO
        $id = $request->id;

        #BUSCAR SERVICIO POR ID
        $viaje = Viaje::find($request->id);
        $viaje->motivo_eliminacion = $request->motivo_eliminacion;
        $viaje->estado_eliminacion = 1;
        $viaje->usuario_eliminacion = Auth::user()->id;
        $viaje->fecha_solicitud_eliminacion = date('Y-m-d H:i:s');

        if($viaje->save()){

            $centrodecosto = DB::table('centrosdecosto')
            ->where('id',$viaje->fk_centrodecosto)
            ->first();

            Viaje::notificarViajeCancelado($viaje->fk_conductor, $centrodecosto->razonsocial, $viaje->fecha_viaje, $viaje->hora_viaje);
        }

        return Response::json([
            'response' => true
        ]);

    }

    public function deletetrip(Request $request) {

        $viaje = Viaje::find($request->id);
        $viaje->estado_papelera = 1;
        $viaje->save();

        return Response::json([
            'response'=>true
        ]);

    }

    public function declinedeletetrip(Request $request) {

        $viaje = Viaje::find($request->id);
        //$viaje->motivo_eliminacion = null;
        $viaje->estado_eliminacion = null;
        //$viaje->usuario_eliminacion = Auth::user()->id;
        //$viaje->fecha_solicitud_eliminacion = date('Y-m-d H:i:s');

        if($viaje->save()){

            //$centrodecosto = DB::table('centrosdecosto')
            //->where('id',$viaje->fk_centrodecosto)
            //->first();

            //Viaje::notificarViajeCancelado($viaje->fk_conductor, $centrodecosto->razonsocial, $viaje->fecha_viaje, $viaje->hora_viaje);

            return Response::json([
                'response' => true
            ]);

        }

    }

    public function listtripsbyremove(Request $request) {

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
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        where v.estado_eliminacion = 1 and v.estado_papelera is null
        GROUP BY v.id";
        
        $viajes = DB::select($viajes);

        return Response::json([
            'response' => true,
            'viajes' => $viajes
        ]);

    }

    public function listbin(Request $request) {

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
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        where v.estado_papelera = 1
        GROUP BY v.id";
        
        $viajes = DB::select($viajes);

        return Response::json([
            'response' => true,
            'viajes' => $viajes
        ]);

    }

    public function showtracking(Request $request) {

        $gps = DB::table('gps')
        ->where('fk_viaje',$request->id)
        ->first();

        $destinos = DB::table('destinos')
        ->where('fk_viaje',$request->id)
        ->get();

        if($gps) {

            $value = json_decode($gps->coordenadas);

            $cont = count($value);

            $last = $value[$cont-1];

            return Response::json([
                'response' => true,
                'ultima_ubicacion' => $last,
                'destinos' => $destinos,
                //'gps' => $gps
            ]);

        }else{

            $viaje = Viaje::find($request->id);

            if($viaje->fk_estado==1) { //Validar que sea estado finalizado
                $text = ' Puede que se deba a que el conductor no activó la ubicación o no tenía conexión a internet durante la ejecución del viaje';
            }else if($viaje->estado==2) { //Validar que sea estado en servicio
                $text = ' Puede ser que el conductor no tenga la ubicación activa o que no disponga de conexión a internet.';
            }else{
                $text = '';
            }

            return Response::json([
                'response' => false,
                'message' => 'Opps! Parece que este servicio no ha registrado GPS.'.$text,
                'destinos' => $destinos
            ]);

        }

    }

    public function shownovs(Request $request) {

        $novedades = DB::table('novedades_de_viajes')
        ->select('novedades_de_viajes.*','users.first_name','users.last_name', 'estados.nombre as nombre_estado', 'estados.codigo as codigo_estado', 'tipos.nombre as nombre_tipo', 'tipos.codigo as codigo_tipo')
        ->join('users', 'novedades_de_viajes.fk_user', '=', 'users.id')
        ->join('estados', 'estados.id', '=',  'novedades_de_viajes.fk_estado')
        ->join('tipos', 'tipos.id', '=',  'novedades_de_viajes.tipo')
        ->where('fk_viaje',$request->id)
        ->get();

        if(count($novedades)<1) {
            $novedades = null;
        }

        $ediciones = DB::table('edicion_de_servicios')
        ->select('edicion_de_servicios.cambios', 'edicion_de_servicios.creado_por','edicion_de_servicios.created_at', 'users.first_name','users.last_name')
        ->where('fk_viaje', $request->id)
        ->leftJoin('users','edicion_de_servicios.creado_por', '=','users.id')
        ->get();

        if(count($ediciones)<1) {
            $ediciones = null;
        }

        $cambiosFacturacion = DB::table('edicion_de_facturacion')
        ->select('edicion_de_facturacion.cambios', 'edicion_de_facturacion.creado_por','edicion_de_facturacion.created_at', 'users.first_name','users.last_name')
        ->where('fk_viaje', $request->id)
        ->leftJoin('users','edicion_de_facturacion.creado_por', '=','users.id')
        ->get();

        if(count($cambiosFacturacion)<1) {
            $cambiosFacturacion = null;
        }

        return Response::json([
            'respose' => true,
            'cambios_facturacion' => $cambiosFacturacion,
            'cambios_viaje' => $ediciones,
            'novedades' => $novedades
        ]);

    }

    //...
    public function addnov(Request $request) {

        //Validar si el servicio ya está facturado

        $novedad = new NovedadViaje;
        $novedad->tipo = $request->tipo;
        $novedad->detalles = $request->detalles;
        $novedad->fk_viaje = $request->viaje;
        $novedad->fk_estado = 55;
        $novedad->fk_user = Auth::user()->id;
        $novedad->save();

        return Response::json([
            'response' => true
        ]);

    }

}
