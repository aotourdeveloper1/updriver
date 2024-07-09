<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NotificacionesUpnet;

class Proyecto extends Model
{
    protected $table = 'proyectos';

    public static function notificationPusher($channel, $name, $data){

        $idpusher = "578229";
        $keypusher = "a8962410987941f477a1";
        $secretpusher = "6a73b30cfd22bc7ac574";
        
        $app_id = $idpusher;
        $key = $keypusher;
        $secret = $secretpusher;
        $body = [
            'data' => $data,
            'name' => $name,
            'channel' => $channel
        ];
        $auth_timestamp =  strtotime('now');
        $auth_version = '1.0';
        $body_md5 = md5(json_encode($body));
    
        $string_to_sign =
        "POST\n/apps/".$app_id.
        "/events\nauth_key=".$key.
        "&auth_timestamp=".$auth_timestamp.
        "&auth_version=".$auth_version.
        "&body_md5=".$body_md5;
    
        $auth_signature = hash_hmac('SHA256', $string_to_sign, $secret);
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, 'https://api-us2.pusher.com/apps/'.$app_id.'/events?auth_key='.$key.'&body_md5='.$body_md5.'&auth_version=1.0&auth_timestamp='.$auth_timestamp.'&auth_signature='.$auth_signature.'&');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $headers = [
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $response = curl_exec($ch);
    
    }

    public static function saveNotification($asunto, $cuerpo, $responsable, $estado){
        
        $notificacion = new NotificacionesUpnet;
        $notificacion->asunto = $asunto;
        $notificacion->cuerpo = $cuerpo;
        $notificacion->fk_users = $responsable;
        $notificacion->estado = $estado;
        $notificacion->save();

    }

    public static function notifyCopies($users, $projectName, $responsableName) {

    }

}
