<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use DB;
use Auth;
use App\Models\Centrosdecosto;
use App\Models\SiigoDepartamento;
use App\Models\SiigoCiudad;
use App\Models\Subcentro;
use App\Models\Siigo;
use App\Models\CorreosCliente;
use App\Models\Traslado;
use App\Models\Tarifa;
use App\Models\Proyecto;

class CostcenterController extends Controller
{
    
    /*Start Centrosdecosto*/
    public function create(Request $request) {

        $know = DB::table('centrosdecosto')
        ->where('nit', $request->nit)
        ->first();

        if($know!=null) {

            return Response::json([
                'response' => false,
                'message' => 'Existe un cliente con el nit ingresado: '.$know->razonsocial
            ]);

        }else{

            $centro = new Centrosdecosto;
            $centro->nit = $request->nit;
            $centro->razonsocial = $request->razon_social;
            $centro->codigoverificacion = $request->digito;
            $centro->tipoempresa = $request->tipo_empresa;
            $centro->direccion = $request->direccion;
            $centro->ciudad = $request->ciudad; //...
            $centro->departamento = $request->departamento; //...
            $centro->email = $request->email;
            $centro->telefono = $request->telefono;
            $centro->fk_sede = $request->sede;
            $centro->nombre_contacto = $request->nombre_contacto;
            $centro->apellido_contacto = $request->apellido_contacto;
            $centro->state_code = $request->state_code;
            $centro->city_code = $request->city_code;
            $centro->pn = $request->pn;
            if($request->pn==1) {
                $centro->siigo = 1;
            }
            $centro->ejecutivos = $request->ejecutivos;
            $centro->rutas = $request->rutas;
            $centro->tarifa_aotour = $request->tarifa_aotour;
            $centro->tarifa_aotour_proveedor = $request->tarifa_aotour_proveedor;
            $centro->recargo_nocturno = $request->recargo_nocturno;
            $centro->desde = $request->desde;
            $centro->hasta = $request->hasta;
            $centro->inactivo = 12;
            $centro->creado_por = Auth::user()->id;
            $centro->save();

            if($centro->ejecutivos == true) {

                $person = new Subcentro;
                $person->nombre = 'EJECUTIVOS';
                $person->centrosdecosto_id = $centro->id;
                $person->siigo = 1;
                $person->save();

            }else{
                $person = null;
            }

            if($centro->rutas == true) {

                $person1 = new Subcentro;
                $person1->nombre = 'RUTAS';
                $person1->centrosdecosto_id = $centro->id;
                $person1->siigo = 1;
                $person1->save();
            }else{
                $person1 = null;
            }
            
            /* archivos pdf de clientes */
            if($request->pn!=1) {

                //Notificación para crear en siigo
                $asunto = "Cliente por Crear en Siigo";
                $cuerpo = $request->razon_social." fue creado y está pendiente por creación en Siigo. ";

                $usersC = DB::table('users')
                ->where('id_perfil',26)
                ->whereNull('baneado')
                ->get();

                foreach ($usersC as $user) {
                    
                    $fk_responsable = $user->id; //Colocar a los usuarios que apliquen

                    Proyecto::saveNotification($asunto, $cuerpo, $fk_responsable, 11);

                    $channel = 'notificaciones_'.$fk_responsable;
                    $name = 'not'.$fk_responsable;

                    $data = json_encode([
                        'asunto' => $asunto,
                        'cuerpo' => $cuerpo,
                    ]);

                    Proyecto::notificationPusher($channel, $name, $data);

                }

                if($request->hasFile('rut_pdf')){

                    $file = $request->file('rut_pdf');
        
                    $name_file = str_replace(' ', '', $file->getClientOriginalName());
                    
                    $numero = $centro->id;
        
                    $ubicacion_pdf = 'images/clientes/rut';
                    $file->move($ubicacion_pdf, $numero.$name_file);
                    $ubicacion_archivo = $numero.$ubicacion_pdf;
        
                    $update = DB::table('centrosdecosto')
                    ->where('id',$centro->id)
                    ->update([
                        'rut_pdf' => $numero.$name_file
                    ]);
                
                }

                if($request->hasFile('camara_comercio')){

                    $file = $request->file('camara_comercio');
        
                    $name_file = str_replace(' ', '', $file->getClientOriginalName());
                    
                    $numero = $centro->id;
        
                    $ubicacion_pdf = 'images/clientes/camara_comercio';
                    $file->move($ubicacion_pdf, $numero.$name_file);
                    $ubicacion_archivo = $numero.$ubicacion_pdf;
        
                    $update = DB::table('centrosdecosto')
                    ->where('id',$centro->id)
                    ->update([
                        'camara_comercio_pdf' => $numero.$name_file
                    ]);
                
                }

                if($request->hasFile('formato_ingreso')){

                    $file = $request->file('formato_ingreso');
        
                    $name_file = str_replace(' ', '', $file->getClientOriginalName());
                    
                    $numero = $centro->id;
        
                    $ubicacion_pdf = 'images/clientes/formato_ingreso';
                    $file->move($ubicacion_pdf, $numero.$name_file);
                    $ubicacion_archivo = $numero.$ubicacion_pdf;
        
                    $update = DB::table('centrosdecosto')
                    ->where('id',$centro->id)
                    ->update([
                        'formato_ingreso_pdf' => $numero.$name_file
                    ]);
                
                }

                if($request->hasFile('formato_facturacion')){

                    $file = $request->file('formato_facturacion');
        
                    $name_file = str_replace(' ', '', $file->getClientOriginalName());
                    
                    $numero = $centro->id;
        
                    $ubicacion_pdf = 'images/clientes/formato_facturacion';
                    $file->move($ubicacion_pdf, $numero.$name_file);
                    $ubicacion_archivo = $numero.$ubicacion_pdf;
        
                    $update = DB::table('centrosdecosto')
                    ->where('id',$centro->id)
                    ->update([
                        'formato_facturacion_pdf' => $numero.$name_file
                    ]);
                
                }
                
            }

        }

        return Response::json([
            'response' => true,
            'cliente' => $centro,
            'ejecutivos' => $person,
            'rutas' => $person1
        ]);

    }

    public function siigocreate(Request $request) {

        $id = $request->id;

        $centro = Centrosdecosto::find($id);

        try {
            
            $urlSiigo = Siigo::URL_SIIGO;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlSiigo."v1/customers");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);

            curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                \"type\": \"Customer\",
                \"person_type\": \"Company\",
                \"id_type\": \"31\",
                \"identification\": \"".$centro->nit."\",
                \"check_digit\": \"".$centro->codigoverificacion."\",
                \"name\": [
                    \"".$centro->razonsocial."\"
                ],
                \"branch_office\": 0,
                \"fiscal_responsibilities\": [
                    {
                    \"code\": \"R-99-PN\"
                    }
                ],
                \"address\": {
                    \"address\": \"".$centro->direccion."\",
                    \"city\": {
                        \"country_code\": \"Co\",
                        \"state_code\": \"".$centro->state_code."\",
                        \"city_code\": \"".$centro->city_code."\"
                    },
                },
                \"contacts\": [
                    {
                    \"first_name\": \"".$centro->nombre_contacto."\",
                    \"last_name\": \"".$centro->apellido_contacto."\"
                    }
                ],

            }");

            $token = DB::table('siigo')->where('id',1)->first();

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Authorization: Bearer ".$token->token."",
                "Partner-Id: AUTONET"
            ));

            $response = curl_exec($ch);
            curl_close($ch);

        } catch (\Throwable $th) {

            return Response::json([
                'response' => false,
                'sintaxis_error' => $th->getMessage()
            ]);

        }

        /*return Response::json([
            'responses' => $response
        ]);*/
        
        $centro->siigo = 1;
        $centro->siigo_id = json_decode($response)->id;
        $centro->creado_siigo_por = Auth::user()->id;
        $centro->save();

        return Response::json([
            'response' => true,
            'responses' => $response
        ]);

    }

    public function edit(Request $request) {

        $centro = Centrosdecosto::find($request->id);

        /* Actualizar en siigo al editar la empresa cliente */

        try {


            if($centro->nit!=$request->nit){

                $know = DB::table('centrosdecosto')->where('nit',$request->nit)->first();

                if($know) {
                    
                    return Response::json([
                        'response' => 'existe_nit'
                    ]);

                }
            }

            if($centro->razonsocial!=$request->razon_social){
                
                $knows = DB::table('centrosdecosto')->where('razonsocial',$request->razon_social)->first();

                if($knows) {
                    
                    return Response::json([
                        'response' => 'existe_razonsocial'
                    ]);

                }
            }

            if($centro->siigo_id!=null) {

                $urlSiigo = Siigo::URL_SIIGO;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlSiigo."v1/customers/".$centro->siigo_id."");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

                curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                    \"type\": \"Customer\",
                    \"person_type\": \"Company\",
                    \"id_type\": \"31\",
                    \"identification\": \"".$request->nit."\",
                    \"check_digit\": \"".$request->digito."\",
                    \"name\": [
                        \"".$request->razon_social."\"
                    ],
                    \"branch_office\": 0,
                    \"fiscal_responsibilities\": [
                        {
                        \"code\": \"R-99-PN\"
                        }
                    ],
                    \"address\": {
                        \"address\": \"".$request->direccion."\",
                        \"city\": {
                            \"country_code\": \"Co\",
                            \"state_code\": \"".$request->state_code."\",
                            \"city_code\": \"".$request->city_code."\"
                        },
                    },
                    \"contacts\": [
                        {
                        \"first_name\": \"".$request->nombre_contacto."\",
                        \"last_name\": \"".$request->apellido_contacto."\"
                        }
                    ],

                }");

                $token = DB::table('siigo')->where('id',1)->first();

                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Authorization: Bearer ".$token->token."",
                    "Partner-Id: AUTONET"
                ));

                $response = curl_exec($ch);
                curl_close($ch);

                if( json_decode($response)->id ) {

                    $centro->nit = $request->nit;
                    $centro->razonsocial = $request->razon_social;
                    $centro->codigoverificacion = $request->digito;
                    $centro->tipoempresa = $request->tipo_empresa;
                    $centro->direccion = $request->direccion;
                    $centro->ciudad = $request->ciudad;
                    $centro->departamento = $request->departamento;
                    $centro->email = $request->email;
                    $centro->telefono = $request->telefono;
                    $centro->fk_sede = $request->sede;
                    $centro->nombre_contacto = $request->nombre_contacto;
                    $centro->apellido_contacto = $request->apellido_contacto;
                    $centro->state_code = $request->state_code;
                    $centro->city_code = $request->city_code;
                    $centro->recargo_nocturno = $request->recargo_nocturno;
                    $centro->desde = $request->desde;
                    $centro->hasta = $request->hasta;
                    $centro->tarifa_aotour = $request->tarifa_aotour;
                    $centro->tarifa_aotour_proveedor = $request->tarifa_aotour_proveedor;
                    $centro->actualizado_por = Auth::user()->id;
                    $centro->save();

                    return Response::json([
                        'response' => true,
                        'siigo' => $response
                    ]);

                }

            }else{

                $centro->nit = $request->nit;
                $centro->razonsocial = $request->razon_social;
                $centro->codigoverificacion = $request->digito;
                $centro->tipoempresa = $request->tipo_empresa;
                $centro->direccion = $request->direccion;
                $centro->ciudad = $request->ciudad;
                $centro->departamento = $request->departamento;
                $centro->email = $request->email;
                $centro->telefono = $request->telefono;
                $centro->fk_sede = $request->sede;
                $centro->nombre_contacto = $request->nombre_contacto;
                $centro->apellido_contacto = $request->apellido_contacto;
                $centro->state_code = $request->state_code;
                $centro->city_code = $request->city_code;
                $centro->recargo_nocturno = $request->recargo_nocturno;
                $centro->desde = $request->desde;
                $centro->hasta = $request->hasta;
                $centro->tarifa_aotour = $request->tarifa_cliente;
                $centro->tarifa_aotour_proveedor = $request->tarifa_proveedor;
                $centro->actualizado_por = Auth::user()->id;
                $centro->save();

                return Response::json([
                    'response' => true
                ]);

            }

        } catch (\Throwable $th) {

            return Response::json([
                'response' => false,
                'sintaxis_error' => $th->getMessage()
            ]);

        }

    }

    public function list(Request $request) {

        $centrosdecosto = "select id, razonsocial, nit,codigoverificacion, departamento, ciudad, tipoempresa, direccion, ciudad, fk_sede, email, telefono, pn, creado_por, inactivo, recargo_nocturno, desde, hasta, tarifa_aotour as tarifa_cliente, tarifa_aotour_proveedor as tarifa_proveedor, nombre_contacto, apellido_contacto, siigo from centrosdecosto";
        $centrosdecosto = DB::select($centrosdecosto);

        return Response::json([
            'response' => true,
            'centrosdecosto' => $centrosdecosto
        ]);

    }

    public function listwithmails(Request $request) {

        $centrosdecosto = "SELECT
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'id', c.id,
                'razonsocial', c.razonsocial,
                'direccion', c.direccion,
                'email', c.email,
                'telefono', c.telefono,
                'correos', ( SELECT JSON_ARRAYAGG(
                                JSON_OBJECT(
                                'id', cc.id,
                                'correo', cc.correo
                )) 
                FROM centrosdecosto c2 
                left join correos_clientes cc on cc.fk_centrosdecosto = c2.id
                where cc.fk_estado = 22 and c.id = c2.id)
            ) 
        ) as centroCosto
    FROM
        centrosdecosto c";
        $centrosdecosto = DB::select($centrosdecosto);

        return Response::json([
            'response' => true,
            'centrosdecosto' => $centrosdecosto
        ]);

    }

    public function inactivate(Request $request) {

        $centro = Centrosdecosto::find($request->id);
        $centro->inactivo = $request->estado;
        $centro->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function liststatusclients(Request $request) {

        $query = "select e.id as id_estado, e.codigo as codigo_estado, e.nombre as nombre_estado, em.id as id_em from estados e left join estados_maestros em on e.fk_estados_maestros = em.id where em.codigo = 'ESTC' and e.activo = 1";
        $consulta = DB::select($query);

        return Response::json([
            'response' => true,
            'estados' => $consulta
        ]);

    }
    /*End Centrosdecosto*/

    /*Start Subcentrosdecosto*/
    public function createsub(Request $request) {

        $consulta = DB::table('centrosdecosto')
        ->select('centrosdecosto.razonsocial', 'centrosdecosto.pn')
        ->where('centrosdecosto.id',$request->centrosdecosto_id)
        ->first();

        if($consulta->pn===1) { //Persona natural
           
            $person = new Subcentro;
            $person->nombre = $request->nombre;
            $person->apellido = $request->apellido;
            $person->identificacion = $request->identificacion;
            $person->correo = $request->correo;
            $person->direccion = $request->direccion;
            $person->celular = $request->celular;
            $person->centrosdecosto_id = $request->centrosdecosto_id;
            $person->departamento = $request->departamento;
            $person->ciudad = $request->ciudad;
            $person->state_code = $request->state_code;
            $person->city_code = $request->city_code;
            $person->save();

            //Notificación para crear en siigo
            $asunto = "Cliente por Crear en Siigo";
            $cuerpo = $request->nombre." ".$request->apellido." fue creado y está pendiente por creación en Siigo. ";
            $fk_responsable = 2; //Colocar a los usuarios que apliquen

            Proyecto::saveNotification($asunto, $cuerpo, $fk_responsable, 11);

            $channel = 'notificaciones_'.$fk_responsable;
            $name = 'not'.$fk_responsable;

            $data = json_encode([
                'asunto' => $asunto,
                'cuerpo' => $cuerpo,
            ]);

            Proyecto::notificationPusher($channel, $name, $data);

        }else{ //Empresa

            $person = new Subcentro;
            $person->nombre = $request->nombre;
            $person->centrosdecosto_id = $request->centrosdecosto_id;
            $coords = [
                'lat' => $request->lat,
                'lng' => $request->lng
            ];
            $person->coords = json_encode([$coords]);
            $person->siigo = 1;
            $person->save();

        }

        return Response::json([
            'response' => true,
            'subcentro' => $person
        ]);

    }

    public function siigocreatesub(Request $request) {

        $id = $request->id;

        $person = Subcentro::find($id);

        $urlSiigo = Siigo::URL_SIIGO;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlSiigo."v1/customers");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"type\": \"Customer\",
            \"person_type\": \"Person\",
            \"id_type\": \"13\",
            \"identification\": \"".$person->identificacion."\",
            \"check_digit\": \"0\",
            \"name\": [
                \"".$person->nombre."\",
                \"".$person->apellido."\"
            ],
            \"branch_office\": 0,
            \"fiscal_responsibilities\": [
                {
                  \"code\": \"R-99-PN\"
                }
            ],
            \"address\": {
                \"address\": \"".$person->direccion."\",
                \"city\": {
                    \"country_code\": \"Co\",
                    \"state_code\": \"".$person->state_code."\",
                    \"city_code\": \"".$person->city_code."\"
                },
            },
            \"phones\": [
                {
                  \"number\": \"".$person->celular."\"
                }
            ],
            \"contacts\": [
                {
                    \"first_name\": \"".$person->nombre."\",
                    \"last_name\": \"".$person->apellido."\",
                    \"email\": \"".$person->correo."\",
                    \"phone\": {
                        \"number\": \"".$person->celular."\"
                    },
                }
            ],

        }");

        $token = DB::table('siigo')->where('id',1)->first();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ".$token->token."",
            "Partner-Id: AUTONET"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        $person->siigo = 1;
        $person->siigo_id = json_decode($response)->id;
        $person->save();

        return Response::json([
            'response' => true,
            'siigo' => $response
        ]);

    }

    public function editsub(Request $request) {

        $consulta = DB::table('subcentrosdecosto')
        ->join('centrosdecosto', 'centrosdecosto.id', '=', 'subcentrosdecosto.centrosdecosto_id')
        ->select('subcentrosdecosto.id', 'subcentrosdecosto.identificacion', 'subcentrosdecosto.nombre', 'subcentrosdecosto.apellido', 'subcentrosdecosto.siigo_id', 'centrosdecosto.razonsocial', 'centrosdecosto.pn')
        ->where('subcentrosdecosto.id',$request->id)
        ->first();

        if($consulta->pn==1) { //Persona natural
           
            try {
                
                if($consulta->identificacion!=$request->identificacion){

                    $know = DB::table('subcentrosdecosto')->where('identificacion',$request->identificacion)->first();
    
                    if($know) {
                        
                        return Response::json([
                            'response' => 'existe_identificacion'
                        ]);
    
                    }
                }

                if($consulta->siigo_id!=null) {

                    $urlSiigo = Siigo::URL_SIIGO;
    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $urlSiigo."v1/customers/".$consulta->siigo_id."");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "{
                        \"type\": \"Customer\",
                        \"person_type\": \"Person\",
                        \"id_type\": \"13\",
                        \"identification\": \"".$request->identificacion."\",
                        \"check_digit\": \"0\",
                        \"name\": [
                            \"".$request->nombre."\",
                            \"".$request->apellido."\"
                        ],
                        \"branch_office\": 0,
                        \"fiscal_responsibilities\": [
                            {
                              \"code\": \"R-99-PN\"
                            }
                        ],
                        \"address\": {
                            \"address\": \"".$request->direccion."\",
                            \"city\": {
                                \"country_code\": \"Co\",
                                \"state_code\": \"".$request->state_code."\",
                                \"city_code\": \"".$request->city_code."\"
                            },
                        },
                        \"phones\": [
                            {
                              \"number\": \"".$request->celular."\"
                            }
                        ],
                        \"contacts\": [
                            {
                                \"first_name\": \"".$request->nombre."\",
                                \"last_name\": \"".$request->apellido."\",
                                \"email\": \"".$request->correo."\",
                                \"phone\": {
                                    \"number\": \"".$request->celular."\"
                                },
                            }
                        ],
            
                    }");
    
                    $token = DB::table('siigo')->where('id',1)->first();
    
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Content-Type: application/json",
                        "Authorization: Bearer ".$token->token."",
                        "Partner-Id: AUTONET"
                    ));
    
                    $response = curl_exec($ch);
                    curl_close($ch);

                    if( json_decode($response)->id ) {
    
                        $person = Subcentro::find($request->id);
                        $person->nombre = $request->nombre;
                        $person->apellido = $request->apellido;
                        $person->identificacion = $request->identificacion;
                        $person->correo = $request->correo;
                        $person->direccion = $request->direccion;
                        $person->celular = $request->celular;
                        $person->state_code = $request->state_code;
                        $person->city_code = $request->city_code;
                        $person->save();
    
                        return Response::json([
                            'response' => true,
                            'siigo' => $response
                        ]);
    
                    }
    
                }else{
    
                    $person = Subcentro::find($request->id);
                    $person->nombre = $request->nombre;
                    $person->apellido = $request->apellido;
                    $person->identificacion = $request->identificacion;
                    $person->correo = $request->correo;
                    $person->direccion = $request->direccion;
                    $person->celular = $request->celular;
                    $person->save();
    
                    return Response::json([
                        'response' => true
                    ]);
    
                }

            } catch (\Throwable $ths) {
    
                return Response::json([
                    'response' => false,
                    'sintaxis_error' => $ths->getMessage()
                ]);

            }

            /* Actualizar registro de persona natural en siigo */

        }else{ //Empresa

            $person = Subcentro::find($request->id);
            $person->nombre = $request->nombre;
            $person->save();

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function editcoordsub(Request $request) {
        
        $subcentro = Subcentro::find($request->id);
        $coords = [
            'lat' => substr($request->lat, 0, 7),
            'lng' => substr($request->lng, 0, 7)
        ];
        $subcentro->coords = json_encode([$coords]);
        $subcentro->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function listsub(Request $request) {

        $subcentrosdecosto = "select subcentrosdecosto.id, subcentrosdecosto.nombre, subcentrosdecosto.apellido, subcentrosdecosto.identificacion, subcentrosdecosto.correo, subcentrosdecosto.direccion, subcentrosdecosto.celular, subcentrosdecosto.departamento, subcentrosdecosto.ciudad, subcentrosdecosto.state_code, subcentrosdecosto.city_code, subcentrosdecosto.siigo, subcentrosdecosto.coords, subcentrosdecosto.centrosdecosto_id, centrosdecosto.razonsocial, centrosdecosto.pn as tipo from subcentrosdecosto left join centrosdecosto on centrosdecosto.id = subcentrosdecosto.centrosdecosto_id";
        $subcentrosdecosto = DB::select($subcentrosdecosto);

        return Response::json([
            'response' => true,
            'subcostcenter' => $subcentrosdecosto
        ]);

    }

    public function createdepartament(Request $request) {

        $dep = new SiigoDepartamento;
        $dep->nombre = $request->nombre;
        $dep->codigo = $request->codigo;
        $dep->save();

        return Response::json([
            'response' => true
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function editdepartament(Request $request) {

        $departamento = SiigoDepartamento::find($request->id);
        $departamento->codigo = $request->codigo;
        $departamento->nombre = $request->nombre;
        $departamento->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function createcity(Request $request) {

        $dep = new SiigoCiudad;
        $dep->nombre = $request->nombre;
        $dep->codigo = $request->codigo;
        $dep->fk_departamento = $request->departamento;
        $dep->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function editcity(Request $request) {

        $departamento = SiigoCiudad::find($request->id);
        $departamento->codigo = $request->codigo;
        $departamento->nombre = $request->nombre;
        $departamento->save();

        return Response::json([
            'response' => true
        ]);

    }
    /* Departamentos y ciudades */
    public function listdepartaments(Request $request) {

        $departamentos = "select d.id as departamento_id, d.nombre as nombre_departamento, d.codigo as codigo_departamento, c.id as ciudad_id, c.nombre as nombre_ciudad, c.codigo as codigo_ciudad, c.fk_departamento from departamentos d left join ciudades c on c.fk_departamento = d.id";
        $departamentos = DB::select($departamentos);

        return Response::json([
            'response' => true,
            'departamentos' => $departamentos
        ]);

    }

    public function listcity(Request $request) {

        $ciudades = DB::table('ciudades')->get();

        return Response::json([
            'response' => true,
            'ciudades' => $ciudades
        ]);

    }

    public function listcompanytypes(Request $request){

        $query = "select e.id as id_estado, e.codigo as codigo_estado, e.nombre as nombre_estado, em.id as id_em from estados e left join estados_maestros em on e.fk_estados_maestros = em.id where em.codigo = 'TEMP'";
        $consulta = DB::select($query);

        return Response::json([
            'response' => true,
            'estados' => $consulta
        ]);

    }

    /* Correos de cartera y comercial */
    public function createemails(Request $request) {

        $correos = $request->correos;

        foreach ($correos as $mail) {

            $correo = new CorreosCliente;
            $correo->correo = $mail['correo'];
            $correo->fk_centrosdecosto = $request->centrodecosto_id;
            $correo->fk_estado = $mail['tipo'];
            $correo->save();

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function editemail(Request $request) {

        $correo = CorreosCliente::find($request->id);
        $correo->correo = $request->correo;
        $correo->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function deleteemail(Request $request) {

        $delete = DB::table('correos_clientes')->where('id',$request->id)->delete();

        return Response::json([
            'response' => true
        ]);

    }

    public function listemails(Request $request) {

        $query = "select cc.id, cc.correo, cc.fk_centrosdecosto, c.razonsocial, e.nombre, e.codigo, cc.fk_estado from correos_clientes cc left join centrosdecosto c on c.id = cc.fk_centrosdecosto left join estados e on e.id = cc.fk_estado where cc.fk_centrosdecosto = ".$request->centrodecosto_id."";
        $consulta =  DB::select($query);

        return Response::json([
            'response' => true,
            'consulta' => $consulta
        ]);

    }

    /* Tarifas */
    public function createfee(Request $request) {

        $trayecto = new Traslado;
		$trayecto->nombre = $request->nombre;
		$trayecto->fk_sede = $request->sede;
        $trayecto->estado = 1;
		$trayecto->save();

		if($request->centrodecosto_id[0]==97) { //Si es aotour, se modifican los valores de los clientes con TA

			$consultas = "SELECT id as id_centro, razonsocial FROM centrosdecosto WHERE inactivo is null and tarifa_aotour is not null and fk_sede = ".$request->sede." and id!=97 and id!=407";

			$bd = DB::select($consultas);

			foreach ($bd as $key) {

				$tarifa = new Tarifa;
				$tarifa->cliente_auto = $request->tarifa_cliente;
                $tarifa->cliente_van = $request->tarifa_cliente_van;
                $tarifa->proveedor_auto = $request->tarifa_proveedor;
                $tarifa->proveedor_van = $request->tarifa_proveedor_van;
                $tarifa->estado = 1;
                $tarifa->centrodecosto_id = $key->id;
                $tarifa->trayecto_id = $trayecto->id;
				$tarifa->save();

			}

            $tarifa = new Tarifa;
            $tarifa->cliente_auto = $request->tarifa_cliente;
            $tarifa->cliente_van = $request->tarifa_cliente_van;
            $tarifa->proveedor_auto = $request->tarifa_proveedor;
            $tarifa->proveedor_van = $request->tarifa_proveedor_van;
            $tarifa->estado = 1;
            $tarifa->centrodecosto_id = 97;
            $tarifa->trayecto_id = $trayecto->id;
            $tarifa->save();


		}else{

            for ($i=0; $i < count($request->centrodecosto_id); $i++) {
            
                $tarifa = new Tarifa;
                $tarifa->cliente_auto = $request->tarifa_cliente;
                $tarifa->cliente_van = $request->tarifa_cliente_van;
                $tarifa->proveedor_auto = $request->tarifa_proveedor;
                $tarifa->proveedor_van = $request->tarifa_proveedor_van;
                $tarifa->estado = 1;
                $tarifa->centrodecosto_id = $request->centrodecosto_id[$i];
                $tarifa->trayecto_id = $trayecto->id;
                $tarifa->save();
    
            }

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function editfee(Request $request) {

        $id_tarifa = $request->id;
		$cliente_auto = $request->tarifa_cliente;
		$cliente_van = $request->tarifa_cliente_van;
		$proveedor_auto = $request->tarifa_proveedor;
		$proveedor_van = $request->tarifa_proveedor_van; //...

      	$text = '';

      	if($cliente_auto!='') {
      		$text ="cliente_auto = ".$cliente_auto;
      	}

      	if($cliente_van!='') {
      		if($text!=''){
      			$text .=", cliente_van = ".$cliente_van;
      		}else{
      			$text ="cliente_van = ".$cliente_van;
      		}
      	}

      	if($proveedor_auto!='') {
      		if($text!=''){
      			$text .=", proveedor_auto = ".$proveedor_auto;
      		}else{
      			$text ="proveedor_auto = ".$proveedor_auto;
      		}
      	}

      	if($proveedor_van!='') {
      		if($text!='') {
      			$text .=", proveedor_van = ".$proveedor_van;
      		}else{
      			$text ="proveedor_van = ".$proveedor_van;
      		}
      	}

		$query = "UPDATE tarifas SET ".$text." WHERE id = ".$id_tarifa."";

		$consulta = DB::update($query);

        return Response::json([
            'response' => true
        ]);

    }

    public function addfee(Request $request) {

		$tarifa = New Tarifa;
		$tarifa->cliente_auto = $request->tarifa_cliente;
		$tarifa->cliente_van = $request->tarifa_cliente_van;
		$tarifa->proveedor_auto = $request->tarifa_proveedor;
		$tarifa->proveedor_van = $request->tarifa_proveedor_van;
		$tarifa->centrodecosto_id = $request->centrodecosto_id;
		$tarifa->trayecto_id = $request->traslado_id;
        $tarifa->estado = 1;
		$tarifa->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function listfee(Request $request) {

        //$tarifas = "select t.id as id_tarifa, c.razonsocial, t.cliente_auto, t.cliente_van, t.proveedor_auto, t.proveedor_van, t.centrodecosto_id, t2.nombre, t2.fk_sede, t2.estado as estado_trayecto from tarifas t left join traslados t2 on t2.id = t.trayecto_id left join centrosdecosto c on c.id = t.centrodecosto_id";
        //$tarifas = "SELECT t.id, t.nombre, t.estado, JSON_OBJECTAGG(t2.id, JSON_OBJECT( 'cliente', c.razonsocial, 'cliente_auto', t2.cliente_auto, 'cliente_van', t2.cliente_van, 'proveedor_auto', t2.proveedor_auto, 'proveedor_van', t2.proveedor_van)) AS tarifas_clientes FROM traslados t LEFT JOIN tarifas t2 ON t2.trayecto_id = t.id LEFT JOIN centrosdecosto c ON c.id = t2.centrodecosto_id WHERE t.estado = 1 GROUP BY t.id";
        $tarifas = "SELECT t.id, t.nombre, t.fk_sede, t.estado, JSON_ARRAYAGG(JSON_OBJECT('centro_costo', c.razonsocial)) AS tarifas FROM traslados t LEFT JOIN tarifas t2 ON t2.trayecto_id = t.id LEFT JOIN centrosdecosto c ON c.id = t2.centrodecosto_id GROUP BY t.id, t.nombre, t.fk_sede, t.estado";
        $tarifas = DB::select($tarifas);

        return Response::json([
            'response' => true,
            'tarifas' => $tarifas
        ]);

    }

    public function listroute(Request $request) {

        $sql = "select t.id, c.razonsocial, traslados.nombre, t.cliente_auto, t.cliente_van, t.proveedor_auto, t.proveedor_van from traslados left join tarifas t on t.trayecto_id = traslados.id left join centrosdecosto c on c.id = t.centrodecosto_id where traslados.id = ".$request->trayecto_id."";
        $mysql = DB::select($sql);

        return Response::json([
            'response' => true,
            'mysql' => $mysql
        ]);

    }

    public function changeroutestatus(Request $request) {

        $traslado = Traslado::find($request->trayecto_id);
        $traslado->estado = $request->estado;
        $traslado->save();

        return Response::json([
            'response' => true
        ]);

    }

    //...
    public function generateincrease(Request $request) {

        $cliente_auto = $request->incremento_porcentual;
		$clientes = $request->centrosdecosto_id;

		$datas = '';

		$cantidad = count($clientes);

		for ($i=0; $i<count($clientes) ; $i++) {

	        if(count($clientes)>1){

	          if($i==count($clientes)-1){
	            $coma = '';
	          }else{
	            $coma = ',';
	          }

	          $datas .= $clientes[$i].$coma;

	        }else{

	          $datas = $clientes[$i];

	        }

      	}

      	$text = '';

      	if($cliente_auto!='') {

      		$text ="cliente_auto = ".$cliente_auto;
      	}

      	$sel = " SELECT * FROM tarifas where centrodecosto_id in (".$datas.") ";
      	$selee = DB::select($sel);
      	$sw = 0;

      	foreach ($selee as $key) {

      		if($key->centrodecosto_id==97){
      			$sw = 1;
      		}

      		$new = doubleval($key->cliente_auto)*doubleval($cliente_auto)/100;
      		$new2 = doubleval($key->proveedor_auto)*doubleval($cliente_auto)/100;

      		$updates = DB::table('tarifas')
      		->where('id',$key->id)
      		->update([
      			'cliente_auto' => $key->cliente_auto+$new
      		]);
      	}

		if($sw==1) {

			//Consulta de todos los clientes
			$consultas = "SELECT id as id_centro, razonsocial FROM centrosdecosto WHERE inactivo is null and inactivo_total is null and tarifa_aotour is not null and fk_sede = 1 and id!=97 and id!=407";

			$bd = DB::select($consultas); //Consulta del query

			foreach ($bd as $key) { //Recorrer los clientes

				//Conusltar las tarifas de cada iteración de clientes
				$sell = " SELECT * FROM tarifas where centrodecosto_id = ".$key->id_centro."";
      			$fees = DB::select($sell);

      			foreach ($fees as $fee) { //Recorrer las tarifas del cliente iterado

      				$news = doubleval($fee->cliente_auto)*doubleval($cliente_auto)/100; //calcular el aumento del porcentaje
      				$news2 = doubleval($fee->proveedor_auto)*doubleval($cliente_auto)/100; //calcular el aumento del porcentaje

					$updates = DB::table('tarifas')
		      		->where('id',$fee->id)
		      		->update([
		      			'cliente_auto' => $fee->cliente_auto+$news
		      		]);

      			}

			}

		}

        return Response::json([
            'response' => true,
            'selee' => $selee
        ]);

    }
}
