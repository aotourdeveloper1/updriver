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

    public function guardaridregistration(Request $request) {

        $id = $request->conductor_id;
        $registration_id = $request->registrationid;
        $device = $request->device;

        $registration = DB::table('conductores')
        ->where('id', $id)
        ->update([
            'idregistrationdevice' => $registration_id,
            'device' => $device
        ]);


        return Response::json([
            'response' => true,
            'registrationid' => $registration_id,
            'version' => 1
        ]);

    }

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
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT COUNT(*) FROM viajes v3 left join pasajeros_rutas_qr pax on pax.fk_viaje = v3.id where v3.id = v.id) as total_pasajeros_ruta,
        (SELECT COUNT(*) FROM viajes v4 left join pasajeros_ejecutivos pass on pass.fk_viaje = v4.id where v4.id = v.id) as total_pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        left join tipos t on t.id = v.tipo_traslado 
        left join tipos t2 on t2.id = v.tipo_ruta
        WHERE `fk_conductor` = ".$id." AND v.fecha_viaje between '".$diaanterior."' and '".$diasiguiente."' AND v.estado_eliminacion is null and v.estado_papelera is null
        GROUP BY v.id order by v.fecha_viaje asc, v.hora_viaje asc";

        //v.fk_estado = 57 and

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
                'response' => false,
                'servicios' => $viajes,
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
		v.fecha_viaje as fecha_servicio,
		v.hora_viaje as hora_servicio,
        c.razonsocial,  
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        v.tipo_traslado,
        t.nombre as nombre_tipo_traslado,
        t.codigo as codigo_tipo_traslado,
        v.tipo_ruta,
        t2.nombre as tipo_de_ruta,
        t2.codigo as codigo_tipo_ruta,
        v.recoger_pasajero,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT COUNT(*) FROM viajes v3 left join pasajeros_rutas_qr pax on pax.fk_viaje = v3.id where v3.id = v.id) as total_pasajeros_ruta,
        (SELECT COUNT(*) FROM viajes v4 left join pasajeros_ejecutivos pass on pass.fk_viaje = v4.id where v4.id = v.id) as total_pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        left join tipos t on t.id = v.tipo_traslado 
        left join tipos t2 on t2.id = v.tipo_ruta
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
                'response' => false,
                'viajes' => $viajes,
            ]);

        }

    }

    public function viajeentendido(Request $request) {

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

        $pasajeros = "SELECT pr.id, pr.nombre, pr.celular, pr.direccion, pr.barrio, pr.localidad, pr.estado_ruta, t.nombre as nombre_estado, pr.recoger_a as sw, pr.location FROM pasajeros_rutas_qr pr left join tipos t on t.id = pr.estado_ruta where fk_viaje = ".$viaje_id."";
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

    public function listarpasajerosejecutivos(Request $request) {

        $viaje_id = $request->viaje_id;

        $pasajeros = "SELECT nombre, celular FROM pasajeros_ejecutivos where fk_viaje = ".$viaje_id."";
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

                $querys = "SELECT id, fecha_viaje as fecha_servicio, hora_viaje as hora_servicio, detalle_recorrido, fk_estado, recoger_pasajero, tipo_servicio, codigo_viaje, tipo_traslado, tipo_ruta from viajes where id = ".$viaje->id."";
                $consulta = DB::select($querys);

                return Response::json([
                    'response' => true,
                    'viaje' => $consulta[0]
                ]);

            }

        }

    }

    public function tracker(Request $request) {

        $id = $request->viaje_id;

        $latitude =  substr($request->latitude, 0, 10);
        $longitude = substr($request->longitude, 0, 10);
        $speed = substr(($request->speed)*3.6, 0, 8);

        //Objeto array json
        $objArray = null;

        //Array a insertar en json
        $array = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $gps = DB::table('gps')
        ->where('fk_viaje', $id)
        ->first();

        if($latitude!=null) {

            if (!$gps) { //primer tracking

                $gps = new Gps;
                $gps->fk_viaje = $id;
                $gps->coordenadas = json_encode([$array]);

                $gps->save();

                return Response::json([
                    'response' => true
                ]);

            }else{

                $objArray = json_decode($gps->coordenadas);
                array_push($objArray, $array);

                $gps = DB::table('gps')
                ->where('fk_viaje', $id)
                ->update([
                    'coordenadas' => json_encode($objArray)
                ]);

                return Response::json([
                    'response' => true
                ]);

            }


            /*if ($servicio->app_user_id!=null) {

                $user_id = $servicio->app_user_id;

                $channel = 'aotour_mobile_client_user_'.$user_id;
                $name = 'servicio_activo';

                $data = json_encode([
                'ultima_ubicacion' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
                'servicio_id' => $id,
                'estado_servicio_app' => $servicio->estado_servicio_app
                ]);

                //Viaje::enviarNotificacionPusher($channel, $name, $data);

            }*/
        
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
		v.fecha_viaje as fecha_servicio,
		v.hora_viaje as hora_servicio,
        c.razonsocial,  
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        v.tipo_traslado,
        t.nombre as nombre_tipo_traslado,
        t.codigo as codigo_tipo_traslado,
        v.tipo_ruta,
        v.recoger_pasajero,
        t2.nombre as tipo_de_ruta,
        t2.codigo as codigo_tipo_ruta,
        sub.coords,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT COUNT(*) FROM viajes v3 left join pasajeros_rutas_qr pax on pax.fk_viaje = v3.id where v3.id = v.id) as total_pasajeros_ruta,
        (SELECT COUNT(*) FROM viajes v4 left join pasajeros_ejecutivos pass on pass.fk_viaje = v4.id where v4.id = v.id) as total_pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left JOIN subcentrosdecosto sub on sub.id = v.fk_subcentrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        left join tipos t on t.id = v.tipo_traslado 
        left join tipos t2 on t2.id = v.tipo_ruta
        WHERE `fk_conductor` = ".$id." AND v.fecha_viaje between '".$diaanterior."' and '".$diasiguiente."' AND v.fk_estado = 59 and v.estado_eliminacion is null and v.estado_papelera is null
        GROUP BY v.id order by v.fecha_viaje asc, v.hora_viaje asc limit 1";

        $viajes = DB::select($consulta);

        if ($viajes) {

            return Response::json([
                'response' => true,
                'viajes' => $viajes[0],
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

        $usuario = PasajeroRutaQr::find($id);
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
        $nombreCond = $request->nombre_conductor;

        $viaje = Viaje::find($viaje_id);
        $viaje->recoger_pasajero = 0;

        if($viaje->save()){

            //Notificar esperando por WhatsApp
            if($viaje->app_user_id!=null){ //ejecutivo de aplicación
                $notifications = Viaje::Enespera($viaje_id, $viaje->app_user_id);
            }else if($viaje->tipo_traslado!=70){

                $pax = "select id, nombre, indicativo, celular, correo from pasajeros_ejecutivos where fk_viaje = ".$viaje_id."";
                $paxs = DB::select($pax);

                foreach ($paxs as $pass) {
                    
                    //envío de whatsapp
                    if($pass->celular!='' and $pass->celular!=null){

                        $numero = $pass->celular;
                        
                        $nombreConductor = explode(' ', $nombreCond);
                        
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

        $usuario = PasajeroRutaQr::find($id);
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
		v.fecha_viaje as fecha_servicio,
		v.hora_viaje as hora_servicio,
        c.razonsocial,  
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        v.tipo_traslado,
        t.nombre as nombre_tipo_traslado,
        t.codigo as codigo_tipo_traslado,
        v.tipo_ruta,
        t2.nombre as tipo_de_ruta,
        t2.codigo as codigo_tipo_ruta,
        v.recoger_pasajero,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT COUNT(*) FROM viajes v3 left join pasajeros_rutas_qr pax on pax.fk_viaje = v3.id where v3.id = v.id) as total_pasajeros_ruta,
        (SELECT COUNT(*) FROM viajes v4 left join pasajeros_ejecutivos pass on pass.fk_viaje = v4.id where v4.id = v.id) as total_pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        left join tipos t on t.id = v.tipo_traslado 
        left join tipos t2 on t2.id = v.tipo_ruta
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
                'response' => false,
                'viajes' => $viajes
            ]);

        }

    }

    public function historialmes(Request $request) {

        $conductor_id = $request->conductor_id;
        $mes = $request->mes;
        
        $meses = explode('-', $mes);

        $ano = $meses[0];
        $mes = $meses[1];

        $fechaInicial = $ano.'-'.$mes.'-01';
        $fechaFinal =  $ano.'-'.$mes.'-31';

        $consulta = "SELECT
		v.id,
		v.fk_estado,
		v.detalle_recorrido,
		v.fecha_viaje as fecha_servicio,
		v.hora_viaje as hora_servicio,
        c.razonsocial,  
        est.nombre as nombre_estado,
        est.codigo as codigo_estado,
        v.tipo_traslado,
        t.nombre as nombre_tipo_traslado,
        t.codigo as codigo_tipo_traslado,
        v.tipo_ruta,
        t2.nombre as tipo_de_ruta,
        t2.codigo as codigo_tipo_ruta,
        v.recoger_pasajero,
        JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
        (SELECT COUNT(*) FROM viajes v3 left join pasajeros_rutas_qr pax on pax.fk_viaje = v3.id where v3.id = v.id) as total_pasajeros_ruta,
        (SELECT COUNT(*) FROM viajes v4 left join pasajeros_ejecutivos pass on pass.fk_viaje = v4.id where v4.id = v.id) as total_pasajeros_ejecutivos
        FROM
            viajes v
        left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
        left join destinos d on d.fk_viaje = v.id 
        -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
        left join estados est on est.id = v.fk_estado 
        left join tipos t on t.id = v.tipo_traslado 
        left join tipos t2 on t2.id = v.tipo_ruta
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
                'response' => false,
                'viajes' => $viajes
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
                'response' => false,
                'novedades' => $novedades
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

        $view = View::make('plantilla_constancia_vieja')->with([
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

    //..validar el código de recogida de pasajero
    public function pasajerorecogido(Request $request) {

        $viaje_id = $request->viaje_id;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $codigo = $request->codigo;

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

            $consulta = "SELECT
            v.id,
            v.fk_estado,
            v.detalle_recorrido,
            v.fecha_viaje as fecha_servicio,
            v.hora_viaje as hora_servicio,
            c.razonsocial,  
            est.nombre as nombre_estado,
            est.codigo as codigo_estado,
            v.tipo_traslado,
            t.nombre as nombre_tipo_traslado,
            t.codigo as codigo_tipo_traslado,
            v.tipo_ruta,
            t2.nombre as tipo_de_ruta,
            t2.codigo as codigo_tipo_ruta,
            v.recoger_pasajero,
            JSON_ARRAYAGG(JSON_OBJECT('direccion', d.direccion)) as destinos,
            (SELECT COUNT(*) FROM viajes v3 left join pasajeros_rutas_qr pax on pax.fk_viaje = v3.id where v3.id = v.id) as total_pasajeros_ruta,
            (SELECT COUNT(*) FROM viajes v4 left join pasajeros_ejecutivos pass on pass.fk_viaje = v4.id where v4.id = v.id) as total_pasajeros_ejecutivos
            FROM
                viajes v
            left JOIN centrosdecosto c on c.id = v.fk_centrodecosto
            left join destinos d on d.fk_viaje = v.id 
            -- left join pasajeros_ejecutivos pax on pax.fk_viaje = v.id 
            left join estados est on est.id = v.fk_estado 
            left join tipos t on t.id = v.tipo_traslado 
            left join tipos t2 on t2.id = v.tipo_ruta
            WHERE v.id = ".$viaje_id." 
            GROUP BY v.id";

            $viajes = DB::select($consulta);

            return Response::json([
                'response' => true,
                'viaje' => $viajes[0]
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
                    ->where('id_empleado', $user->id_empleado)
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

    public function obtenerconductor(Request $request) {

        $id = $request->conductor_id;

        $conductor = DB::table('conductores')
        ->where('id',$id)
        ->first();

        return Response::json([
            'response' => $conductor
        ]);

    }

    public function gps(Request $request) {

        $gps = DB::table('gps')
        ->where('fk_viaje', $request->viaje_id)
        ->first();

        if($gps) {

            return Response::json([
                'response' => true,
                'gps' => json_decode($gps->coordenadas)
            ]);

        }else{

            return Response::json([
                'response' => false,
                'message' => 'Opps! Parece que este servicio no ha registrado GPS.'
            ]);

        }

    }

    public function notificaciones(Request $request) {

        $id = $request->conductor_id;

        $fechaActual = date('Y-m-d');
        $horaActual = date('H:i');

        $notificaciones = DB::table('notificaciones_conductores')
        //->leftJoin('viajes', 'viajes.id', '=', 'notificaciones_conductores.id_servicio', 'seevicios.estado_servicio_app')
        //->select('notificaciones.*', 'servicios.fecha_servicio', 'servicios.hora_servicio')
        ->where('conductor_id',intval($id))
        //->where('servicios.fecha_servicio', '<=', $fechaActual)
        ->whereNull('leido')
        ->orderBy('fecha', 'DESC')
        ->get();

        $contador = count($notificaciones);

        if($contador>0) {

            $update = DB::table('notificaciones_conductores')
            ->where('conductor_id',intval($id))
            ->whereNull('leido')
            ->update([
                'leido' => 1
            ]);

            return Response::json([
                'response' => true,
                'contador' => $contador
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function listarnotificaciones(Request $request) {

        $id = $request->conductor_id;

        $fechaActual = date('Y-m-d');
        $horaActual = date('H:i');

        $servicios = DB::table('viajes')
        ->select('viajes.id')
        ->where('app_user_id',$id)
        ->whereNull('estado_eliminacion')
        ->whereNull('estado_papelera')
        ->where('fecha_viaje', '>=', $fechaActual)
        ->get();

        $notificaciones = DB::table('notificaciones_conductores')
        ->leftJoin('viajes', 'viajes.id', '=', 'notificaciones_conductores.id_servicio')
        ->select('notificaciones_conductores.*', 'viajes.fecha_viaje as fecha_servicio', 'viajes.hora_viaje as hora_servicio')
        ->where('conductor_id', intval($id))
        ->where('viajes.fecha_viaje', '>=', $fechaActual)
        ->orderBy('fecha', 'DESC')
        ->get();

        if($notificaciones){

            $update = DB::table('notificaciones_conductores')
            ->where('conductor_id', intval($id))
            ->whereNull('leido')
            ->update([
                'leido'=> 1
            ]);

            return Response::json([
                'response' => true,
                'notificaciones' => $notificaciones,
                'viajes' => $servicios
            ]);

        }else{

            $update = DB::table('notificaciones_conductores')
            ->where('conductor_id',intval($id))
            ->whereNull('leido')
            ->update([
                'leido'=> 1
            ]);

            return Response::json([
                'response' => false,
                'usuario' => $id,
                'notificaciones' => $notificaciones,
                'viajes' => $servicios
            ]);

        }

    }

    public function listartiposnovedades(Request $request) {

        $novedades = DB::table('tipos')
        ->select('id', 'codigo', 'nombre')
        ->where('fk_tipo_maestros', 29)
        ->whereIn('id',[87, 88, 91])
        ->get();

        return Response::json([
            'response' => true,
            'novedades' => $novedades
        ]);

    }

    public function contactos(Request $request) {

        $barranquilla_movil = DB::table('contactos')
        ->where('ciudad','BARRANQUILLA')
        ->where('tipo','movil')
        ->get();

        $barranquilla_email = DB::table('contactos')
        ->where('ciudad','BARRANQUILLA')
        ->where('tipo','email')
        ->get();

        $bogota_movil = DB::table('contactos')
        ->where('ciudad','BOGOTA')
        ->where('tipo','movil')
        ->get();

        $bogota_email = DB::table('contactos')
        ->where('ciudad','BOGOTA')
        ->where('tipo','email')
        ->get();

        return Response::json([
            'respuesta'=>true,
            'barranquilla_movil' => $barranquilla_movil,
            'barranquilla_email' => $barranquilla_email,
            'bogota_movil' => $bogota_movil,
            'bogota_email' => $bogota_email
        ]);

    }

}
