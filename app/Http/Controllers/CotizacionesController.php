<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use DB;
use Auth;
use Mail;
use App\Models\Cotizacion;
use App\Models\Cotizaciondetalle;
use App\Models\GestionesCotizacion;
use App\Models\EvidenciasGestion;
use App\Models\Portafolio;
use App\Models\GestionesPortafolio;
use App\Models\EvidenciasGestionPortafolio;
use App\Models\PropuestaEconomica;

class CotizacionesController extends Controller
{
    public function create(Request $request) {
    
        $arraynombres = [];

        $cotizaciones = new Cotizacion;
        $cotizaciones->fecha_solicitud = $request->fecha_solicitud;
        $cotizaciones->fecha_creado = date('Y-m-d');
        $cotizaciones->fecha_servicio = $request->fecha_servicio;
        $cotizaciones->nombre_completo = $request->nombre_completo;
        $cotizaciones->nit = $request->nit;
        $cotizaciones->direccion = $request->direccion;
        $cotizaciones->celular = $request->celular;
        $cotizaciones->email = $request->email;
        $cotizaciones->asunto = $request->asunto;
        $cotizaciones->contacto = $request->contacto;
        $cotizaciones->vendedor = Auth::user()->id;
        $cotizaciones->canal = $request->canal;
        $cotizaciones->observacion = $request->observaciones;
        $cotizaciones->creado_por = Auth::user()->id;
        $cotizaciones->estado = 24;
        $cotizaciones->valor_total = $request->valor_total;
        $cotizaciones->enviado_a = $request->emails_to_send;

        $name_pdf = null;
        //Soporte de la cotización solicitada
        
        if ($request->hasFile('soporte_solicitud')){

            $file_pdf = $request->file('soporte_solicitud');
            $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());

            $ubicacion_pdf = 'images/archivos_cotizaciones/';
            $file_pdf->move($ubicacion_pdf, $name_pdf);
            $cotizaciones->soporte_solicitud = $name_pdf;

        }
            
        if ($cotizaciones->save()) {
            
            $insertedId = $cotizaciones->id;

            $trayectos = json_decode($request->trayectos);

            for ($i=0; $i < count($trayectos); $i++){
                
                $cotizacion_det = new Cotizaciondetalle;
                $cotizacion_det->fk_cotizaciones = $insertedId;
                $cotizacion_det->fecha_servicio = $trayectos[$i]->fecha_servicioV;
                $cotizacion_det->fk_traslados = $trayectos[$i]->traslados;
                $cotizacion_det->ciudad = $trayectos[$i]->ciudadV;
                $cotizacion_det->tipo_vehiculo = $trayectos[$i]->tipo_vehiculoV;
                $cotizacion_det->pax = $trayectos[$i]->paxV;
                $cotizacion_det->vehiculos = $trayectos[$i]->vehiculoV;
                $cotizacion_det->valorxvehiculo = $trayectos[$i]->valor_trayectoV;
                $cotizacion_det->valortotal = $trayectos[$i]->valortotalV;
                $cotizacion_det->save();

            }

            $emailsSended = '';

            $emailss = json_decode($request->emails_to_send);

            for ($i=0; $i < count($emailss) ; $i++) { 
                $emailsSended .= $emailss[$i].', ';
            }
            $dataText = 'Se realizó envío de cotización a : '.$emailsSended.' el '.date('Y-m-d').' a las '.date('H:i').', por '.Auth::user()->first_name.' '.Auth::user()->last_name.'';

            $gestion = new GestionesCotizacion;
            $gestion->texto = $dataText;
            $gestion->fk_cotizaciones = $insertedId;
            $gestion->fk_users = Auth::user()->id;
            $gestion->creado = date('Y-m-d H:i');
            $gestion->save();

            $fecha = explode('-', $request->fecha_solicitud);

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

            $fechaModificada = $fecha[2].' de '.ucwords(strtolower($mes)).' del '.$fecha[0];

            $empleado = DB::table('empleados')
            ->where('id',Auth::user()->id_empleado)
            ->first();

            $detalles = DB::table('cotizaciones_detalle')
            ->where('fk_cotizaciones',$insertedId)
            ->get();

            return Response::json([
                'response'=>true,
                'id' => $insertedId
            ]);

        }
    
    }

    public function sendquotemail(Request $request) {

        $insertedId = $request->id;
        $plantilla = $request->plantilla;

        if($plantilla=='crear') {
            $nombre = 'COTIZACIÓN';
            $name = 'Cotización';
            $plantilla = 'email_test';
        }else if($plantilla=='editar'){
            $nombre = 'RECOTIZACIÓN';
            $name = "Recotización";
            $plantilla = 'email_test_e';
        }

        $cotizaciones = Cotizacion::find($insertedId);

        $email = json_decode($cotizaciones->enviado_a);
        $cc = ['j.ojeda@aotour.com.co','b.carrillo@aotour.com.co','facturacion@aotour.com.co', 'gustelo@aotour.com.co'];
        //$cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co'];
        
        $clients = $cotizaciones->nombre_completo;

        $data = [
            'consecutivo' => $insertedId,
            'asunto' => $cotizaciones->asunto,
            'contacto' => $cotizaciones->contacto,
            'cliente' => $clients
        ];
        
        $pdfPath = 'https://cotizaciones-up-net.s3.amazonaws.com/'.$insertedId.'_cotizacion.pdf';

        Mail::send(''.$plantilla.'', $data, function($message) use ($pdfPath, $email, $insertedId, $cc, $cotizaciones, $nombre, $name){
            $message->from('no-reply@aotour.com.co', $nombre.' AOTOUR');
            $message->to($email)->subject($name.' AOTOUR N°. '.$insertedId);
            $message->Bcc($cc);
            $message->attach($pdfPath);
        });

        $cotizaciones->pdf_cotizacion = 'https://cotizaciones-up-net.s3.amazonaws.com/'.$insertedId.'_cotizacion.pdf';
        $cotizaciones->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function list(Request $request) {

        $query = "select c.id, count(g.fk_cotizaciones) as cantidad_gestiones, c.fecha_solicitud, c.fecha_creado, c.fecha_servicio, c.nombre_completo, c.nit, c.direccion, c.celular, c.email, c.asunto, c.contacto, est.nombre as canal, c.canal as id_canal, c.observacion, c.valor_total, u.first_name as nombre_creador, u.last_name as apellido_creador, u2.first_name as nombre_vendedor, u2.last_name as apellido_vendedor, e.nombre as estado, e.id as id_estado, c.pdf_cotizacion from cotizaciones c left join users u on u.id = c.creado_por left join users u2 on u2.id = c.vendedor left join estados e on e.id = c.estado left join estados est on est.id = c.canal left join gestiones g on g.fk_cotizaciones = c.id group by c.id order by c.id desc";
        $cotizaciones = DB::select($query);

        return Response::json([
            'response' => true,
            'cotizaciones' => $cotizaciones
        ]);

    }
    
    public function approve(Request $request) {

        $cotizacion = Cotizacion::find($request->id);
        $cotizacion->estado = 25;
        $cotizacion->save();

        $dataText = 'Se ACEPTÓ la cotización manualmente por '.Auth::user()->first_name.' '.Auth::user()->last_name.'';

        $gestion = new GestionesCotizacion;
        $gestion->texto = $dataText;
        $gestion->fk_cotizaciones = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        $data = [
            'contacto' => $cotizacion->contacto,
            'consecutivo' => $cotizacion->id
        ];
            
        //envío a clientes
        $email = json_decode($cotizacion->enviado_a);
        $cc = ['comercial@aotour.com.co','b.carrillo@aotour.com.co','facturacion@aotour.com.co', 'gustelo@aotour.com.co'];
        //$cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co'];

        Mail::send('email_acept', $data, function($message) use ($email, $cc){
            $message->from('no-reply@aotour.com.co', 'Alertas Cotizaciones');
            $message->to($email)->subject('¡Cotización Aceptada!');
            $message->bcc($cc);
        });

        return Response::json([
            'response' => true
        ]);

    }

    public function disapprove(Request $request) {

        $cotizacion = Cotizacion::find($request->id);
        $cotizacion->estado = 26;
        $cotizacion->save();

        $dataText = 'Se RECHAZÓ la cotización manualmente por '.Auth::user()->first_name.' '.Auth::user()->last_name.'';

        $gestion = new GestionesCotizacion;
        $gestion->texto = $dataText;
        $gestion->fk_cotizaciones = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        $data = [
            'contacto' => $cotizacion->contacto,
            'consecutivo' => $cotizacion->id
        ];

        //envío a clientes
        $email = json_decode($cotizacion->enviado_a);
        $cc = ['comercial@aotour.com.co','b.carrillo@aotour.com.co','facturacion@aotour.com.co', 'gustelo@aotour.com.co'];
        //$cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co'];

        Mail::send('email_rechaz', $data, function($message) use ($email, $cc){
            $message->from('no-reply@aotour.com.co', 'Alertas Cotizaciones');
            $message->to($email)->subject('¡Cotización Rechazada!');
            $message->bcc($cc);
        });

        return Response::json([
            'response' => true
        ]);

    }

    public function reactivate(Request $request) {

        $cotizacion = Cotizacion::find($request->id);
        $cotizacion->estado = 24;
        $cotizacion->save();

        $dataText = 'SE REACTIVÓ la cotización manualmente por '.Auth::user()->first_name.' '.Auth::user()->last_name.'';

        $gestion = new GestionesCotizacion;
        $gestion->texto = $dataText;
        $gestion->fk_cotizaciones = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function newmanagement(Request $request) {

        $gestion = new GestionesCotizacion;
        $gestion->texto = $request->texto;
        $gestion->fk_cotizaciones = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        return Response::json([
            'response' => true,
            'id' => $gestion->id
        ]);

    }

    public function evidence(Request $request) {

        if($request->hasFile('archivo')){

            $evidenciaGestion = new EvidenciasGestion;
            $evidenciaGestion->path = null;
            $evidenciaGestion->fk_gestiones = $request->id;
            $evidenciaGestion->save();

            $file = $request->file('archivo');
            $name_file = str_replace(' ', '', $file->getClientOriginalName());

            $ubicacion_pdf = 'images/archivos_cotizaciones/';
            $file->move($ubicacion_pdf, $evidenciaGestion->id.$name_file);
            
            $update = DB::table('evidencias_gestiones')
            ->where('id',$evidenciaGestion->id)
            ->update([
                'path' => $evidenciaGestion->id.$name_file
            ]);

            return Response::json([
                'response' => true
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function listbyquote(Request $request) {
        
        $query = "select cd.id, cd.fk_cotizaciones, cd.fecha_servicio as fecha_servicioV, cd.fk_traslados as traslados, cd.ciudad as ciudadV, cd.tipo_vehiculo as tipo_vehiculoV, cd.pax as paxV, cd.vehiculos as vehiculoV, cd.valorxvehiculo as valor_trayectoV, cd.valortotal as valortotalV, cd.created_at, e.nombre as nombre_tipo_vehiculo, c.nombre as nombre_ciudad, t.nombre from cotizaciones_detalle cd left join ciudades c on c.id = cd.ciudad left join traslados t on t.id = cd.fk_traslados left join estados e on e.id = cd.tipo_vehiculo where fk_cotizaciones = ".$request->id."";
        $tarifas = DB::select($query);

        return Response::json([
            'response' => true,
            'tarifas' => $tarifas
        ]);

    }

    public function editfees(Request $request) {

        $insertedId = $request->id;

        $cot = Cotizacion::find(intval($insertedId));

        $cotizacion = DB::table('cotizaciones')
        ->where('id',$insertedId)
        ->update([
            'valor_total' => $request->valor_total
        ]);

        $delete = DB::table('cotizaciones_detalle')
        ->where('fk_cotizaciones',$insertedId)
        ->delete();

        $arrayData2 = [];

        $trayectos = $request->trayectos;

        for ($i=0; $i < count($trayectos); $i++){
            
            $cotizacion_det = new Cotizaciondetalle;
            $cotizacion_det->fk_cotizaciones = $insertedId;
            $cotizacion_det->fecha_servicio = $trayectos[$i]['fecha_servicioV'];
            $cotizacion_det->fk_traslados = $trayectos[$i]['traslados'];
            $cotizacion_det->ciudad = $trayectos[$i]['ciudadV'];
            $cotizacion_det->tipo_vehiculo = $trayectos[$i]['tipo_vehiculoV'];
            $cotizacion_det->pax = $trayectos[$i]['paxV'];
            $cotizacion_det->vehiculos = $trayectos[$i]['vehiculoV'];
            $cotizacion_det->valorxvehiculo = $trayectos[$i]['valor_trayectoV'];
            $cotizacion_det->valortotal = $trayectos[$i]['valortotalV'];
            $cotizacion_det->save();

            $array = [
                'trayecto' => $trayectos[$i]['nombre'],
                'vehiculos' => $trayectos[$i]['vehiculoV'],
                'valortotal' => $trayectos[$i]['valortotalV']
            ];
            array_push($arrayData2, $array);

        }

        $dataText = 'Se realizó actualización de Tarifas/Traslados';

        $gestion = new GestionesCotizacion;
        $gestion->texto = $dataText;
        $gestion->fk_cotizaciones = $insertedId;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->tarifas = json_encode($arrayData2);
        $gestion->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function consultmanagement(Request $request) {

        $query = "SELECT g.id, g.texto, g.fk_users, g.fk_cotizaciones, u.first_name, u.last_name, egs.id as id_evidencia, egs.path, egs.fk_gestiones, g.creado, g.tarifas, JSON_ARRAYAGG(egs.path) AS evidencias FROM gestiones g LEFT JOIN evidencias_gestiones egs ON egs.fk_gestiones = g.id LEFT JOIN users u ON u.id = g.fk_users where g.fk_cotizaciones = ".$request->id." GROUP BY g.id";
        $gestiones = DB::select($query);

        return Response::json([
            'response' => true,
            'gestiones' => $gestiones
        ]);

    }

    public function consultevidence(Request $request) {

        $evidencias = DB::table('evidencias_gestiones')
        ->where('id',$request->id)
        ->get();
        
        return Response::json([
            'response' => true,
            'evidencias' => $evidencias
        ]);

    }

    //Pruebas de email
    public function email(Request $request) {
        //Whatsapp

        /*$numero = 573013869946;
        $fecha = '02 de Abril';
        $texto = 'Reunión con Google Maps Platform';

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

        return Response::json([
            'response' => true,
            'result' => $response
        ]);*/
        
        //email
        $data = [
            'code' => 12345
        ];

        $email = 'sistemas@aotour.com.co';
        $emailcc = ['sistemas1@aotour.com.co'];

        Mail::send('email_validation_code', $data, function($message) use ($email, $emailcc){
            $message->from('no-reply@aotour.com.co', 'Alertas Cotizaciones');
            $message->to($email)->subject('En Negociación');
            $message->cc($emailcc);
        });

        //crear la plantilla de correo de aviso para las cotizaciones que se encuentran en negociación

        return Response::json([
            'response' => true
        ]);

    }

    public function listchannels(Request $request){

        $canales = DB::table('estados')
        ->where('fk_estados_maestros',10)
        ->get();

        return Response::json([
            'response' => true,
            'canales' => $canales
        ]);

    }

    public function listvehiclestype() {

        $tipos = DB::table('estados')
        ->where('fk_estados_maestros',11)
        ->get();

        return Response::json([
            'response' => true,
            'tipos' => $tipos
        ]);

    }

    public function listfeebyclient(Request $request) {

        //$tarifas = "select t.id as id_tarifa, c.razonsocial, t.cliente_auto, t.cliente_van, t.proveedor_auto, t.proveedor_van, t.centrodecosto_id, t2.nombre, t2.fk_sede, t2.estado as estado_trayecto from tarifas t left join traslados t2 on t2.id = t.trayecto_id left join centrosdecosto c on c.id = t.centrodecosto_id";
        //$tarifas = "SELECT t.id, t.nombre, t.estado, JSON_OBJECTAGG(t2.id, JSON_OBJECT( 'cliente', c.razonsocial, 'cliente_auto', t2.cliente_auto, 'cliente_van', t2.cliente_van, 'proveedor_auto', t2.proveedor_auto, 'proveedor_van', t2.proveedor_van)) AS tarifas_clientes FROM traslados t LEFT JOIN tarifas t2 ON t2.trayecto_id = t.id LEFT JOIN centrosdecosto c ON c.id = t2.centrodecosto_id WHERE t.estado = 1 GROUP BY t.id";
        $tarifas = "SELECT t.id, t.nombre, t.fk_sede, t.estado, t2.cliente_auto, t2.cliente_van, JSON_ARRAYAGG(JSON_OBJECT('centro_costo', c.razonsocial)) AS tarifas FROM traslados t LEFT JOIN tarifas t2 ON t2.trayecto_id = t.id LEFT JOIN centrosdecosto c ON c.id = t2.centrodecosto_id WHERE c.id = ".$request->id." GROUP BY t.id, t.nombre, t.fk_sede, t.estado";
        $tarifas = DB::select($tarifas);

        return Response::json([
            'response' => true,
            'tarifas' => $tarifas
        ]);

    }

    public function listways(Request $request) {

        $traslados = "select t.id, t.nombre, t.fk_sede, t.estado, t.created_at, t.updated_at from traslados t where t.estado = 1";
        $traslados = DB::select($traslados);

        return Response::json([
            'response' => true,
            'traslados' => $traslados
        ]);

    }

    //PORTAFOLIO
    public function send(Request $request) {

        $portafolio = new Portafolio;
        $portafolio->fecha = date('Y-m-d');
        $portafolio->nombre_cliente = $request->nombre_cliente;
        $portafolio->correo = $request->correo;
        $portafolio->telefono = $request->telefono;
        $portafolio->direccion = $request->direccion;
        $portafolio->solicitante = $request->solicitante;
        $portafolio->ciudad = $request->ciudad;
        $portafolio->estado = $request->estado;
        $portafolio->ejecutivos = $request->ejecutivos;
        $portafolio->rutas = $request->rutas;
        $portafolio->creado_por = Auth::user()->id;
        $portafolio->estado = 43;
        $portafolio->save();

        $dataText = 'Se realizó envío de portafolio al correo: '.$request->correo.' el '.date('Y-m-d').' a las '.date('H:i').'.';

        $gestion = new GestionesPortafolio;
        $gestion->texto = $dataText;
        $gestion->fk_portafolio = $portafolio->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        //ENVÍO DE CORREO AL CLIENTE CON EL PORTAFOLIO

        $pdfPathFile = null;
        //$pdfPathFile = 'biblioteca_imagenes/reportes/portafolio/Propuesta_economica_00'.$insertedId.'.pdf';
        //if(Input::get('sw_tarifas')!=0){
          //  File::put($pdfPathFile, PDF::load($html, 'A4', 'portrait')->output());
            //Generación de Portafolio
        //}
        
        $sw = $portafolio->id;

        $data = [
            'consecutivo' => $sw,
            'code'=> 12345
        ];
    
        //$cco = ['sistemas@aotour.com.co','sistemas1@aotour.com.co'];
        $cco = ['comercial@aotour.com.co','b.carrillo@aotour.com.co', 'gustelo@aotour.com.co'];
        $email = $request->correo;
    
        $urlEjecutivos = 'images/PORTAFOLIO DE SERVICIOS.pdf';
        $urlRutas = 'images/PORTAFOLIO DE RUTAS.pdf';
    
        Mail::send('email_port', $data, function($message) use ($email, $sw, $cco, $urlEjecutivos, $urlRutas, $pdfPathFile, $request){
            $message->from('no-reply@aotour.com.co', '¡VIVE LA EXPERIENCIA AOTOUR!');
            $message->to($email)->subject('Te presentamos nuestro portafolio de servicios');
            $message->bcc($cco);
            if($request->ejecutivos==1 and $request->rutas!=1){
                $message->attach($urlEjecutivos);
            }else if($request->ejecutivos!=1 and $request->rutas==1){
                $message->attach($urlRutas);
            }else if($request->ejecutivos==1 and $request->rutas==1){
                $message->attach($urlEjecutivos);
                $message->attach($urlRutas);
            }else{
                //$pdfPaths = 'images/archivos_cotizaciones/'.$file->archivos;
                //$message->attach($pdfPaths);
            }

            //if($request->sw_tarifas==10){
              //  $message->attach($pdfPathFile);
            //}
        });

        return Response::json([
            'response' => true
        ]);

    }

    public function sendfees(Request $request) {

        $id = $request->id; //id del portafolio
        $tarifas = $request->tarifas;

        $portafolio = Portafolio::find($id);

        $fecha = date('Y-m-d');

        $dataText = 'Se realizó envío de propuesta ecomómica al correo: '.$portafolio->correo.' el '.$fecha.' a las '.date('H:i').'.';

        $objArray = [];

        for ($i=0; $i<count($tarifas) ; $i++) {

            $propuesta = new PropuestaEconomica;
            $propuesta->traslado = $tarifas[$i]['nombre'];
            $propuesta->valor_suv = $tarifas[$i]['valor_suv'];
            $propuesta->valor_van = $tarifas[$i]['valor_van'];
            $propuesta->fk_portafolio = $id;
            $propuesta->save();

        }
        
        $gestion = new GestionesPortafolio;
        $gestion->texto = $dataText;
        $gestion->fk_portafolio = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        $pdfPathFile = null;
        $pdfNameFile = null;

        $updatePdfPath = DB::table('portafolio')
        ->where('id',$request->id)
        ->update([
            'pdf_tarifas' => $pdfNameFile
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function updateurl(Request $request) {

        $portafolio = Portafolio::find($request->id);

        $data = [

        ];
            
        //envío a clientes
        //$email = 'sistemas@aotour.com.co'; //Correo al que se envía la solicitud de reunión
        $email = $portafolio->correo; //Correo al que se envía la solicitud de reunión
        $cc = ['comercial@aotour.com.co','b.carrillo@aotour.com.co', 'gustelo@aotour.com.co'];
        //$cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co']; //comentar esta línea en producción

        $urlPath = $request->url;
    
        Mail::send('email_economy', $data, function($message) use ($email, $cc, $urlPath){
            $message->from('no-reply@aotour.com.co', 'Propuesta Económica AOTOUR');
            $message->to($email)->subject('Nos complace enviarte nuestras tarifas');
            $message->bcc($cc);
            $message->attach($urlPath);
        });

        $portafolio->pdf_tarifas = $request->url;
        $portafolio->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function listp(Request $request) {

        $query = "select p.id, count(g.fk_portafolio) as cantidad_gestiones, p.ejecutivos, p.rutas, p.pdf_tarifas, p.fecha, p.nombre_cliente, p.correo, p.telefono, p.direccion, p.solicitante, ciu.nombre as nombre_ciudad, u.first_name as nombre_creador, u.last_name as apellido_creador, e.nombre as estado, e.id as id_estado from portafolio p left join users u on u.id = p.creado_por left join estados e on e.id = p.estado left join gestiones_portafolio g on g.fk_portafolio = p.id left join ciudades ciu on ciu.id = p.ciudad group by p.id";
        $negocaciones =  DB::select($query);

        return Response::json([
            'response' => true,
            'negociaciones' => $negocaciones
        ]);

    }
    
    public function newmanagementp(Request $request) {

        $gestion = new GestionesPortafolio;
        $gestion->texto = $request->texto;
        $gestion->fk_portafolio = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        return Response::json([
            'response' => true,
            'id' => $gestion->id
        ]);

    }

    public function consultmanagementp(Request $request) {

        $query = "SELECT gp.id, gp.texto, gp.fk_users, gp.fk_portafolio, u.first_name, u.last_name, egsp.id as id_evidencia, egsp.path, egsp.fk_gestiones_portafolio, gp.creado, JSON_ARRAYAGG(egsp.path) AS evidencias FROM gestiones_portafolio gp LEFT JOIN evidencias_gestiones_portafolio egsp ON egsp.fk_gestiones_portafolio = gp.id LEFT JOIN users u ON u.id = gp.fk_users where gp.fk_portafolio = ".$request->id." GROUP BY gp.id";
        $gestiones = DB::select($query);

        return Response::json([
            'response' => true,
            'gestiones' => $gestiones
        ]);

    }

    public function evidencep(Request $request) {

        if($request->hasFile('archivo')){

            $evidenciaGestion = new EvidenciasGestionPortafolio;
            $evidenciaGestion->path = null;
            $evidenciaGestion->fk_gestiones_portafolio = $request->id;
            $evidenciaGestion->save();

            $file = $request->file('archivo');
            $name_file = str_replace(' ', '', $file->getClientOriginalName());

            $ubicacion_pdf = 'images/archivos_portafolio/soporte_gestiones/';
            $file->move($ubicacion_pdf, $evidenciaGestion->id.$name_file);
            
            $update = DB::table('evidencias_gestiones_portafolio')
            ->where('id',$evidenciaGestion->id)
            ->update([
                'path' => $evidenciaGestion->id.$name_file
            ]);

            return Response::json([
                'response' => true
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

    public function approvep(Request $request) {

        $portafolio = Portafolio::find($request->id);
        $portafolio->estado = 44;
        $portafolio->save();

        $dataText = 'SE CONCRETÓ LA NEGOCIACIÓN. ESTA FUE MARCADA COMO EXITOSA.';

        $gestion = new GestionesPortafolio;
        $gestion->texto = $dataText;
        $gestion->fk_portafolio = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        $data = [

        ];

        //envío de correo de bienvenida a cliente
        //$email = 'sistemas@aotour.com.co'; //Correo al que se envía la solicitud de reunión
        $email = $portafolio->correo; //destinatario del correo
        //$cc = ['comercial@aotour.com.co']; //Correos de copia oculta
        $cc = ['comercial@aotour.com.co','b.carrillo@aotour.com.co','facturacion@aotour.com.co', 'gustelo@aotour.com.co'];
        //$cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co']; //comentar esta línea en producción

        Mail::send('email_welco', $data, function($message) use ($email, $cc){
            $message->from('no-reply@aotour.com.co', '¡Bienvenido!');
            $message->to($email)->subject('AOTOUR te da la bienvenida');
            $message->bcc($cc);
            $message->attach('images/Formato_Inscripcion_de_clientes.docx');
        });

        return Response::json([
            'response' => true
        ]);

    }

    public function disapprovep(Request $request) {

        $portafolio = Portafolio::find($request->id);
        $portafolio->estado = 45;
        $portafolio->save();

        $dataText = 'SE CANCELÓ LA NEGOCIACIÓN. ESTA FUE MARCADA COMO NO EXITOSA.';

        $gestion = new GestionesPortafolio;
        $gestion->texto = $dataText;
        $gestion->fk_portafolio = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function reactivatep(Request $request) {

        $portafolio = Portafolio::find($request->id);
        $portafolio->estado = 43;
        $portafolio->save();

        $dataText = 'SE REACTIVÓ LA NEGOCIACIÓN.';

        $gestion = new GestionesPortafolio;
        $gestion->texto = $dataText;
        $gestion->fk_portafolio = $request->id;
        $gestion->fk_users = Auth::user()->id;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function listfeetoquotes(Request $request) {

        $tarifas = "SELECT t.id, t.nombre, t.fk_sede, t.estado, t2.cliente_auto, t2.cliente_van, JSON_ARRAYAGG(JSON_OBJECT('centro_costo', c.razonsocial)) AS tarifas FROM traslados t LEFT JOIN tarifas t2 ON t2.trayecto_id = t.id LEFT JOIN centrosdecosto c ON c.id = t2.centrodecosto_id WHERE c.id = 97 GROUP BY t.id, t.nombre, t.fk_sede, t.estado";
        $tarifas = DB::select($tarifas);

        return Response::json([
            'response' => true,
            'tarifas' => $tarifas
        ]);

    }

    public function sendemail1(Request $request) {

        $data = [
            'titulo' => $request->titulo,
            'texto' => $request->texto
        ];

        $email = $request->email;
        $cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co'];

        Mail::send('pqr_emails.email_pqr', $data, function($message) use ($email, $cc, $request){
            $message->from('no-reply@aotour.com.co', ''.$request->nombre.'');
            $message->to($email)->subject(''.$request->asunto.'');
            $message->cc($cc);
        });

        return Response::json([
            'response' => true
        ]);
        
    }

    public function enviaremail(Request $request) {

        $data = [
            'code' => 12345,
            'link' => 'https://app.aotour.com.co/autonet/transportederuta/confirmarubicacion/18'
        ];

        $email = 'aotourdeveloper@gmail.com';
        $cc = 'sistemas@aotour.com.co';

        Mail::send('confirmar_ubicacion', $data, function($message) use ($email, $cc){
            $message->from('no-reply@aotour.com.co', 'Notificaciones Aotour');
            $message->to($email)->subject('Confirmación de Ruta');
            $message->cc($cc);
        });

        //file_put_contents('images/file.pdf', base64_decode($request->base64Binary));

        return Response::json([
            'response' => true
        ]);

        $number = 3013869946;

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
            \"name\": \"anunc\",
            \"language\": {
            \"code\": \"es\",
            },
            \"components\": [{
                \"type\": \"header\",
                \"parameters\": [{
                    \"type\": \"image\",
                    \"image\": {
                        \"link\": \"https://updeveloment.online/images/Ruta.png\"
                    }
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

        return Response::json([
            'response' => true,
            'response' => $response
        ]);
        
    }

}
