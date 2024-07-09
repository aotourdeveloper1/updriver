<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NotificationDriver;
use App\Http\Controllers\ConfigController;

use Auth;
use Response;
Use DB;

class Viaje extends Model
{
    protected $table = 'viajes';

    //notificaciones Whatsapp
    public static function notificarInicioRutaEntrada($number, $direccion, $id) {

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
            \"name\": \"inicio_ruta\",
            \"language\": {
            \"code\": \"es\",
            },
            \"components\": [{
            \"type\": \"body\",
            \"parameters\": [{
                \"type\": \"text\",
                \"text\": \"".$direccion."\",
            }]
            },
            {
            \"type\": \"button\",
            \"sub_type\": \"url\",
            \"index\": \"0\",
            \"parameters\": [{
                \"type\": \"payload\",
                \"payload\": \"".$id."\"
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

        return $response;

    }

    public static function ServicioIniciadoWhatsApp($id, $indicativo, $phone, $viaje){//RECONFIRMADOR PARA EL PASAJERO WAA
        
        if($indicativo==null or $indicativo=='') {
            $indicativo = 57;
        }

        $number = $indicativo.$phone;
  
        $number = intval($number);

        $codigo = $viaje->codigo_viaje;
  
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
            \"name\": \"iniciarviaje\",
            \"language\": {
              \"code\": \"es\",
            },
            \"components\": [{
              \"type\": \"body\",
              \"parameters\": [{
                \"type\": \"text\",
                \"text\": \"".$codigo."\",
              }]
            },
            {
              \"type\": \"button\",
              \"sub_type\": \"url\",
              \"index\": \"0\",
              \"parameters\": [{
                \"type\": \"payload\",
                \"payload\": \"".$id."\"
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

        return $response;
  
    }

    public static function esperaEjecutivo($viaje, $number, $nombreConductor, $cel, $indicativo){//RECONFIRMADOR PARA EL PASAJERO WAA

        $number = intval($indicativo.$number);
        
        $recogida = DB::table('destinos')
        ->where('fk_viaje', $viaje->id)
        ->where('orden', 1)
        ->first();

        $recogerEn = $recogida->direccion;
        $codes = $viaje->codigo_viaje;
  
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
            \"name\": \"waiting\",
            \"language\": {
              \"code\": \"es\",
            },
            \"components\": [{
              \"type\": \"body\",
              \"parameters\": [{
                \"type\": \"text\",
                \"text\": \"".$nombreConductor."\",
              },
              {
                \"type\": \"text\",
                \"text\": \"".$recogerEn."\",
              },{
                \"type\": \"text\",
                \"text\": \"".$codes."\",
              },{
                \"type\": \"text\",
                \"text\": \"".$cel."\",
              }]
            },
            {
              \"type\": \"button\",
              \"sub_type\": \"url\",
              \"index\": \"0\",
              \"parameters\": [{
                \"type\": \"payload\",
                \"payload\": \"".$viaje->id."\"
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

        return $response;
  
    }

    public static function esperaRutaWhatsapp($number, $name, $recogerEn, $contacto, $usuario_id) {

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
            \"name\": \"espera_ruta\",
            \"language\": {
            \"code\": \"es\",
            },
            \"components\": [{
            \"type\": \"body\",
            \"parameters\": [{
                \"type\": \"text\",
                \"text\": \"".$name."\",
            },
            {
                \"type\": \"text\",
                \"text\": \"".$recogerEn."\",
            },
            {
                \"type\": \"text\",
                \"text\": \"".$contacto."\",
            }]
            },
            {
            \"type\": \"button\",
            \"sub_type\": \"url\",
            \"index\": \"0\",
            \"parameters\": [{
                \"type\": \"payload\",
                \"payload\": \"".$usuario_id."\"
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

        return $response;

    }

    public static function usuarioActualWhatsapp($number, $recogerEn, $usuario_id) {

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
            \"name\": \"recoger_pasajero\",
            \"language\": {
            \"code\": \"es\",
            },
            \"components\": [{
            \"type\": \"body\",
            \"parameters\": [{
                \"type\": \"text\",
                \"text\": \"".$recogerEn."\",
            }]
            },
            {
            \"type\": \"button\",
            \"sub_type\": \"url\",
            \"index\": \"0\",
            \"parameters\": [{
                \"type\": \"payload\",
                \"payload\": \"".$usuario_id."\"
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
        
        return $response;

    }

    public static function notificarNovedadRegistrada($number, $nombreConductor, $fecha, $cliente) {

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
            \"name\": \"novedad_registrada\",
            \"language\": {
            \"code\": \"es\",
            },
            \"components\": [{
            \"type\": \"body\",
            \"parameters\": [{
                \"type\": \"text\",
                \"text\": \"".$nombreConductor."\",
            },
            {
                \"type\": \"text\",
                \"text\": \"".$fecha."\",
            },
            {
                \"type\": \"text\",
                \"text\": \"".$cliente."\",
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

        return $response;

    }

    //notificaciones UP
    public static function RutaIniciada($viaje_id, $idregistrationdevice, $idioma){

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $id = $idregistrationdevice;
  
        if($idioma==='en'){
  
            $title = 'Trip Started!';
            $message = 'Your Route is now available for tracking. Click here to go.';
  
        }else{
  
            $title = '¡Viaje Iniciado!';
            $message = 'Tu ruta se encuentra disponible para hacer tracking. Click aquí para ir.';
  
        }
  
        $fields = array (
          'registration_ids' => array (
            $id
          ),
          'notification' => array (
            "body" => $message,
            "title" => $title,
            "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
            "vibration_pattern" => $vibration_pattern
          ),
          'data' => array (
            "id" => 4,
            "servicio" => $viaje_id,
            "screen" => 'current',
          )
        );
        $fields = json_encode ( $fields );
  
        $headers = array (
          'Authorization: key=' . $key,
          'Content-Type: application/json'
        );
  
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
  
        $result = curl_exec ( $ch );

        return $result;
  
    }

    public static function usuarioActual($servicio_id, $idregistrationdevice, $idioma){ //Notificación al pasajero que van por el

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $id = $idregistrationdevice;
  
        if($idioma==='en'){
  
            $title = 'Your driver is on his way...';
            $message = 'Your driver is heading your way. We will notify you when it arrives at your location.';
  
        }else{
  
            $title = 'Tu conductor viene en camino...';
            $message = 'Tu conductor se dirige a tu dirección. Te notificaremos cuando llegue a tu ubicación.';
  
        }
  
        $fields = array (
          'registration_ids' => array (
            $id
          ),
          'notification' => array (
            "body" => $message,
            "title" => $title,
            "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
            "vibration_pattern" => $vibration_pattern
          ),
          'data' => array (
            "id" => 4,
            "servicio" => $servicio_id,
            "screen" => 'current',
          )
        );
        $fields = json_encode ( $fields );
  
        $headers = array (
          'Authorization: key=' . $key,
          'Content-Type: application/json'
        );
  
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
  
        $result = curl_exec ( $ch );

        return $result;
  
    }

    public static function Enespera($servicio_id, $user){

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $usuario = DB::table('users')
        ->where('id', $user)
        ->first();
  
        if ($usuario->idregistrationdevice!=null and $usuario->idregistrationdevice!='') {
  
          $id = $usuario->idregistrationdevice;
  
          if($usuario->idioma==='en'){
  
            $title = 'In pick up location...';
            $message = 'Your driver is waiting for you at the pick-up location!';
  
          }else{
  
            $title = 'En lugar de recogida...';
            $message = '¡Tu conductor te está esperando en el lugar de recogida!';
  
          }
  
          $fields = array (
            'registration_ids' => array (
              $id
            ),
            'notification' => array (
              "body" => $message,
              "title" => $title,
              "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
              "vibration_pattern" => $vibration_pattern
            ),
            'data' => array (
              "id" => 4,
              "servicio" => $servicio_id,
              "screen" => 'waiting',
            )
          );
          $fields = json_encode ( $fields );
  
          $headers = array (
            'Authorization: key=' . $key,
            'Content-Type: application/json'
          );
  
          $ch = curl_init ();
          curl_setopt ( $ch, CURLOPT_URL, $url );
          curl_setopt ( $ch, CURLOPT_POST, true );
          curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
          curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
          curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
  
          $result = curl_exec ( $ch );
          //curl_close ( $ch );
  
        }
  
    }

    public static function esperaRutaUp($viaje_id, $idregistrationdevice, $idioma){ //Notificación al pasajero que van por el

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $id = $idregistrationdevice;
  
        if($idioma==='en'){
  
            $title = 'Your driver has arrived...';
            $message = 'Your driver is waiting for you. Click here to find out the location of your driver.';
  
        }else{
  
            $title = 'Tu conductor ha llegado...';
            $message = 'Tu conductor te está esperando. Presiona aquí para conocer la ubicación de tu conductor.';
  
        }
  
        $fields = array (
          'registration_ids' => array (
            $id
          ),
          'notification' => array (
            "body" => $message,
            "title" => $title,
            "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
            "vibration_pattern" => $vibration_pattern
          ),
          'data' => array (
            "id" => 4,
            "servicio" => $viaje_id,
            "screen" => 'current',
          )
        );
        $fields = json_encode ( $fields );
  
        $headers = array (
          'Authorization: key=' . $key,
          'Content-Type: application/json'
        );
  
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
  
        $result = curl_exec ( $ch );

        return $response;
  
    }

    public static function bienvenidoaBordoUp($viaje_id, $idregistrationdevice, $idioma){ //Notificación al pasajero que van por el

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $id = $idregistrationdevice;
  
        if($idioma==='en'){
  
            $title = 'Welcome aboard!';
            $message = 'In moments we will head to the destination point.';
  
        }else{
  
            $title = '¡Bienvenido a Bordo!';
            $message = 'En instantes nos dirigiremos al punto de destino.';
  
        }
  
        $fields = array (
          'registration_ids' => array (
            $id
          ),
          'notification' => array (
            "body" => $message,
            "title" => $title,
            "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
            "vibration_pattern" => $vibration_pattern
          ),
          'data' => array (
            "id" => 4,
            "servicio" => $viaje_id,
            "screen" => 'current',
          )
        );
        $fields = json_encode ( $fields );
  
        $headers = array (
          'Authorization: key=' . $key,
          'Content-Type: application/json'
        );
  
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
  
        $result = curl_exec ( $ch );

        return $result;
  
    }

    public static function pasajeroRecogidoUp($viaje_id, $user){

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $usuario = DB::table('users')
        ->select('id', 'idregistrationdevice', 'idioma')
        ->where('id', $user)
        ->first();
  
        if ($usuario->idregistrationdevice!=null and $usuario->idregistrationdevice!='') {
  
            $id = $usuario->idregistrationdevice;
    
            if($usuario->idioma==='en'){
    
                $title = 'On the way to destination';
                $message = 'Your trip has started. We hope you enjoy your trip.';
    
            }else{
    
                $title = 'En camino hacia punto de destino';
                $message = 'Tu viaje a iniciado. Esperamos que disfrutes tu viaje.';
    
            }
    
            $fields = array (
                'registration_ids' => array (
                $id
                ),
                'notification' => array (
                "body" => $message,
                "title" => $title,
                "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
                "vibration_pattern" => $vibration_pattern
                ),
                'data' => array (
                "id" => 4,
                "servicio" => $viaje_id,
                "screen" => 'waiting',
                )
            );
            $fields = json_encode ( $fields );
    
            $headers = array (
                'Authorization: key=' . $key,
                'Content-Type: application/json'
            );
    
            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_POST, true );
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
    
            $result = curl_exec ( $ch );
    
            return $result;
  
        }
  
    }

    public static function finalizaciondeviajeUp($viaje, $user){ //NOTIFICACIÓN UPDATE SERVICIO DE APP CLIENTE

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $usuario = DB::table('users')
        ->select('id', 'idregistrationdevice', 'idioma')
        ->where('id', $user)
        ->first();
  
        if ($usuario->idregistrationdevice!=null and $usuario->idregistrationdevice!='') {
  
            $id = $usuario->idregistrationdevice;
    
            if($usuario->idioma==='en'){
    
                $title = 'Service Finished!';
                $message = 'The Service has been finished. Please dont forget to rate the driver. Click here to go.';
    
            }else{
    
                $title = '¡Servicio Finalizado!';
                $message = 'El Servicio ha sido finalizado. Por favor, no olvide calificar al conductor. Haga clic aquí para ir a calificar.';
    
            }
    
            $fields = array (
                'registration_ids' => array (
                $id
                ),
                'notification' => array (
                "body" => $message,
                "title" => $title,
                "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
                "vibration_pattern" => $vibration_pattern
                ),
                'data' => array (
                "id" => 5,
                "servicio" => $viaje,
                "service" => $viaje,
                "screen" => 'rate'
                )
            );
            $fields = json_encode ( $fields );
    
            $headers = array (
                'Authorization: key=' . $key,
                'Content-Type: application/json'
            );
    
            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_POST, true );
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
    
            $result = curl_exec ( $ch );
    
            return Response::json([
                'response' => true,
                'resultado' => $result
            ]);
  
        }
  
    }

    public static function finalizacionderutaUp($viaje_id, $idregistrationdevice, $idioma){

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $id = $idregistrationdevice;
  
        if($idioma==='en'){
  
            $title = 'Service Started!';
            $message = 'Your Route is now available for tracking. Click here to go.';
  
        }else{
  
            $title = '¡Ruta finalizada!';
            $message = 'Esperamos que hayas disfrutado tu viaje con nosotros. Te agradecemos calificar nuestro servicio.';
  
        }
  
        $fields = array (
          'registration_ids' => array (
            $id
          ),
          'notification' => array (
            "body" => $message,
            "title" => $title,
            "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
            "vibration_pattern" => $vibration_pattern
          ),
          'data' => array (
            "id" => 4,
            "servicio" => $viaje_id,
            "screen" => 'current',
          )
        );
        $fields = json_encode ( $fields );
  
        $headers = array (
          'Authorization: key=' . $key,
          'Content-Type: application/json'
        );
  
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
  
        $result = curl_exec ( $ch );

        return $result;
  
    }





    //AQUÍ INICIAN LAS NOTIFICACIONES A LOS CONDUCTORES
    public static function notificarConductor($conductorId, $fechaServicio, $horaServicio, $idNotificacion, $id_servicio){//ACTUALIZADO

        $countFalse = 0;
        $countTrue = 0;
    
        $url = 'https://fcm.googleapis.com/fcm/send';
    
        $apikey = ConfigController::FIREBASE_KEY_DRIVER;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
    
        //$servicio = Viaje::find($id_servicio);
    
        if (4>2) { //Verificar si tiene el conductor tiene usuario de aplicación para notificar al id
    
            $id_registration = 'fEc6AKkMy0Dns5ZIlIpSMf:APA91bE0KpuAY6xmEX2ixLjyDhvrtvZFQ97rqWtCUY0IJLW6qEwwNX8r37ewQrfTTMDPGGtf-lTwe2ej6yCa2dUzaR47VtfRFZCDWJEz94BrmV8_IqRGhd2WCWlCn4rE5zYeg-ufzRCm'; //revisar
    
            if ($id_registration!=null and $id_registration!='') {
    
                $message = 'Tienes un nuevo viaje para el '.$fechaServicio.', a las: '.$horaServicio.'. Presiona aquí para ver más detalles.';
        
                $id = $id_registration;
        
                $fields = array (
                    'registration_ids' => array (
                    $id
                    ),
                    'notification' => array (
                    "body" => $message,
                    "title" => '¡Nuevo Viaje!',
                    "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png'
                    )
                );
                $fields = json_encode ( $fields );
        
                $headers = array (
                    'Authorization: key=' . $apikey,
                    'Content-Type: application/json'
                );
        
                $ch = curl_init ();
                curl_setopt ( $ch, CURLOPT_URL, $url );
                curl_setopt ( $ch, CURLOPT_POST, true );
                curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
                curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
        
                $result = curl_exec ( $ch );
                curl_close ( $ch );
        
        
                if ($result===FALSE) {
        
                    $countFalse++;
        
                }else{
        
                    $countTrue++;
                    /*Guardar Notificación*/
                    //$notificationSave = new NotificationDriver;
                    //$notificationSave->fecha = date('Y-m-d H:i');
                    //$notificationSave->titulo = 'Nuevo Viaje';
                    //$notificationSave->text = $message;
                    //$notificationSave->id_usuario = $servicio->conductor->user->id; //revisar
                    //$notificationSave->tipo = 6;
                    //$notificationSave->id_servicio = $id_servicio;
                    //$notificationSave->save();
                    /*Guardar Notificación*/
        
                }
    
            }
    
            //$counters['countTrue'] = $countTrue;
            //$counters['countFalse'] = $countFalse;
    
            return $result;
    
        }
    
    }

    public static function NotificacionConductorAntiguo($mensaje, $conductor_id){

        $url = 'https://fcm.googleapis.com/fcm/send';
  
        $apikey = ConfigController::FIREBASE_KEY_DRIVER;
  
        $conductor = Conductor::find($conductor_id);
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        //if ($conductor->user!=null) {
        if (4>2) {
  
          //if ($conductor->user->idregistrationdevice!=null and $conductor->user->idregistrationdevice!='') { //Validar
            if(4>2){
  
                $id = 'fEc6AKkMy0Dns5ZIlIpSMf:APA91bE0KpuAY6xmEX2ixLjyDhvrtvZFQ97rqWtCUY0IJLW6qEwwNX8r37ewQrfTTMDPGGtf-lTwe2ej6yCa2dUzaR47VtfRFZCDWJEz94BrmV8_IqRGhd2WCWlCn4rE5zYeg-ufzRCm';
    
                $fields = array (
                'registration_ids' => array (
                    $id
                ),
                'notification' => array (
                    "body" => $mensaje,
                    "title" => 'Viaje Cancelado...',
                    "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png'
                )
                );
                $fields = json_encode ( $fields );
    
                $headers = array (
                'Authorization: key=' . "AAAABsrVRW8:APA91bHeyqFdFTYzPuSQe6SXB-FO1bLqJ_cQcNTWim-oBShNazh00NagwyKA0ouZC94lre12goZcjSzyqwpDJsGPiVa4voh7xm3-DOkl11u2YF9f3PnLXFRdmb59vXYj6cHeafkuqeA-",
                'Content-Type: application/json'
                );
    
                $ch = curl_init ();
                curl_setopt ( $ch, CURLOPT_URL, $url );
                curl_setopt ( $ch, CURLOPT_POST, true );
                curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
                curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
    
                $result = curl_exec ( $ch );
    
                curl_close($ch);
    
            }
  
        }

    }

    public static function NotificacionConductorNuevo($mensaje, $conductor_id){

        $countFalse = 0;
        $countTrue = 0;
    
        $url = 'https://fcm.googleapis.com/fcm/send';
    
        $apikey = ConfigController::FIREBASE_KEY_DRIVER;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
    
        //$servicio = Viaje::find($id_servicio);
    
        if (4>2) { //Verificar si tiene el conductor tiene usuario de aplicación para notificar al id
    
            $id_registration = 'fEc6AKkMy0Dns5ZIlIpSMf:APA91bE0KpuAY6xmEX2ixLjyDhvrtvZFQ97rqWtCUY0IJLW6qEwwNX8r37ewQrfTTMDPGGtf-lTwe2ej6yCa2dUzaR47VtfRFZCDWJEz94BrmV8_IqRGhd2WCWlCn4rE5zYeg-ufzRCm'; //revisar
    
            if ($id_registration!=null and $id_registration!='') {
    
                $message = $mensaje;
        
                $id = $id_registration;
        
                $fields = array (
                    'registration_ids' => array (
                    $id
                    ),
                    'notification' => array (
                    "body" => $message,
                    "title" => '¡Nuevo Viaje!',
                    "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png'
                    )
                );
                $fields = json_encode ( $fields );
        
                $headers = array (
                    'Authorization: key=' . $apikey,
                    'Content-Type: application/json'
                );
        
                $ch = curl_init ();
                curl_setopt ( $ch, CURLOPT_URL, $url );
                curl_setopt ( $ch, CURLOPT_POST, true );
                curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
                curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
        
                $result = curl_exec ( $ch );
                curl_close ( $ch );
        
        
                if ($result===FALSE) {
        
                    $countFalse++;
        
                }else{
        
                    $countTrue++;
                    /*Guardar Notificación*/
                    //$notificationSave = new NotificationDriver;
                    //$notificationSave->fecha = date('Y-m-d H:i');
                    //$notificationSave->titulo = 'Nuevo Viaje';
                    //$notificationSave->text = $message;
                    //$notificationSave->id_usuario = $servicio->conductor->user->id; //revisar
                    //$notificationSave->tipo = 6;
                    //$notificationSave->id_servicio = $id_servicio;
                    //$notificationSave->save();
                    /*Guardar Notificación*/
        
                }
    
            }
    
            //$counters['countTrue'] = $countTrue;
            //$counters['countFalse'] = $countFalse;
    
            return $result;
    
        }

    }

    public static function notificarViajeCancelado($conductorId, $razonsocial, $fecha, $hora){//ACTUALIZADO

        $countFalse = 0;
        $countTrue = 0;
    
        $url = 'https://fcm.googleapis.com/fcm/send';
    
        $apikey = ConfigController::FIREBASE_KEY_DRIVER;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
    
        //$servicio = Viaje::find($id_servicio);
    
        if (4>2) { //Verificar si tiene el conductor tiene usuario de aplicación para notificar al id
    
            $id_registration = 'fEc6AKkMy0Dns5ZIlIpSMf:APA91bE0KpuAY6xmEX2ixLjyDhvrtvZFQ97rqWtCUY0IJLW6qEwwNX8r37ewQrfTTMDPGGtf-lTwe2ej6yCa2dUzaR47VtfRFZCDWJEz94BrmV8_IqRGhd2WCWlCn4rE5zYeg-ufzRCm'; //revisar
    
            if ($id_registration!=null and $id_registration!='') {
    
                $message = 'Se ha cancelado el viaje de '.$razonsocial.' del '.$fecha.' a las: '.$hora.'.';
        
                $id = $id_registration;
        
                $fields = array (
                    'registration_ids' => array (
                    $id
                    ),
                    'notification' => array (
                    "body" => $message,
                    "title" => 'Viaje Cancelado...',
                    "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png'
                    )
                );
                $fields = json_encode ( $fields );
        
                $headers = array (
                    'Authorization: key=' . $apikey,
                    'Content-Type: application/json'
                );
        
                $ch = curl_init ();
                curl_setopt ( $ch, CURLOPT_URL, $url );
                curl_setopt ( $ch, CURLOPT_POST, true );
                curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
                curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
        
                $result = curl_exec ( $ch );
                curl_close ( $ch );
        
        
                if ($result===FALSE) {
        
                    $countFalse++;
        
                }else{
        
                    $countTrue++;
                    /*Guardar Notificación*/
                    //$notificationSave = new NotificationDriver;
                    //$notificationSave->fecha = date('Y-m-d H:i');
                    //$notificationSave->titulo = 'Nuevo Viaje';
                    //$notificationSave->text = $message;
                    //$notificationSave->id_usuario = $servicio->conductor->user->id; //revisar
                    //$notificationSave->tipo = 6;
                    //$notificationSave->id_servicio = $id_servicio;
                    //$notificationSave->save();
                    /*Guardar Notificación*/
        
                }
    
            }
    
            //$counters['countTrue'] = $countTrue;
            //$counters['countFalse'] = $countFalse;
    
            return $result;
    
        }
    
    }

    //AQUÍ INICIAN LAS NOTIFICACIONES A LOS CLIENTES -WHATSAPP-
    public static function notificarViajeEjecutivo($number, $nombre, $fecha, $hora, $conductor, $placa, $trayecto, $id) {

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
            \"name\": \"notificarviajeejecutivo_up\",
            \"language\": {
                \"code\": \"es\",
            },
            \"components\": [{
                \"type\": \"body\",
                \"parameters\": [{
                \"type\": \"text\",
                \"text\": \"".$nombre."\",
                },
                {
                \"type\": \"text\",
                \"text\": \"".$fecha."\",
                },
                {
                \"type\": \"text\",
                \"text\": \"".$hora."\",
                },
                {
                \"type\": \"text\",
                \"text\": \"".$conductor."\",
                },
                {
                \"type\": \"text\",
                \"text\": \"".$placa."\",
                },
                {
                \"type\": \"text\",
                \"text\": \"".$trayecto."\",
                }]
            },
            {
                \"type\": \"button\",
                \"sub_type\": \"url\",
                \"index\": \"0\",
                \"parameters\": [{
                \"type\": \"payload\",
                \"payload\": \"".$id."\"
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

        return $response;

    }

    public static function ActualizacionViaje($number, $nombre, $fecha, $hora, $conductor, $placa, $trayecto, $id) {

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
            \"name\": \"actualizacionviaje_up\",
            \"language\": {
                \"code\": \"es\",
            },
            \"components\": [{
                \"type\": \"body\",
                \"parameters\": [{
                    \"type\": \"text\",
                    \"text\": \"".$nombre."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$fecha."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$hora."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$conductor."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$placa."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$trayecto."\",
                }]
            },
            {
                \"type\": \"button\",
                \"sub_type\": \"url\",
                \"index\": \"0\",
                \"parameters\": [{
                    \"type\": \"payload\",
                    \"payload\": \"".$id."\"
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
    
    //AQUÍ INCIAN LAS NOTIFICACIONES A LOS CLIENTES - UP
    public static function NotificacionCambiosUp($message, $messageEn, $user){ //NOTIFICACIÓN UPDATE SERVICIO DE UP

        $url = 'https://fcm.googleapis.com/fcm/send';
        $key = ConfigController::FIREBASE_KEY_CLIENT;
        $vibration_pattern = ConfigController::VIBRATION_PATTERN;
  
        $usuario = DB::table('users')
        ->where('id',$user)
        ->first();
  
        if ($usuario->idregistrationdevice!=null and $usuario->idregistrationdevice!='') {
  
            $id = $usuario->idregistrationdevice;
    
            if($usuario->idioma==='en'){
    
                $title = 'Service Update...';
                $message = $messageEn;
    
            }else{
    
                $title = 'Actualización de Servicio...';
                $message = $message;
    
            }
    
            $fields = array (
                'registration_ids' => array (
                $id
                ),
                'notification' => array (
                "body" => $message,
                "title" => $title,
                "icon" => 'https://app.aotour.com.co/autonet/image_notifications.png',
                "vibration_pattern" => $vibration_pattern
                )
            );
            $fields = json_encode ( $fields );
    
            $headers = array (
                'Authorization: key=' . $key,
                'Content-Type: application/json'
            );
    
            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_POST, true );
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
    
            $result = curl_exec ( $ch );
            //curl_close ( $ch );
    
            /*Guardar Notificación*/
            //$notificationSave = new Notifications;
            //$notificationSave->fecha = date('Y-m-d H:i');
            //$notificationSave->titulo = 'Actualización de Servicio...';
            //$notificationSave->text = $message;
            //$notificationSave->titulo_en = 'Service Update...';
            //$notificationSave->text_en = $messageEn;
            //$notificationSave->id_usuario = $cliente_id;
            //$notificationSave->tipo = 6;
            //$notificationSave->id_servicio = $servicio;
            //$notificationSave->save();
            /*Guardar Notificación*/
    
            return $result;
  
        }
  
    }

}
