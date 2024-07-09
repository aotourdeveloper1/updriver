<?php

namespace App\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\NotificacionesUpnet;
use App\Models\Proyecto;
use App\Models\Siigo;
use App\Models\Cotizaciones;
use App\Models\Notas;
use App\Models\User;
use App\Models\Empleado;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /*Listado de notificaciones programadas de forma periódica*/

        //Notificacion a los empleados sobre el cumpleaños de algun trabajador el día siguiente; ejemplo: Mañana es el cumpleaños de Fulanito (Diario)
        //Notificación a los empleados sobre el cumpleaños el día actual; ejemplo: Hoy es el cumpleaños de Fulanito (Diario)
        //Notificación a los proveedores sobre documentos vencidos y por vencerse (Diario)
        //Notificación a los empleados sobre tareas con poco plazo con respecto al día actual (Diario)
        //Notificación a conductores de servicios próximos a iniciar (Reconfirmaciones) (Diario/Cada 5 minutos)
        //Notificación a clientes sobre servicios próximos a iniciar (Reconfirmaciones) (Diario/Cada 5 minutos)
        //Notificación a Operaciones sobre Usuarios de rutas dobles (Diario)
        //Notificación a contabilidad sobre facturas vencidas que no tienen ingreso (Diario)
        //Notificación a Mantenimeinto sobre documentación pendiente que no ha sido aprobada a la fecha (Diario)

        /*$schedule->call(function () {
            
            $number = 3013869946;
            $nombre = 'DAVID COBA';
            $dia = 'MAÑANA';
            $hora = '21:00';
            $placa = 'UUW126';
            $conductor = 'SAMUEL GONZÁLEZ opc 2';
            $numero = 3013869946;
            $qr = '3013869946';

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
                \"name\": \"ruta_qr\",
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
                    \"text\": \"".$dia."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$hora."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$placa."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$conductor."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$numero."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"3013869946\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"3013869946\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"3013869946\",
                }]
                },
                {
                \"type\": \"button\",
                \"sub_type\": \"url\",
                \"index\": \"0\",
                \"parameters\": [{
                    \"type\": \"payload\",
                    \"payload\": \"".$qr."\"
                }]
                }]
            }
            }");

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer EAAHPlqcJlZCMBAMDLjgTat7TlxvpmDq1fgzt2gZBPUnEsTyEuxuJw9uvGJM1WrWtpN7fmpmn3G2KXFZBRIGLKEDhZBPZAeyUSy2OYiIcNEf2mQuFcW67sgGoU95VkYayreD5iBx2GbnZBgaGvS8shX6f2JKeBp7pm9TNLm2EZBEbcx0Sdg47miONZCpUNZCfqEWlZAFxkltEOBPAZDZD"
            ));

            $response = curl_exec($ch);
            curl_close($ch);

        })->everyTwoMinutes();*/

        /*$schedule->call(function () {

            $number = 3013869946;
            $nombre = 'DAVID';
            $dia = 'HOY';
            $hora = '19:00';
            $placa = 'UUW126';
            $conductor = 'SAMUEL GONZÁLEZ';
            $numero = 3013869946;
            $qr = 'dfdad4545dsfsdfs';

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
                \"name\": \"ruta_qr\",
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
                    \"text\": \"".$dia."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$hora."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$placa."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$conductor."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"".$numero."\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"3147484288\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"3012030290\",
                },
                {
                    \"type\": \"text\",
                    \"text\": \"3014791279\",
                }]
                },
                {
                \"type\": \"button\",
                \"sub_type\": \"url\",
                \"index\": \"0\",
                \"parameters\": [{
                    \"type\": \"payload\",
                    \"payload\": \"".$qr."\"
                }]
                }]
            }
            }");

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer EAAHPlqcJlZCMBAMDLjgTat7TlxvpmDq1fgzt2gZBPUnEsTyEuxuJw9uvGJM1WrWtpN7fmpmn3G2KXFZBRIGLKEDhZBPZAeyUSy2OYiIcNEf2mQuFcW67sgGoU95VkYayreD5iBx2GbnZBgaGvS8shX6f2JKeBp7pm9TNLm2EZBEbcx0Sdg47miONZCpUNZCfqEWlZAFxkltEOBPAZDZD"
            ));

            $response = curl_exec($ch);
            curl_close($ch);
            
        })->everyMinute();*/

        //Notificar sobre el cumpleaños de alguien
        /*$schedule->call(function () {

            $mes = date('m');
            $dia = date('d');
            $querys = $mes.$dia;

            $consulta = DB::table('empleados')->where('cumpleanos',$querys)->where('estado',1)->get();

            //SI EN EL DIA Y MES ACTUAL HAY UNO O MÁS CUMPLIMENTADOS
            if($consulta!=null){
                $valores = '';
                foreach ($consulta as $employ) {

                    $fecha = explode('-', $employ->fecha_nacimiento);
                    $mes = $fecha[1];
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

                    if($valores!=null){
                        $valores .= ', <br> '.$employ->nombres.' '.$employ->apellidos.'';
                    }else{
                        $valores .=$employ->nombres.' '.$employ->apellidos.'';
                    }

                }
                
                $frase = 'AOTOUR Felicita a :';

                $datos = $frase.' <p style="color: gray"> '.$valores.'  &#x270b;</p> Por la celebración de su cumpleaños hoy '.$day.' '.$fecha[2].' de '.$mes.' del 2024. &#x1f973; &#x1f389;';
            }elseif($welcome->mensaje!=null){
                $datos = $welcome->mensaje;
            }else{
                $datos = null;
            }

            
        })->everyMinute();*/

        //Notificar sobre el cumpleaños de alguien (un día antes)
        /*$schedule->call(function () {
            
            $fecha = date('Y-m-d');

            $diasiguiente = strtotime ('+1 day', strtotime($fecha));
            $diasiguiente = date('Y-m-d' , $diasiguiente);

            $cumpleaneros = DB::table('empleados')
            ->where('fecha_nacimiento',$diasiguiente)
            ->first();

            foreach ($cumpleaneros as $cumple) {
                //$numero = DB::table()
            }
            
        })->daily();*/

        //Función para el registro de la administración de vehículos
        /*$schedule->call(function () {
            
            $fecha = date('Y-m-d');

            $diasiguiente = strtotime ('+1 day', strtotime($fecha));
            $diasiguiente = date('Y-m-d' , $diasiguiente);

            $cumpleaneros = DB::table('empleados')
            ->where('fecha_nacimiento',$diasiguiente)
            ->first();

            foreach ($cumpleaneros as $cumple) {
                //$numero = DB::table()
            }
            
        })->daily();*/

        //Función para crear el token de siigo cada día
        /*$schedule->call(function () {
            
            $fecha = date('Y-m-d');

            $diasiguiente = strtotime ('+1 day', strtotime($fecha));
            $diasiguiente = date('Y-m-d' , $diasiguiente);

            $cumpleaneros = DB::table('empleados')
            ->where('fecha_nacimiento',$diasiguiente)
            ->first();

            foreach ($cumpleaneros as $cumple) {
                //$numero = DB::table()
            }
            
        })->daily();*/

        //Función para cambiarle el año al fuec el 31-dec at 23:59
        /*$schedule->call(function () {
            
            //Cambio de año fuec
            
        })->daily();*/

        //Función para Notificar a contabilidad de facturas vencidas sin pago - también al cliente
        /*$schedule->call(function () {
            
            //Notificación de facturas vencidas sin pago
            
        })->daily();*/


        /* INICIO NOTIFICACIONES AUTOMÁTICAS DE TAREAS */

        //Función para el envío de notificaciones a los USUARIOS - TAREAS PENDIENTES al inicio de la semana
        $schedule->call(function () {

            $users = "select users.*, tipo_usuario.codigo from users left join tipo_usuario on tipo_usuario.id = users.id_perfil where users.fk_tipo_usuario = 1 and users.master != 1 and users.baneado is null";
            $users = DB::select($users);

            foreach ($users as $user) {

                $cantidadTareas = "select id from proyectos where fk_responsable = ".$user->id." and fk_estado in(3,4,5)";
                $cantidadTareas = DB::select($cantidadTareas);
                $cont = count($cantidadTareas);

                if($cont>0) {

                    $asunto = 'Tus tareas pendientes...';
                    $cuerpo = 'Tienes '.$cont.' tareas en proceso';
                    $usuario = $user->id;

                    Proyecto::saveNotification($asunto, $cuerpo, $usuario, 11);

                    $channel = 'notificaciones_'.$usuario;
                    $name = 'not'.$usuario;

                    $data = json_encode([
                        'asunto' => $asunto,
                        'cuerpo' => $cuerpo,
                    ]);

                    Proyecto::notificationPusher($channel, $name, $data);

                }

            }
            
        })->weekly()->mondays()->at('07:30');//})->everyMinute();

        //Envío de notificaciones a los ADMINISTRADORES - TAREAS POR APROBAR
        $schedule->call(function () {

            $users = "select * FROM users WHERE master = 1 and users.baneado is null";
            $users = DB::select($users);

            foreach ($users as $user) {

                $cantidadTareas = "select id from proyectos where fk_estado = 9";
                $cantidadTareas = DB::select($cantidadTareas);
                $cont = count($cantidadTareas);

                if($cont>0) {

                    $asunto = 'Tus tareas por aprobar...';
                    $cuerpo = 'Tienes '.$cont.' tareas que no has aprobado.';
                    $usuario = $user->id;

                    Proyecto::saveNotification($asunto, $cuerpo, $usuario, 11);

                    $channel = 'notificaciones_'.$usuario;
                    $name = 'not'.$usuario;

                    $data = json_encode([
                        'asunto' => $asunto,
                        'cuerpo' => $cuerpo,
                    ]);

                    Proyecto::notificationPusher($channel, $name, $data);

                }

            }
        })->weekly()->mondays()->at('07:30');//})->everyMinute();

        //Viernes
        //Envío de notificaciones a los USUARIOS - TAREAS PENDIENTES al final de la semana
        $schedule->call(function () {

            $users = "select users.*, tipo_usuario.codigo from users left join tipo_usuario on tipo_usuario.id = users.id_perfil where users.fk_tipo_usuario = 1 and users.master != 1 and users.baneado is null";
            $users = DB::select($users);

            foreach ($users as $user) {

                $cantidadTareas = "select id from proyectos where fk_responsable = ".$user->id." and fk_estado in(3,4,5)";
                $cantidadTareas = DB::select($cantidadTareas);
                $cont = count($cantidadTareas);

                if($cont>0) {

                    $asunto = 'Tus tareas pendientes...';
                    $cuerpo = 'Finalizas esta semana con '.$cont.' tareas pendientes.';
                    $usuario = $user->id;

                    Proyecto::saveNotification($asunto, $cuerpo, $usuario, 11);

                    $channel = 'notificaciones_'.$usuario;
                    $name = 'not'.$usuario;

                    $data = json_encode([
                        'asunto' => $asunto,
                        'cuerpo' => $cuerpo,
                    ]);

                    Proyecto::notificationPusher($channel, $name, $data);

                }

            }
            
        })->weekly()->fridays()->at('17:15');//})->everyMinute();

        /* FIN NOTIFICACIONES AUTOMÁTICAS DE TAREAS */

        //Generación de Token de Siigo - Cada día a las 23:30
        $schedule->call(function () {

            try {

                $urlSiigo = Siigo::URL_SIIGO;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlSiigo."auth");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                  \"username\": \"siigoapi@pruebas.com\",
                  \"access_key\": \"".Siigo::KEY_SIIGO."\"
                }");
                /*
                curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                  \"username\": \"contabilidad@aotour.com.co\",
                  \"access_key\": \"".SiigoController::KEY_SIIGO."\"
                }");
                */
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                  "Content-Type: application/json",
                  "Partner-Id: AUTONET"
                ));
        
                $response = curl_exec($ch);
                curl_close($ch);
        
                $fecha = strtotime ('+1 day', strtotime(date('Y-m-d')));
                $diasiguiente = date('Y-m-d' , $fecha);
        
                $token = Siigo::find(1);
                $token->token = json_decode($response)->access_token;
                $token->fecha_vence = $diasiguiente;
                $token->hora_vence = date('H:i:s');
                $token->save();
        
                $fecha_vence = date("d/m/Y", strtotime($token->fecha_vence));
        
                return Response::json([
                  'respuesta' => true,
                  'response' => $response,
                  'fecha' => $fecha_vence,
                  'hora' => $token->hora_vence
                ]);
        
              } catch (Exception $e) {
        
                return Response::json([
                    'respuesta'=>'error',
                    'response' => $response,
                    'code' => json_decode($response)->Errors[0]->Code,
                    'message' => json_decode($response)->Errors[0]->Message,
                ]);
        
              }
            
        })->dailyAt('23:30');

        /* COMERCIAL START*/

        //Cliente


        //Colocar cotizaciones como vencidas
        /*$schedule->call(function () {

            try {

                $fecha = date('Y-m-d');
                $diaanterior = strtotime('-1 day', strtotime($fecha));
                $diaanterior = date('Y-m-d' , $diaanterior);

                $cotizaciones = DB::table('cotizaciones')
                ->where('fecha_servicio',$fecha)
                ->update([
                    'estado' => 23
                ]);

                //Enviar correo a comercial
        
            } catch (Exception $e) {
        
                
        
            }
            
        })->dailyAt('00:30');*/

        //Aviso de las cotizaciones en negociación cada 2 días y un día antes
        /*$schedule->call(function () {

            try {

                $fecha = date('Y-m-d');
                $fechaExploded = explode('-', $fecha);
                $dia = $fechaExploded[2];

                if ((intval($dia) % 2) == 0) {
                    
                }else{

                    $cotizaciones = DB::table('cotizaciones')
                    ->where('estado',24)
                    ->get();

                    $data = [
                        'code' => 12345
                    ];
            
                    $email = 'sistemas@aotour.com.co';
                    $emailcc = ['aotourdeveloper@gmail.com'];
            
                    Mail::send('cotizaciones_negociando', $data, function($message) use ($email, $emailcc){
                        $message->from('no-reply@aotour.com.co', 'Alertas Cotizaciones');
                        $message->to($email)->subject('En Negociación');
                        $message->cc($emailcc);
                    });
                    //enviar correo de cotizaciones en negociación
                }

                //Enviar correo a comercial
        
            } catch (Exception $e) {
        
                
        
            }
            
        })->dailyAt('07:45');*/

        //Aviso de las cotizaciones en negociación a los clientes cada 5 días y un día antes
        /*$schedule->call(function () {

            try {

                $fecha = date('Y-m-d');
                $fechaExploded = explode('-', $fecha);
                $dia = $fechaExploded[2];

                if ((intval($dia) % 2) == 0) {
                    
                }else{

                    $cotizaciones = DB::table('cotizaciones')
                    ->where('estado',24)
                    ->get();

                    $data = [
                        'code' => 12345
                    ];
            
                    $email = 'sistemas@aotour.com.co';
                    $emailcc = ['aotourdeveloper@gmail.com'];
            
                    Mail::send('cotizaciones_negociando', $data, function($message) use ($email, $emailcc){
                        $message->from('no-reply@aotour.com.co', 'Alertas Cotizaciones');
                        $message->to($email)->subject('En Negociación');
                        $message->cc($emailcc);
                    });
                    //enviar correo de cotizaciones en negociación
                }

                //Enviar correo a comercial
        
            } catch (Exception $e) {
        
                
        
            }
            
        })->dailyAt('07:45');*/

        /* COMERCIAL END */

        /* NOTAS START */
        //Notificar notas con alerta
        $schedule->call(function () {

            try {

                $fecha = date('Y-m-d');
                $hora = date('H:i');

                $notas = "select id, codigo, descripcion, estado, notificar, fecha_notificacion, hora_notificacion, fk_user, date(created_at) as fecha from notas where fecha_notificacion = '".$fecha."' and hora_notificacion = '".$hora."' and notificar = 1";
                $notas = DB::select($notas);

                foreach ($notas as $nota) {
                    
                    $user = User::find($nota->fk_user);

                    $empleado = Empleado::find($user->id_empleado);

                    $numero = $empleado->telefono;

                    $exploded = explode("-", $nota->fecha);

                    $dia = $exploded[2];
                    
                    $mess = $exploded[1];

                    if(intval($mess) == 1){
                        $mes = 'Enero';
                    }else if(intval($mess) == 2){
                        $mes = 'Febrero';
                    }else if(intval($mess) == 3){
                        $mes = 'Marzo';
                    }else if(intval($mess) == 4){
                        $mes = 'Abril';
                    }else if(intval($mess) == 5){
                        $mes = 'Mayo';
                    }else if(intval($mess) == 6){
                        $mes = 'Junio';
                    }else if(intval($mess) == 7){
                        $mes = 'Julio';
                    }else if(intval($mess) == 8){
                        $mes = 'Agosto';
                    }else if(intval($mess) == 9){
                        $mes = 'Septiembre';
                    }else if(intval($mess) == 10){
                        $mes = 'Octubre';
                    }else if(intval($mess) == 11){
                        $mes = 'Noviembre';
                    }else if(intval($mess) == 12){
                        $mes = 'Diciembre';
                    }

                    $fecha = $dia.' de '. $mes;
                    $texto = $nota->descripcion;

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v15.0/109529185312847/messages");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);

                    curl_setopt($ch, CURLOPT_POST, TRUE);

                    curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                    \"messaging_product\": \"whatsapp\",
                    \"to\": \"".$numero."\",
                    \"type\": \"template\",
                    \"template\": {
                        \"name\": \"note_notification\",
                        \"language\": {
                        \"code\": \"es\",
                        },
                            \"components\": [{
                            \"type\": \"body\",
                            \"parameters\": [{
                                \"type\": \"text\",
                                \"text\": \"".$fecha."\",
                            },
                            {
                                \"type\": \"text\",
                                \"text\": \"".$texto."\",
                            }]
                        }]
                    }
                    }");

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Content-Type: application/json",
                        "Authorization: Bearer EAAHPlqcJlZCMBAMDLjgTat7TlxvpmDq1fgzt2gZBPUnEsTyEuxuJw9uvGJM1WrWtpN7fmpmn3G2KXFZBRIGLKEDhZBPZAeyUSy2OYiIcNEf2mQuFcW67sgGoU95VkYayreD5iBx2GbnZBgaGvS8shX6f2JKeBp7pm9TNLm2EZBEbcx0Sdg47miONZCpUNZCfqEWlZAFxkltEOBPAZDZD"
                    ));

                    $response = curl_exec($ch);
                    curl_close($ch);

                }
        
            } catch (Exception $e) {
        
                
        
            }
            
        })->everyMinute();
        /* NOTAS END */

        /* CREACIÓN DE TAREAS AUTOMÁTICAS - TAREA QUE SE EJECUTA LOS 1 DE CADA MES A LAS 00:01 */
        /*$schedule->call(function () {
            
            $projects = DB::table('cargo_proyectos')
            ->whereNotNull('estado')
            ->whereNotNull('activo')
            ->get();

            $mes = date('m');

            foreach ($projects as $project) {
                
                if($mes==1) { //Todas se crean

                    $project = new Proyecto;
                    $project->parameter1 = $value1;
                    $project->parameter2 = $value2;
                    $project->parameter3 = $value3;
                    $project->parameter4 = $value4;
                    $project->parameter5 = $value5;
                    $project->save();

                }else if($mes==2) {
                    
                    if($project->periodo==1 or $project->periodo==2){
                        
                        $project = new Proyecto;
                        $project->parameter1 = $value1;
                        $project->parameter2 = $value2;
                        $project->parameter3 = $value3;
                        $project->parameter4 = $value4;
                        $project->parameter5 = $value5;
                        $project->save();

                    }

                }else if($mes==3) {

                    if($project->periodo==1 or $project->periodo==3){
                        
                        $project = new Proyecto;
                        $project->parameter1 = $value1;
                        $project->parameter2 = $value2;
                        $project->parameter3 = $value3;
                        $project->parameter4 = $value4;
                        $project->parameter5 = $value5;
                        $project->save();

                    }

                }else if($mes==4) {

                    if($project->periodo==1 or $project->periodo==2 or $project->periodo==4){
                        
                        $project = new Proyecto;
                        $project->parameter1 = $value1;
                        $project->parameter2 = $value2;
                        $project->parameter3 = $value3;
                        $project->parameter4 = $value4;
                        $project->parameter5 = $value5;
                        $project->save();

                    }

                }else if($mes==5) {

                    if($project->periodo==1 or $project->periodo==5){
                        
                        $project = new Proyecto;
                        $project->parameter1 = $value1;
                        $project->parameter2 = $value2;
                        $project->parameter3 = $value3;
                        $project->parameter4 = $value4;
                        $project->parameter5 = $value5;
                        $project->save();

                    }

                }else if($mes==6) {

                    if($project->periodo==1 or $project->periodo==2 or $project->periodo==3 or $project->periodo==6){
                        
                        $project = new Proyecto;
                        $project->parameter1 = $value1;
                        $project->parameter2 = $value2;
                        $project->parameter3 = $value3;
                        $project->parameter4 = $value4;
                        $project->parameter5 = $value5;
                        $project->save();

                    }

                }else if($mes==7) {

                    if($project->periodo==1 or $project->periodo==7){
                        
                        $project = new Proyecto;
                        $project->parameter1 = $value1;
                        $project->parameter2 = $value2;
                        $project->parameter3 = $value3;
                        $project->parameter4 = $value4;
                        $project->parameter5 = $value5;
                        $project->save();

                    }

                }else if($mes==8) {

                    if($project->periodo==1 or $project->periodo==2 or $project->periodo==4 or $project->periodo==8){
                        
                        $project = new Proyecto;
                        $project->parameter1 = $value1;
                        $project->parameter2 = $value2;
                        $project->parameter3 = $value3;
                        $project->parameter4 = $value4;
                        $project->parameter5 = $value5;
                        $project->save();

                    }

                }
                

                //Guardar y enviar notificación de la tarea creada - PENDING
                
            }

        })->monthlyOn(1, '00:01');//})->everyMinute();*/
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        require base_path('routes/console.php');
    }
}
