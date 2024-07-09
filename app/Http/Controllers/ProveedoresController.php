<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use DB;
use Auth;
use Mail;
use Hash;
use App\Models\Proveedor;
use App\Models\Conductor;
use App\Models\Vehiculo;
use App\Models\CuentaBancaria;
use App\Models\UserEntidad;
use App\Models\User;
use App\Models\FotosVehiculos;

class ProveedoresController extends Controller
{
    public function create(Request $request) {

        $proveedor = new Proveedor;
        $proveedor->nit = $request->nit;
        $proveedor->codigoverificacion = $request->codigoverificacion;
        $proveedor->razonsocial = $request->razonsocial;
        $proveedor->direccion = $request->direccion;
        $proveedor->email = $request->email;
        $proveedor->telefono = $request->telefono;
        $proveedor->celular = $request->celular;
        //$proveedor->
        //$proveedor->
        //$proveedor->
        //$proveedor->
        //$proveedor->
        //$proveedor->
        $proveedor->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function edit(Request $request) {

        return Response::json([
            'response' => true
        ]);

    }

    public function inactivate(Request $request) {

        $proveedor = Proveedor::find($request->id);
        $proveedor->fk_estado = $request->estado;
        $proveedor->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function list(Request $request) {

        return Response::json([
            'response' => true
        ]);

    }

    /* Inscripción de proveedores */
    public function inscribe(Request $request) {

        $query = "SELECT id, nit FROM proveedores where nit = ".$request->nit."";
        $consulta = DB::select($query);

        if($consulta) {

            return Response::json([
                'response' => false,
                'message' => 'El número de identificación ingresado ya se encuentra registrado en el sistema.'
            ]);

        }else{

            $cuentaBancaria = new CuentaBancaria;
            $cuentaBancaria->fk_tipo_cuenta = $request->tipo_cuenta;
            $cuentaBancaria->fk_banco = $request->entidad_bancaria;
            $cuentaBancaria->numero_cuenta = $request->numero_cuenta;

            if ($request->hasFile('certificacion_pdf')){

                $file_pdf = $request->file('certificacion_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/proveedores/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $cuentaBancaria->certificacion_pdf = $ubicacion_pdf.$name_pdf;
    
            }

            if ($request->hasFile('poder_pdf')){

                $file_pdf = $request->file('poder_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/proveedores/poderes/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $cuentaBancaria->poder_pdf = $ubicacion_pdf.$name_pdf;
    
            }
            
            if($cuentaBancaria->save()) {

                $proveedor = new Proveedor;
                $proveedor->nit = $request->nit;
                $proveedor->digito = $request->digito;
                $proveedor->razonsocial = $request->razonsocial;
                $proveedor->fk_tipos_empresa = $request->fk_tipos_empresa;
                $proveedor->representantelegal = $request->representantelegal;
                $proveedor->identificacion_representante = $request->identificacion_representante;
                $proveedor->direccion = $request->direccion;
                $proveedor->fk_sede = $request->sede_ingreso;
                $proveedor->fk_departamento = $request->departamento;
                $proveedor->fk_ciudad = $request->ciudad;
                $proveedor->email = $request->email;
                $proveedor->celular = $request->celular;
                $proveedor->fk_estado = 46; //Estado por ingresar
                $proveedor->fk_tipo_afiliado = $request->tipo_afiliado;
                $proveedor->fk_cuenta_bancaria = $cuentaBancaria->id;
                
                if ($request->hasFile('foto_proveedor')){
                    $file_pdf = $request->file('foto_proveedor');
                    $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
                    $ubicacion_pdf = 'images/prov_nuevos/proveedores/';
                    $file_pdf->move($ubicacion_pdf, $name_pdf);
                    $proveedor->foto = $ubicacion_pdf.$name_pdf;
                }
                $proveedor->save();

                //Ingreso de conductores
                $conductores = json_decode($request->conductores);

                for ($i=0; $i < count($conductores); $i++){
                    
                    $conductor = new Conductor;
                    $conductor->fecha_vinculacion = date('Y-m-d');
                    $conductor->primer_nombre = $conductores[$i]->primer_nombre;
                    $conductor->segundo_nombre = $conductores[$i]->segundo_nombre;
                    $conductor->primer_apellido = $conductores[$i]->primer_apellido;
                    $conductor->segundo_apellido = $conductores[$i]->segundo_apellido;
                    $conductor->fecha_de_nacimiento = $conductores[$i]->fecha_de_nacimiento;
                    $conductor->fk_departamento = $conductores[$i]->fk_departamento;
                    $conductor->fk_ciudad = $conductores[$i]->fk_ciudad;
                    $conductor->fk_tipo_documento = $conductores[$i]->fk_tipo_documento;
                    $conductor->numero_documento = $conductores[$i]->numero_documento;
                    $conductor->celular = $conductores[$i]->celular;
                    $conductor->direccion = $conductores[$i]->direccion;
                    $conductor->fk_tipo_licencia = $conductores[$i]->fk_tipo_licencia;
                    $conductor->fecha_licencia_expedicion = $conductores[$i]->fecha_licencia_expedicion;
                    $conductor->fecha_licencia_vigencia = $conductores[$i]->fecha_licencia_vigencia;
                    $conductor->fk_genero = $conductores[$i]->fk_genero;
                    $conductor->experiencia = $conductores[$i]->experiencia;
                    $conductor->accidentes = $conductores[$i]->accidentes;
                    if(isset($conductores[$i]->descripcion_accidente)) {
                        $conductor->descripcion_accidente = $conductores[$i]->descripcion_accidente;
                    }
                    $conductor->fk_proveedor = $proveedor->id;
                    $conductor->fk_estado = 46; //POR INGRESAR
                    $conductor->foto = $conductores[$i]->foto;

                    $urls = 'images/prov_nuevos/conductores/';
                    
                    $fotoNameFile = 0;

                    $fotoNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $fotoNameFile .= $characters[$rand];
                    }

                    //$fotoNameFile = 11111;
                    file_put_contents('images/prov_nuevos/conductores/'.$fotoNameFile.'.png', base64_decode($conductores[$i]->foto));
                    $conductor->foto = $urls.$fotoNameFile.'.png';

                    $licenciaNameFile = 0;

                    $licenciaNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $licenciaNameFile .= $characters[$rand];
                    }

                    //$licenciaNameFile = 22222;
                    file_put_contents('images/prov_nuevos/conductores/'.$licenciaNameFile.'.pdf', base64_decode($conductores[$i]->licencia_conduccion_pdf));
                    $conductor->licencia_conduccion_pdf = $urls.$licenciaNameFile.'.pdf';

                    $seguridadSocialNameFile = 0;

                    $seguridadSocialNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $seguridadSocialNameFile .= $characters[$rand];
                    }

                    //$seguridadSocialNameFile = 33333;
                    file_put_contents('images/prov_nuevos/conductores/'.$seguridadSocialNameFile.'.pdf', base64_decode($conductores[$i]->seguridad_social_pdf));
                    $conductor->seguridad_social_pdf = $urls.$seguridadSocialNameFile.'.pdf';

                    $numeroDocumentoNameFile = 0;

                    $numeroDocumentoNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $numeroDocumentoNameFile .= $characters[$rand];
                    }
                    
                    //$numeroDocumentoNameFile = 44444;
                    file_put_contents('images/prov_nuevos/conductores/'.$numeroDocumentoNameFile.'.pdf', base64_decode($conductores[$i]->numero_documento_pdf));
                    $conductor->numero_documento_pdf = $urls.$numeroDocumentoNameFile.'.pdf';

                    $examenesNameFile = 0;

                    $examenesNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $examenesNameFile .= $characters[$rand];
                    }

                    //$examenesNameFile = 55555;
                    file_put_contents('images/prov_nuevos/conductores/'.$examenesNameFile.'.pdf', base64_decode($conductores[$i]->examenes_pdf));
                    $conductor->examenes_pdf = $urls.$examenesNameFile.'.pdf';

                    $conductor->save(); 

                }

                //Ingreso de conductores
                $vehiculos = json_decode($request->vehiculos);
                $sw = 0;

                for ($i=0; $i < count($vehiculos); $i++){
                    $sw = 1;
                    $vehiculo = new Vehiculo;
                    $vehiculo->placa = $vehiculos[$i]->placa;
                    $vehiculo->numero_motor = $vehiculos[$i]->numero_motor;
                    $vehiculo->fk_tipo_vehiculo = $vehiculos[$i]->fk_tipo_vehiculo;
                    $vehiculo->marca = $vehiculos[$i]->marca;
                    $vehiculo->modelo = $vehiculos[$i]->modelo;
                    $vehiculo->ano = $vehiculos[$i]->ano;
                    $vehiculo->capacidad = $vehiculos[$i]->capacidad;
                    $vehiculo->color = $vehiculos[$i]->color;
                    $vehiculo->empresa_afiliada = $vehiculos[$i]->empresa_afiliada;
                    $vehiculo->tarjeta_operacion = $vehiculos[$i]->tarjeta_operacion;
                    $vehiculo->fecha_vigencia_operacion = $vehiculos[$i]->fecha_vigencia_operacion;
                    $vehiculo->fecha_vigencia_soat = $vehiculos[$i]->fecha_vigencia_soat;
                    $vehiculo->fecha_vigencia_tecnomecanica = $vehiculos[$i]->fecha_vigencia_tecnomecanica;
                    $vehiculo->mantenimiento_preventivo = $vehiculos[$i]->mantenimiento_preventivo;
                    $vehiculo->poliza_todo_riesgo = $vehiculos[$i]->poliza_contractual;
                    $vehiculo->poliza_contractual = $vehiculos[$i]->poliza_contractual;
                    $vehiculo->poliza_extracontractual = $vehiculos[$i]->poliza_extracontractual;
                    $vehiculo->fk_proveedor = $proveedor->id;
                    $vehiculo->numero_interno = $vehiculos[$i]->numero_interno;
                    $vehiculo->fk_estado = 46;
                    $vehiculo->numero_vin = $vehiculos[$i]->numero_vin;
                    $vehiculo->cilindraje = $vehiculos[$i]->cilindraje;
                    //$vehiculo->fkv_conductor = $request->fkv_conductor;

                    $urls = 'images/prov_nuevos/vehiculos/';

                    $operacionNameFile = 0;

                    $operacionNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $operacionNameFile .= $characters[$rand];
                    }

                    //$operacionNameFile = 1111;
                    file_put_contents('images/prov_nuevos/vehiculos/'.$operacionNameFile.'.pdf', base64_decode($vehiculos[$i]->tarjeta_operacion_pdf));
                    $vehiculo->tarjeta_operacion_pdf = $urls.$operacionNameFile.'.pdf';

                    $soatNameFile = 0;

                    $soatNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $soatNameFile .= $characters[$rand];
                    }

                    //$soatNameFile = 2222;
                    file_put_contents('images/prov_nuevos/vehiculos/'.$soatNameFile.'.pdf', base64_decode($vehiculos[$i]->soat_pdf));
                    $vehiculo->soat_pdf = $urls.$soatNameFile.'.pdf';

                    $tecnoNameFile = 0;

                    $tecnoNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $tecnoNameFile .= $characters[$rand];
                    }

                    //$tecnoNameFile = 3333;
                    file_put_contents('images/prov_nuevos/vehiculos/'.$tecnoNameFile.'.pdf', base64_decode($vehiculos[$i]->tecnomecanica_pdf));
                    $vehiculo->tecnomecanica_pdf = $urls.$tecnoNameFile.'.pdf';

                    $preventivoNameFile = 0;

                    $preventivoNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $preventivoNameFile .= $characters[$rand];
                    }

                    //$preventivoNameFile = 4444;
                    file_put_contents('images/prov_nuevos/vehiculos/'.$preventivoNameFile.'.pdf', base64_decode($vehiculos[$i]->preventivo_pdf));
                    $vehiculo->preventivo_pdf = $urls.$preventivoNameFile.'.pdf';

                    $contraNameFile = 0;

                    $contraNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $contraNameFile .= $characters[$rand];
                    }

                    //$contraNameFile = 5555;
                    file_put_contents('images/prov_nuevos/vehiculos/'.$contraNameFile.'.pdf', base64_decode($vehiculos[$i]->poliza_contractual_pdf));
                    $vehiculo->poliza_contractual_pdf = $urls.$contraNameFile.'.pdf';

                    $extraNameFile = 0;

                    $extraNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $extraNameFile .= $characters[$rand];
                    }

                    //$extraNameFile = 6666;
                    file_put_contents('images/prov_nuevos/vehiculos/'.$extraNameFile.'.pdf', base64_decode($vehiculos[$i]->poliza_extracontractual_pdf));
                    $vehiculo->poliza_extracontractual_pdf = $urls.$extraNameFile.'.pdf';

                    $propiedadNameFile = 0;

                    $propiedadNameFile = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $propiedadNameFile .= $characters[$rand];
                    }

                    //$propiedadNameFile = 6666;
                    file_put_contents('images/prov_nuevos/vehiculos/'.$propiedadNameFile.'.pdf', base64_decode($vehiculos[$i]->tarjeta_propiedad_pdf));
                    $vehiculo->tarjeta_propiedad_pdf = $urls.$propiedadNameFile.'.pdf';

                    //Fotos

                    $vehiculo->save();

                    $urlss = 'images/prov_nuevos/vehiculos/fotos/';
                    //1
                    $photo1 = 0;

                    $photo1 = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $photo1 .= $characters[$rand];
                    }
                    //$photo1 = 7777;
                    file_put_contents($urlss.$photo1.'.png', base64_decode($vehiculos[$i]->foto1));

                    $foto = new FotosVehiculos;
                    $foto->path = $urlss.$photo1.'.png';
                    $foto->fk_vehiculo = $vehiculo->id;
                    $foto->save();
                    //1

                    //2
                    $photo2 = 0;

                    $photo2 = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $photo2 .= $characters[$rand];
                    }
                    //$photo2 = 8888;
                    file_put_contents($urlss.$photo2.'.png', base64_decode($vehiculos[$i]->foto2));

                    $foto = new FotosVehiculos;
                    $foto->path = $urlss.$photo2.'.png';
                    $foto->fk_vehiculo = $vehiculo->id;
                    $foto->save();
                    //2
                    
                    //3
                    $photo3 = 0;

                    $photo3 = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $photo3 .= $characters[$rand];
                    }
                    //$photo3 = 9999;
                    file_put_contents($urlss.$photo3.'.png', base64_decode($vehiculos[$i]->foto3));

                    $foto = new FotosVehiculos;
                    $foto->path = $urlss.$photo3.'.png';
                    $foto->fk_vehiculo = $vehiculo->id;
                    $foto->save();
                    //3

                    //4
                    $photo4 = 0;

                    $photo4 = "";
                    $characters = array_merge(range('0','9'));
                    $max = count($characters) - 1;
                    for ($u = 0; $u < 8; $u++) {
                        $rand = mt_rand(0, $max);
                        $photo4 .= $characters[$rand];
                    }
                    //$photo4 = 1010;
                    file_put_contents($urlss.$photo4.'.png', base64_decode($vehiculos[$i]->foto4));

                    $foto = new FotosVehiculos;
                    $foto->path = $urlss.$photo4.'.png';
                    $foto->fk_vehiculo = $vehiculo->id;
                    $foto->save();
                    //4

                }

                $data = [
                    'nombre' => $proveedor->razonsocial,
                    'totalConductores' => count($conductores),
                    'totalVehiculos' => count($vehiculos)
                ];
        
                $email = 'comercial@aotour.com.co';
                $cc = ['b.carrillo@aotour.com.co'];
        
                Mail::send('inscripcion_proveedores_emails.email_ingreso', $data, function($message) use ($email, $cc){
                    $message->from('no-reply@aotour.com.co', 'Inscripción de Proveedor');
                    $message->to($email)->subject('Nuevo proveedor para ingreso');
                    $message->Bcc($cc);
                });

                return Response::json([
                    'response' => true,
                    'sw' => $sw
                ]);
                
            }
        }

    }

    public function listbankingentities(Request $request) {

        $query = "SELECT * FROM bancos";
        $entidades = DB::select($query);

        return Response::json([
            'response' => true,
            'entidades' => $entidades
        ]);

    }

    public function listinscribe(Request $request) { //Poner filtro de sólo los proveedores en estado POR INGRESAR

        $query = "SELECT p.*, sd.nombre, dp.nombre as nombre_departamento, c.nombre as nombre_ciudad, es.nombre as estado_proveedor, es.id as id_estado_proveedor, es.codigo, tp.nombre as tipo_afil, tps.nombre as tipo_empresa, bc.nombre as nombre_banco, tip.nombre as tipo_cuenta, cb.numero_cuenta, cb.certificacion_pdf FROM proveedores p left join ciudades c on c.id = p.fk_ciudad LEFT JOIN departamentos dp on dp.id = p.fk_departamento LEFT JOIN sedes sd on sd.id = p.fk_sede left JOIN estados es on es.id = p.fk_estado left JOIN tipos tp on tp.id = p.fk_tipo_afiliado LEFT JOIN estados tps on tps.id = p.fk_tipos_empresa left join cuenta_bancaria cb on cb.id = p.fk_cuenta_bancaria left join bancos bc on bc.id = cb.fk_banco LEFT join tipos tip on tip.id = cb.fk_tipo_cuenta";
        $consulta = DB::select($query);

        return Response::json([
            'response' => true,
            'proveedores' => $consulta
        ]);

    }

    public function sendtoreview(Request $request) {

        $proveedor = Proveedor::find($request->id);
        $proveedor->fk_estado = 47;
        $proveedor->save();

        $data = [
            'nombre' => $proveedor->razonsocial
        ];

        $email = ['talentohumano@aotour.com.co','mantenimiento@aotour.com.co','contabilidad@aotour.com.co'];
        $cc = 'comercial@aotour.com.co';

        Mail::send('inscripcion_proveedores_emails.email_revision', $data, function($message) use ($email, $cc){
            $message->from('no-reply@aotour.com.co', 'Inscripción de Proveedor');
            $message->to($email)->subject('En revisión documental');
            $message->Bcc($cc);
        });

        return Response::json([
            'response' => true
        ]);

    }

    /*public function sendtocorrect(Request $request) { //Link en la plantilla del correo

        $proveedor = Proveedor::find($request->id);
        $proveedor->fk_estado = 48;
        $proveedor->save();

        $total = 0;

        $conductoresConsulta = "SELECT id, primer_nombre, primer_apellido, licencia_conduccion_sw, examenes_sw, seguridad_social_sw, numero_documento_sw FROM `conductores` WHERE fk_proveedor = ".$request->id."";
        $conductoresConsulta = DB::select($conductoresConsulta);
        foreach ($conductoresConsulta as $conductor) {
            if($conductor->licencia_conduccion_sw==2) { 
                $total++;
            }
            if($conductor->seguridad_social_sw==2) { 
                $total++;
            }
            if($conductor->numero_documento_sw==2) { 
                $total++;
            }
            if($conductor->examenes_sw==2) { 
                $total++;
            }
        }

        $vehiculosConsulta = "SELECT id, placa, tarjeta_operacion_sw, tarjeta_propiedad_sw, soat_sw, tecnomecanica_sw, preventivo_sw, poliza_contractual_sw, poliza_extracontractual_sw FROM `vehiculos` WHERE fk_proveedor = ".$request->id."";
        $vehiculosConsulta = DB::select($vehiculosConsulta);
        foreach ($vehiculosConsulta as $vehiculo) {
            if($vehiculo->tarjeta_operacion_sw==2) { 
                $total++;
            }
            if($vehiculo->soat_sw==2) { 
                $total++;
            }
            if($vehiculo->tecnomecanica_sw==2) { 
                $total++;
            }
            if($vehiculo->preventivo_sw==2) { 
                $total++;
            }
            if($vehiculo->poliza_contractual_sw==2) { 
                $total++;
            }
            if($vehiculo->poliza_extracontractual_sw==2) { 
                $total++;
            }
        }

        $link = "https://www.upnetweb.com/auth/documentos?proveedor=".$request->id."";

        $data = [
            'id' => $request->id,
            'total' => $total,
            'link' => $link
        ];

        $email = 'comercial@aotour.com.co'; //Colocar el Email del proveedor
        $cc = ['comercial@aotour.com.co','gustelo1@aotour.com.co']; //Copiar a comercial@aotour.com.co

        Mail::send('inscripcion_proveedores_emails.email_documentos_rechazados', $data, function($message) use ($email, $cc){
            $message->from('no-reply@aotour.com.co', 'Inscripción de Proveedor');
            $message->to($email)->subject('Documentos rechazados');
            $message->Bcc($cc);
        });

        return Response::json([
            'response' => true
        ]);

    }*/

    public function resenddocuments(Request $request) {
        
        $proveedor = Proveedor::find($request->id);
        $proveedor->fk_estado = 47;
        $proveedor->save();

        if($request->tipo=='veh') {

            $VehiculosQuery = "SELECT id, sw_global, tarjeta_operacion_sw, tarjeta_propiedad_sw, soat_sw, tecnomecanica_sw, preventivo_sw, poliza_contractual_sw, poliza_extracontractual_sw FROM vehiculos where fk_proveedor = ".$request->id."";
            $vehiculosConsulta = DB::select($VehiculosQuery);

            foreach ($vehiculosConsulta as $vehiculo) {
                
                if($vehiculo->tarjeta_operacion_sw==null or $vehiculo->tarjeta_propiedad_sw==null or $vehiculo->soat_sw==null or $vehiculo->tecnomecanica_sw==null or $vehiculo->preventivo_sw==null or $vehiculo->poliza_contractual_sw==null or $vehiculo->poliza_extracontractual_sw==null) {
                    $update = DB::table('vehiculos')->where('id',$vehiculo->id)->update(['sw_global' => null,'tarjeta_operacion_obs' => null, 'soat_obs' => null, 'tecnomecanica_obs' => null, 'preventivo_obs' => null, 'poliza_contractual_obs' => null, 'poliza_extracontractual_obs' => null, 'tarjeta_propiedad_obs' => null]);
                }

            }

            //envio de correo de documentos actualizados
            $data = [
                'nombre' => $proveedor->razonsocial,
            ];
    
            $email = 'mantenimiento@aotour.com.co';
            $cc = 'comercial@aotour.com.co';
    
            Mail::send('inscripcion_proveedores_emails.email_documentos_actualizados', $data, function($message) use ($email, $cc){
                $message->from('no-reply@aotour.com.co', 'Inscripción de Proveedor');
                $message->to($email)->subject('Documentos Actualizados');
                $message->Bcc($cc);
            });

        }else if($request->tipo=='con') {

            $ConductoresQuery = "SELECT id, sw_global, licencia_conduccion_sw, seguridad_social_sw, numero_documento_sw, examenes_sw FROM conductores where fk_proveedor = ".$request->id."";
            $conductoresConsulta = DB::select($ConductoresQuery);

            foreach ($conductoresConsulta as $conductor) {
                
                if($conductor->licencia_conduccion_sw==null or $conductor->seguridad_social_sw==null or $conductor->numero_documento_sw==null or $conductor->examenes_sw==null) { 
                    $update = DB::table('conductores')->where('id', $conductor->id)->update(['sw_global' => null,'licencia_conduccion_obs' => null, 'seguridad_social_obs' => null, 'numero_documento_obs' => null, 'examenes_obs' => null]);
                }
            }

            //envio de correo de documentos actualizados
            $data = [
                'nombre' => $proveedor->razonsocial,
            ];
    
            $email = 'talentohumano@aotour.com.co';
            $cc = 'comercial@aotour.com.co';
    
            Mail::send('inscripcion_proveedores_emails.email_documentos_actualizados', $data, function($message) use ($email, $cc){
                $message->from('no-reply@aotour.com.co', 'Inscripción de Proveedor');
                $message->to($email)->subject('Documentos Actualizados');
                $message->Bcc($cc);
            });

        }else{
            
            $cuentaBancaria = CuentaBancaria::find($request->id_cuenta);
            $cuentaBancaria->fk_tipo_cuenta = $request->tipo_cuenta;
            $cuentaBancaria->fk_banco = $request->entidad_bancaria;
            $cuentaBancaria->numero_cuenta = $request->numero_cuenta;

            if ($request->hasFile('certificacion_pdf')){

                $file_pdf = $request->file('certificacion_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/proveedores/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $cuentaBancaria->certificacion_pdf = $ubicacion_pdf.$name_pdf;
    
            }

            if($request->poder_pdf!=null) {

                $urls = 'images/prov_nuevos/proveedores/poderes/';

                $poderNameFile = 0;

                $poderNameFile = "";
                $characters = array_merge(range('0','9'));
                $max = count($characters) - 1;
                for ($i = 0; $i < 8; $i++) {
                    $rand = mt_rand(0, $max);
                    $poderNameFile .= $characters[$rand];
                }
                //$poderNameFile = 121221;

                file_put_contents('images/prov_nuevos/proveedores/poderes/'.$poderNameFile.'.pdf', base64_decode($request->poder_pdf));
                $cuentaBancaria->poder_pdf = $urls.$poderNameFile.'.pdf';

            }

            /*if ($request->hasFile('poder_pdf')){

                $file_pdf = $request->file('poder_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/proveedores/poderes/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $cuentaBancaria->poder_pdf = $ubicacion_pdf.$name_pdf;
    
            }*/
            
            $cuentaBancaria->save();

            $update = DB::table('proveedores')
            ->where('id',$request->id)
            ->update([
                'sw_global' => null
            ]);

            //Enviar correos de actualización de datos bancarios
            $data = [
                'nombre' => $proveedor->razonsocial,
            ];
    
            $email = 'contabilidad@aotour.com.co';
            $cc = 'comercial@aotour.com.co';
    
            Mail::send('inscripcion_proveedores_emails.email_documentos_actualizados', $data, function($message) use ($email, $cc){
                $message->from('no-reply@aotour.com.co', 'Inscripción de Proveedor');
                $message->to($email)->subject('Documentos Actualizados');
                $message->Bcc($cc);
            });

        }
        
        return Response::json([
            'response' => true
        ]);

    }
    //...
    public function approveprovider(Request $request) {

        $proveedor = Proveedor::find($request->id);
        $proveedor->fk_estado = 50; //Activo total
        
        if($proveedor->save()) {

            $conductores = DB::table('conductores')
            ->where('fk_proveedor',$request->id)
            ->update([
                'fk_estado' => 50
            ]);

            $vehiculos = DB::table('vehiculos')
            ->where('fk_proveedor',$request->id)
            ->update([
                'fk_estado' => 50
            ]);

            $data = [
                
            ];
    
            $email = $proveedor->email;
            $cc = ['comercial@aotour.com.co','jefedetransporte@aotour.com.co'];
    
            /*Mail::send('inscripcion_proveedores_emails.email_bienvenido', $data, function($message) use ($email, $cc){
                $message->from('no-reply@aotour.com.co', '¡Inscripción Completada!');
                $message->to($email)->subject('Bienvenido a nuestra familia de proveedores');
                $message->Bcc($cc);
            });*/

            $conds = "Select id, primer_nombre from conductores where fk_proveedor = ".$proveedor->id." and fk_estado = 50";
            $conds = DB::select($conds);
            $totalConductores = count($conds);

            $vehs = "Select id, placa from vehiculos where fk_proveedor = ".$proveedor->id." and fk_estado = 50";
            $vehs = DB::select($vehs);
            $totalVehiculos = count($vehs);

            //Correo de ingreso a todas las áreas
            $data = [
                'nombre' => $proveedor->razonsocial,
                'totalConductores' => $totalConductores,
                'totalVehiculos' => $totalVehiculos
            ];
    
            $email = ['talentohumano@aotour.com.co','mantenimiento@aotour.com.co','contabilidad@aotour.com.co'];
            $cc = ['comercial@aotour.com.co', 'jefedetransporte@aotour.com.co'];
    
            /*Mail::send('inscripcion_proveedores_emails.email_aviso', $data, function($message) use ($email, $cc){
                $message->from('no-reply@aotour.com.co', '¡NUEVO PROVEEDOR!');
                $message->to($email)->subject('Proveedor disponible en el sistema');
                $message->Bcc($cc);
            });*/

            //Creación del usuario del proveedor
            $usuario = new User;
            $usuario->fk_tipo_usuario = 1; //Parametrizar el tipo de usuario para proveedores, clientes y administrativos
            $usuario->id_perfil = 40;
            $usuario->activated = true;

            //$usuario->email = $proveedor->email;

            $nombre_comp = $proveedor->razonsocial;
            $nombre_comp = explode(' ',$nombre_comp);
    		$cant_nombre = count($nombre_comp);

            if($cant_nombre===3){
    			$usuario->first_name = $nombre_comp[0];
    			$usuario->last_name = $nombre_comp[1].' '.$nombre_comp[2];
    		}else if($cant_nombre>=4){
    			$usuario->first_name = $nombre_comp[0].' '.$nombre_comp[1];
    			$usuario->last_name = $nombre_comp[2].' '.$nombre_comp[3];
    		}else{
    			$usuario->first_name = $nombre_comp[0];
                if(isset($nombre_comp[1])){
                    $usuario->last_name = $nombre_comp[1];
                }
    		}
            
            $usuario->username = $proveedor->email;
            $usuario->password = Hash::make($proveedor->nit);
            $usuario->proveedor_id = $proveedor->id;
            $usuario->master = 0;

            $usuario->save();

            $entidad = New UserEntidad;
            $entidad->fk_user_id = $usuario->id;
            if($proveedor->fk_sede==1) {
                $entidad->fk_entidad_id = 1;
            }else{
                $entidad->fk_entidad_id = 2;
            }
            $entidad->estado = true;
            $entidad->save();

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function listaccounttoinscribe(Request $request) {

        $query = "select 
        JSON_ARRAYAGG(
            JSON_OBJECT(
                    'id', p.id,
                    'razonsocial', p.razonsocial,
                    'sw_global', p.sw_global,
                    'banco', b.nombre,
                    'numero_cuenta', c.numero_cuenta,
                    'nombre', t.nombre,
                    'certificacion_pdf', c.certificacion_pdf, 'poder_pdf', c.poder_pdf,
                    'sede', s.nombre,
                    'tarjeta', (
                    SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'id', v.tarjeta_propiedad_pdf
                        ))
                        FROM proveedores p2
                        left join vehiculos v on
                        v.fk_proveedor = p2.id
                        where p.id = p2.id
                    )
            )) as json
    from
        proveedores p
    left join cuenta_bancaria c on
        c.id = p.fk_cuenta_bancaria
    left join vehiculos v on
        v.fk_proveedor = p.id
    left join bancos b on
        b.id = c.fk_banco
    left JOIN tipos t on
        t.id = c.fk_tipo_cuenta
    left join sedes s on
        s.id = p.fk_sede
    where
        p.sw_global is null";
        $proveedores = DB::select($query);

        return Response::json([
            'response' => true,
            'proveedores' => $proveedores
        ]);

    }

    public function listdrivertoinscribe(Request $request) {

        $query = "SELECT c.id, proveedores.razonsocial, c.primer_nombre, c.segundo_nombre, c.primer_apellido, c.segundo_apellido, c.celular, c.numero_documento, c.celular, c.direccion, c.fecha_licencia_expedicion, c.fecha_licencia_expedicion, c.fecha_licencia_vigencia, c.examenes_vigencia, t.nombre as tipo_licencia, tp.nombre as tipo_documento, c.fecha_licencia_vigencia, c.foto, tps.nombre as genero, c.fk_estado, est.nombre as estado_conductor, est.codigo, c.licencia_conduccion_sw, c.licencia_conduccion_pdf, c.seguridad_social_sw, c.seguridad_social_pdf, c.numero_documento_sw, c.numero_documento_pdf, c.examenes_sw, c.examenes_pdf, c.sw_global FROM conductores c left join ciudades ci on ci.id = c.fk_ciudad left join departamentos d on d.id = c.fk_departamento left join tipos t on t.id = c.fk_tipo_licencia left join tipos tp on tp.id = c.fk_tipo_documento left join tipos tps on tps.id = c.fk_genero left join estados est on est.id = c.fk_estado left join proveedores on proveedores.id = c.fk_proveedor where c.sw_global is null and proveedores.fk_estado = 47";
        $conductores = DB::select($query);

        return Response::json([
            'response' => true,
            'conductores' => $conductores
        ]);

    }

    public function listvehicletoinscribe(Request $request) {

        $query = "SELECT v.id, proveedores.razonsocial, v.placa, v.marca, v.modelo, v.modelo, v.ano, v.capacidad, v.color, v.sw_global, v.tarjeta_operacion_sw, v.tarjeta_operacion_pdf, v.tarjeta_propiedad_sw, v.tarjeta_propiedad_pdf, v.soat_sw, v.soat_pdf, v.tecnomecanica_sw, v.tecnomecanica_pdf, v.preventivo_sw, v.preventivo_pdf, v.poliza_contractual_sw, v.poliza_contractual_pdf, v.poliza_extracontractual_sw, v.poliza_extracontractual_pdf, v.tarjeta_operacion, v.fecha_vigencia_operacion, v.fecha_vigencia_soat, v.fecha_vigencia_tecnomecanica, v.mantenimiento_preventivo, v.poliza_contractual, v.poliza_extracontractual, es.nombre as estado_vehiculo, tv.nombre as tipo_vehiculo FROM vehiculos v left join estados tv on tv.id = v.fk_tipo_vehiculo left join estados es on es.id = v.fk_estado left join proveedores on proveedores.id = v.fk_proveedor where v.sw_global is null and proveedores.fk_estado = 47";
        $vehiculos = DB::select($query);

        return Response::json([
            'response' => true,
            'vehiculos' => $vehiculos
        ]);

    }

    public function listbyprovider(Request $request) {

    /*$query = "select 
    JSON_ARRAYAGG(
        JSON_OBJECT(
            'id',p.id, 
            'razonsocial',p.razonsocial, 
            'nit',p.nit,
            'conductor', (SELECT(
                JSON_ARRAYAGG(JSON_OBJECT('id', c.id, 'capacitado', c.soporte_capacitacion, 'nombre', c.primer_nombre, 'apellido', c.primer_apellido, 'licencia', c.licencia_conduccion_sw, 'seguridad_social', c.seguridad_social_sw, 'numero_documento', c.numero_documento_sw, 'examenes', c.examenes_sw, 'estado_global', c.sw_global))
            ) FROM proveedores p2 left join conductores c on c.fk_proveedor = p2.id where c.fk_proveedor = ".$request->id."),
            'vehiculos', (SELECT (
            JSON_ARRAYAGG(JSON_OBJECT('id', v.id, 'placa', v.placa, 'operacion', v.tarjeta_operacion_sw, 'soat', v.soat_sw, 'tecno', v.tecnomecanica_sw, 'preventivo', v.preventivo_sw, 'contra', v.poliza_contractual_sw, 'extra', v.poliza_extracontractual_sw, 'estado_global', v.sw_global))
            ) FROM proveedores p2 left join vehiculos v on v.fk_proveedor = p2.id where v.fk_proveedor = ".$request->id."),
            'cuenta', (SELECT (
            JSON_ARRAYAGG(JSON_OBJECT('id', cb.id, 'tipo', cb.fk_tipo_cuenta, 'numero', cb.numero_cuenta, 'banco', b.nombre, 'estado', p2.sw_global))
            ) FROM proveedores p2 
            left join cuenta_bancaria cb on cb.id = p2.fk_cuenta_bancaria 
            left join bancos b on b.id = cb.fk_banco
            where p2.id = ".$request->id.")
        ) 
    ) as json
from proveedores p
where p.id = ".$request->id." group by p.id";*/

        $query = "select 
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'id',p.id, 
                'razonsocial',p.razonsocial, 
                'nit',p.nit,
                'conductor', (SELECT(
                    JSON_ARRAYAGG(JSON_OBJECT('id', c.id, 'capacitado', c.soporte_capacitacion, 'cc', c.numero_documento, 'nombre', c.primer_nombre, 'apellido', c.primer_apellido, 'licencia', c.licencia_conduccion_sw, 'seguridad_social', c.seguridad_social_sw, 'numero_documento', c.numero_documento_sw, 'examenes', c.examenes_sw, 'estado_global', c.sw_global))
                ) FROM proveedores p2 left join conductores c on c.fk_proveedor = p2.id where c.fk_proveedor = ".$request->id."),
                'vehiculos', (SELECT (
                JSON_ARRAYAGG(JSON_OBJECT('id', v.id, 'placa', v.placa, 'operacion', v.tarjeta_operacion_sw, 'propietario', v.tarjeta_propiedad_sw, 'soat', v.soat_sw, 'tecno', v.tecnomecanica_sw, 'preventivo', v.preventivo_sw, 'contra', v.poliza_contractual_sw, 'extra', v.poliza_extracontractual_sw, 'estado_global', v.sw_global))
                ) FROM proveedores p2 left join vehiculos v on v.fk_proveedor = p2.id where v.fk_proveedor = ".$request->id."),
                'cuenta', (SELECT (
                JSON_ARRAYAGG(JSON_OBJECT('id', cb.id, 'tipo', t.nombre, 'numero', cb.numero_cuenta, 'banco', b.nombre, 'estado', p2.sw_global))
                ) FROM proveedores p2 
                left join cuenta_bancaria cb on cb.id = p2.fk_cuenta_bancaria
                left join tipos t on t.id = cb.fk_tipo_cuenta
                left join bancos b on b.id = cb.fk_banco
                where p2.id = ".$request->id.")
            ) 
        ) as json
    from proveedores p
    where p.id = ".$request->id." group by p.id";

        $proveedores = DB::select($query);

        $ConductoresQuery = "SELECT id, sw_global FROM conductores where fk_proveedor = ".$request->id."";
        $conductoresConsulta = DB::select($ConductoresQuery);

        $swConductoresok = 0;
        $swConductores = 0;

        foreach ($conductoresConsulta as $conductor) {
            
            if($conductor->sw_global==null) {
                $swConductores = 1;
            }
            if($conductor->sw_global==2) {
                $swConductoresok = 1;
            }

        }

        $swVehiculosok = 0;
        $swVehiculos = 0;

        $VehiculosQuery = "SELECT id, sw_global FROM vehiculos where fk_proveedor = ".$request->id."";
        $vehiculosConsulta = DB::select($VehiculosQuery);

        foreach ($vehiculosConsulta as $vehiculo) {
            
            if($vehiculo->sw_global==null) {
                $swVehiculos = 1;
            }
            if($vehiculo->sw_global==2) {
                $swVehiculosok = 1;
            }

        }

        $searchProveedor = Proveedor::find($request->id);

        if($swConductores==1 or $swVehiculos==1) { //Faltan por aprobar
            $sw_global = null;
        }else if($swConductoresok==1 or $swVehiculosok==1){
            //$updateProveedor = DB::table('proveedores')->where('id',$request->id)->update(['sw_global'=>2]);
            $sw_global = 2;
        }else{
            //$updateProveedor = DB::table('proveedores')->where('id',$request->id)->update(['sw_global'=>1]);
            $sw_global = 1;
        }

        if($sw_global==1 and $searchProveedor->sw_global==1) {
            $sw_global = 1;
        }else{
            $sw_global = null;
        }

        return Response::json([
            'response' => true,
            'proveedores' => $proveedores,
            'sw_global' => $sw_global
        ]);

    }

    public function sendtocapacite(Request $request) {

        $update = DB::table('proveedores')
        ->where('id',$request->id)
        ->update([
            'fk_estado' => 52
        ]);

        //correo a th sobre el conductor pendiente por capacitar PLANTILLA

        $conductores = DB::table('conductores')
        ->where('fk_proveedor',$request->id)
        ->get();

        foreach ($conductores as $conductor) {
            
            $texto = $conductor->primer_nombre.' '.$conductor->primer_apellido.' se encuentra habilitado para capacitar.';

            $data = [
                'texto' => $texto
            ];
    
            $email = 'talentohumano@aotour.com.co';
            $cc = ['comercial@aotour.com.co'];
    
            Mail::send('inscripcion_proveedores_emails.email_capacitar', $data, function($message) use ($email, $cc){
                $message->from('no-reply@aotour.com.co', 'Capacitación de Conductor');
                $message->to($email)->subject('Tienes un nuevo conductor por capacitar');
                $message->Bcc($cc);
            });

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function uploadcapacite(Request $request) {

        $conductor = Conductor::find($request->id);

        $urls = 'images/prov_nuevos/conductores/capacitaciones';

        $capacitacionNameFile = 0;

        $capacitacionNameFile = "";
        $characters = array_merge(range('0','9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < 8; $i++) {
            $rand = mt_rand(0, $max);
            $capacitacionNameFile .= $characters[$rand];
        }
        //$capacitacionNameFile = 121221;

        file_put_contents('images/prov_nuevos/conductores/capacitaciones/'.$capacitacionNameFile.'.pdf', base64_decode($request->file));
        $conductor->soporte_capacitacion = $urls.$capacitacionNameFile.'.pdf';
        $conductor->save();

        $conductores = "SELECT id, primer_nombre, soporte_capacitacion FROM conductores where fk_proveedor = ".$conductor->fk_proveedor."";
        $conductores = DB::select($conductores);

        $sw = 1;

        foreach ($conductores as $cond) {
            if($cond->soporte_capacitacion==null) {
                $sw = 0;
            }
        }

        if($sw==1) {
            
            $proveedor = Proveedor::find($conductor->fk_proveedor);
            $proveedor->fk_estado = 50; //Activo total
            
            //if(1>0) {
            if($proveedor->save()) {

                $conductores = DB::table('conductores')
                ->where('fk_proveedor',$conductor->fk_proveedor)
                ->update([
                    'fk_estado' => 50
                ]);

                $vehiculos = DB::table('vehiculos')
                ->where('fk_proveedor',$conductor->fk_proveedor)
                ->update([
                    'fk_estado' => 50
                ]);

                $data = [
                    
                ];
        
                $email = $proveedor->email;//'sistemas@aotour.com.co';
                $cc = ['comercial@aotour.com.co'];
        
                /*Mail::send('inscripcion_proveedores_emails.email_bienvenido', $data, function($message) use ($email, $cc){
                    $message->from('no-reply@aotour.com.co', '¡Inscripción Completada!');
                    $message->to($email)->subject('Bienvenido a nuestra familia de proveedores');
                    $message->Bcc($cc);
                });*/

                $conds = "Select id, primer_nombre from conductores where fk_proveedor = ".$proveedor->id." and fk_estado = 50";
                $conds = DB::select($conds);
                $totalConductores = count($conds);

                $vehs = "Select id, placa from vehiculos where fk_proveedor = ".$proveedor->id." and fk_estado = 50";
                $vehs = DB::select($vehs);
                $totalVehiculos = count($vehs);

                //Correo de ingreso a todas las áreas
                $data = [
                    'nombre' => $proveedor->razonsocial,
                    'totalConductores' => $totalConductores,
                    'totalVehiculos' => $totalVehiculos
                ];
        
                $email = ['mantenimiento@aotour.com.co','talentohumano@aotour.com.co','contabilidad@aotour.com.co'];
                $cc = ['comercial@aotour.com.co'];
        
                Mail::send('inscripcion_proveedores_emails.email_aviso', $data, function($message) use ($email, $cc){
                    $message->from('no-reply@aotour.com.co', '¡NUEVO PROVEEDOR!');
                    $message->to($email)->subject('Proveedor disponible en el sistema');
                    $message->Bcc($cc);
                });

                //Creación del usuario del proveedor
                $usuario = new User;
                $usuario->fk_tipo_usuario = 1; //Parametrizar el tipo de usuario para proveedores, clientes y administrativos
                $usuario->id_perfil = 40;
                $usuario->activated = true;

                //$usuario->email = $proveedor->email;

                $nombre_comp = $proveedor->razonsocial;
                $nombre_comp = explode(' ',$nombre_comp);
                $cant_nombre = count($nombre_comp);

                if($cant_nombre===3){
                    $usuario->first_name = $nombre_comp[0];
                    $usuario->last_name = $nombre_comp[1].' '.$nombre_comp[2];
                }else if($cant_nombre>=4){
                    $usuario->first_name = $nombre_comp[0].' '.$nombre_comp[1];
                    $usuario->last_name = $nombre_comp[2].' '.$nombre_comp[3];
                }else{
                    $usuario->first_name = $nombre_comp[0];
                    if(isset($nombre_comp[1])){
                        $usuario->last_name = $nombre_comp[1];
                    }
                }
                
                $usuario->username = $proveedor->email;
                $usuario->password = Hash::make($proveedor->nit);
                $usuario->proveedor_id = $proveedor->id;
                $usuario->master = 0;

                $usuario->save();

                $entidad = New UserEntidad;
                $entidad->fk_user_id = $usuario->id;
                if($proveedor->fk_sede==1) {
                    $entidad->fk_entidad_id = 1;
                }else{
                    $entidad->fk_entidad_id = 2;
                }
                $entidad->estado = true;
                $entidad->save();

            }

            $proveedors = DB::table('proveedores')
            ->leftJoin('cuenta_bancaria', 'cuenta_bancaria.id', '=', 'proveedores.fk_cuenta_bancaria')
            ->leftJoin('bancos', 'bancos.id', '=', 'cuenta_bancaria.fk_banco')
            ->leftJoin('tipos', 'tipos.id', '=', 'cuenta_bancaria.fk_tipo_cuenta')
            ->select('proveedores.*', 'cuenta_bancaria.numero_cuenta', 'cuenta_bancaria.fk_banco', 'cuenta_bancaria.fk_tipo_cuenta')
            ->where('proveedores.id',$conductor->fk_proveedor)
            ->first();

            $drivers = DB::table('conductores')
            ->where('fk_proveedor',$conductor->fk_proveedor)
            ->get();

            $vehicles = DB::table('vehiculos')
            ->where('fk_proveedor',$conductor->fk_proveedor)
            ->get();

            return Response::json([
                'response' => true,
                'proveedor' => $proveedors,
                'conductores' => $drivers,
                'vehiculos' => $vehicles
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);
        }

    }

    public function approvedocprovider(Request $request) {

        $proveedor = Proveedor::find($request->id);
        $razon = $proveedor->razonsocial;

        if($request->estado==1) {

            $proveedor->sw_global = 1;

            //Enviar mensaje de documentos no aprobados
            $link = 0;
            
            $texto = 'Sr(a) '.$razon.', su documentación financiera fue APROBADA!';

            $data = [
                'id' => $request->id,
                'texto' => $texto,
                'link' => $link,
                'titulo' => 'PROVEEDOR',
                'motivo' => 0
            ];

            $email = $proveedor->email;
            $cc = ['comercial@aotour.com.co','contabilidad@aotour.com.co'];

            Mail::send('inscripcion_proveedores_emails.email_documentos_aprobados_c', $data, function($message) use ($email, $cc, $razon){
                $message->from('no-reply@aotour.com.co', 'Documentación de Proveedor - '.$razon);
                $message->to($email)->subject('Documentos Aprobados');
                $message->Bcc($cc);
            });
            //

        }else{

            $proveedor->sw_global = 2;

            //Enviar mensaje de documentos no aprobados
            $link = "https://www.upnetweb.com/auth/documentos?proveedor=".$request->id."&document=cue";
            
            $texto = 'Sr(a) '.$razon.', su documentación financiera fue RECHAZADA por el siguiente motivo:';

            $data = [
                'id' => $request->id,
                'texto' => $texto,
                'link' => $link,
                'titulo' => 'PROVEEDOR',
                'motivo' => $request->motivo
            ];

            $email = $proveedor->email;
            $cc = ['comercial@aotour.com.co', 'contabilidad@aotour.com.co'];

            Mail::send('inscripcion_proveedores_emails.email_documentos_aprobados_c', $data, function($message) use ($email, $cc, $razon){
                $message->from('no-reply@aotour.com.co', 'Documentación de Proveedor - '.$razon);
                $message->to($email)->subject('Documentos Rechazados');
                $message->Bcc($cc);
            });
            //
        }
        
        $proveedor->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function approvedocdriver(Request $request) {

        $conductor = Conductor::find($request->id);
        $name = $conductor->primer_nombre;
        $name2 = $conductor->primer_apellido;
        
        if($request->name=='licencia_conduccion_pdf') {
            $conductor->licencia_conduccion_sw = $request->estado;
            if($request->motivo!=null) {
                $conductor->licencia_conduccion_obs = $request->motivo;
            }
        }else if($request->name=='seguridad_social_pdf') {
            $conductor->seguridad_social_sw = $request->estado;
            if($request->motivo!=null) {
                $conductor->seguridad_social_obs = $request->motivo;
            }
        }else if($request->name=='numero_documento_pdf') {
            $conductor->numero_documento_sw = $request->estado;
            if($request->motivo!=null) {
                $conductor->numero_documento_obs = $request->motivo;
            }
        }else if($request->name=='examenes_pdf') {
            $conductor->examenes_sw = $request->estado;
            if($request->motivo!=null) {
                $conductor->examenes_obs = $request->motivo;
            }
        }

        $conductor->save();

        if($conductor->licencia_conduccion_sw == 1 and $conductor->seguridad_social_sw == 1 and $conductor->numero_documento_sw == 1 and $conductor->examenes_sw == 1) {
            
            $update = DB::table('conductores')->where('id',$request->id)->update(['sw_global' => 1]);

            //Enviar mensaje de documentos aprobados de ese conductor
            $link = 0;
            
            $texto = 'La documentación del conductor '.$name.' '.$name2.' fue APROBADA!';

            $data = [
                'id' => $conductor->fk_proveedor,
                'texto' => $texto,
                'link' => $link,
                'titulo' => 'CONDUCTOR'
            ];

            $proveedor = Proveedor::find($conductor->fk_proveedor);

            $email = $proveedor->email;
            $cc = ['comercial@aotour.com.co','talentohumano@aotour.com.co'];

            Mail::send('inscripcion_proveedores_emails.email_documentos_aprobados', $data, function($message) use ($email, $cc, $name, $name2){
                $message->from('no-reply@aotour.com.co', 'Documentación de Conductor - '.$name.' '.$name2);
                $message->to($email)->subject('Documentos Aprobados');
                $message->Bcc($cc);
            });
            //

        }else if($conductor->licencia_conduccion_sw != null and $conductor->seguridad_social_sw != null and $conductor->numero_documento_sw != null and $conductor->examenes_sw != null) {
            $update = DB::table('conductores')->where('id',$request->id)->update(['sw_global' => 2]);

            //Enviar mensaje de documentos no aprobados
            $link = "https://www.upnetweb.com/auth/documentos?proveedor=".$conductor->fk_proveedor."&document=con";
            
            $texto = 'Algunos documentos del conductor '.$name.' '.$name2.' fueron RECHAZADOS!';

            $data = [
                'id' => $conductor->fk_proveedor,
                'texto' => $texto,
                'link' => $link,
                'titulo' => 'CONDUCTOR'
            ];

            $proveedor = Proveedor::find($conductor->fk_proveedor);

            $email = $proveedor->email;
            $cc = ['comercial@aotour.com.co','talentohumano@aotour.com.co'];

            Mail::send('inscripcion_proveedores_emails.email_documentos_aprobados', $data, function($message) use ($email, $cc, $name, $name2){
                $message->from('no-reply@aotour.com.co', 'Documentación de Conductor - '.$name.' '.$name2);
                $message->to($email)->subject('Documentos Rechazados');
                $message->Bcc($cc);
            });
            //

        }

        return Response::json([
            'response' => true
        ]);

    }
    
    public function approvedocvehicle(Request $request) {

        $vehiculo = Vehiculo::find($request->id);
        $placa = $vehiculo->placa;

        if($request->name=='tarjeta_operacion_pdf'){
            $vehiculo->tarjeta_operacion_sw = $request->estado;
            if($request->motivo!=null) {
                $vehiculo->tarjeta_operacion_obs = $request->motivo;
            }
        }else if($request->name=='tarjeta_propiedad_pdf'){
            $vehiculo->tarjeta_propiedad_sw = $request->estado;

            if($request->motivo!=null) {
                $vehiculo->tarjeta_propiedad_obs = $request->motivo;
            }
        }else if($request->name=='soat_pdf') {
            $vehiculo->soat_sw = $request->estado;

            if($request->motivo!=null) {
                $vehiculo->soat_obs = $request->motivo;
            }

        }else if($request->name=='tecnomecanica_pdf') {
            $vehiculo->tecnomecanica_sw = $request->estado;

            if($request->motivo!=null) {
                $vehiculo->tecnomecanica_obs = $request->motivo;
            }
        }else if($request->name=='preventivo_pdf') {
            $vehiculo->preventivo_sw = $request->estado;

            if($request->motivo!=null) {
                $vehiculo->preventivo_obs = $request->motivo;
            }
        }else if($request->name=='poliza_contractual_pdf') {
            $vehiculo->poliza_contractual_sw = $request->estado;

            if($request->motivo!=null) {
                $vehiculo->poliza_contractual_obs = $request->motivo;
            }
        }else if($request->name=='poliza_extracontractual_pdf') {
            $vehiculo->poliza_extracontractual_sw = $request->estado;

            if($request->motivo!=null) {
                $vehiculo->poliza_extracontractual_obs = $request->motivo;
            }
        }

        $vehiculo->save();

        if($vehiculo->tarjeta_operacion_sw == 1 and $vehiculo->tarjeta_propiedad_sw == 1 and $vehiculo->soat_sw == 1 and $vehiculo->tecnomecanica_sw == 1 and $vehiculo->preventivo_sw == 1 and $vehiculo->poliza_contractual_sw == 1 and $vehiculo->poliza_extracontractual_sw == 1) {
            
            $update = DB::table('vehiculos')->where('id',$request->id)->update(['sw_global' => 1]);

            //Enviar mensaje de documentos no aprobados
            $link = 0;
            
            $texto = 'La documentación del vehículo '.$placa.' fue APROBADA!';

            $data = [
                'id' => $vehiculo->fk_proveedor,
                'texto' => $texto,
                'link' => $link,
                'titulo' => 'VEHÍCULO'
            ];

            $proveedor = Proveedor::find($vehiculo->fk_proveedor);

            $email = $proveedor->email;
            $cc = ['comercial@aotour.com.co','mantenimiento@aotour.com.co'];

            Mail::send('inscripcion_proveedores_emails.email_documentos_aprobados', $data, function($message) use ($email, $cc, $placa){
                $message->from('no-reply@aotour.com.co', 'Documentación de Vehículo - '.$placa);
                $message->to($email)->subject('Documentos Aprobados');
                $message->Bcc($cc);
            });
            //

        }else if($vehiculo->tarjeta_operacion_sw != null and $vehiculo->tarjeta_propiedad_sw != null and $vehiculo->soat_sw != null and $vehiculo->tecnomecanica_sw != null and $vehiculo->preventivo_sw != null and $vehiculo->poliza_contractual_sw != null and $vehiculo->poliza_extracontractual_sw != null) {
            
            $update = DB::table('vehiculos')->where('id',$request->id)->update(['sw_global' => 2]);

            //Enviar mensaje de documentos no aprobados de ese vehículo
            $link = "https://www.upnetweb.com/auth/documentos?proveedor=".$vehiculo->fk_proveedor."&document=veh";
            
            $texto = 'Algunos documentos del vehiculo '.$placa.' fueron RECHAZADOS!';

            $data = [
                'id' => $vehiculo->fk_proveedor,
                'texto' => $texto,
                'link' => $link,
                'titulo' => 'VEHÍCULO'
            ];

            $proveedor = Proveedor::find($vehiculo->fk_proveedor);

            $email = $proveedor->email;
            $cc = ['comercial@aotour.com.co','mantenimiento@aotour.com.co'];

            Mail::send('inscripcion_proveedores_emails.email_documentos_aprobados', $data, function($message) use ($email, $cc, $placa){
                $message->from('no-reply@aotour.com.co', 'Documentación de Vehículo - '.$placa);
                $message->to($email)->subject('Documentos Rechazados');
                $message->Bcc($cc);
            });
            //

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function submitdocdriver(Request $request) {

        $conductor = Conductor::find($request->id);
        $urls = 'images/prov_nuevos/conductores/';

        if($request->name=='licencia_conduccion_pdf') {

            $licenciaNameFile = 0;

            $licenciaNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $licenciaNameFile .= $characters[$rand];
            }

            //$licenciaNameFile = 22222;
            file_put_contents('images/prov_nuevos/conductores/'.$licenciaNameFile.'.pdf', base64_decode($request->file));
            $conductor->licencia_conduccion_pdf = $urls.$licenciaNameFile.'.pdf';
            $conductor->licencia_conduccion_sw = null;

        }else if($request->name=='seguridad_social_pdf') {

            $seguridadSocialNameFile = 0;

            $seguridadSocialNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $seguridadSocialNameFile .= $characters[$rand];
            }
            
            //$seguridadSocialNameFile = 33333;
            file_put_contents('images/prov_nuevos/conductores/'.$seguridadSocialNameFile.'.pdf', base64_decode($request->file));
            $conductor->seguridad_social_pdf = $urls.$seguridadSocialNameFile.'.pdf';
            $conductor->seguridad_social_sw = null;

        }else if($request->name=='numero_documento_pdf') {
            
            $numeroDocumentoNameFile = 0;

            $numeroDocumentoNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $numeroDocumentoNameFile .= $characters[$rand];
            }
            
            //$numeroDocumentoNameFile = 44444;
            file_put_contents('images/prov_nuevos/conductores/'.$numeroDocumentoNameFile.'.pdf', base64_decode($request->file));
            $conductor->numero_documento_pdf = $urls.$numeroDocumentoNameFile.'.pdf';
            $conductor->numero_documento_sw = null;

        }else if($request->name=='examenes_pdf') {

            $examenesNameFile = 0;

            $examenesNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $examenesNameFile .= $characters[$rand];
            }

            //$examenesNameFile = 55555;
            file_put_contents('images/prov_nuevos/conductores/'.$examenesNameFile.'.pdf', base64_decode($request->file));
            $conductor->examenes_pdf = $urls.$examenesNameFile.'.pdf';
            $conductor->examenes_sw = null;

        }

        $conductor->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function submitdocvehicle(Request $request) {

        $vehiculo = Vehiculo::find($request->id);
        $urls = 'images/prov_nuevos/vehiculos/';

        if($request->name=='tarjeta_operacion_pdf') {

            $operacionNameFile = 0;

            $operacionNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $operacionNameFile .= $characters[$rand];
            }
            
            //$operacionNameFile = 1111;
            file_put_contents('images/prov_nuevos/vehiculos/'.$operacionNameFile.'.pdf', base64_decode($request->file));
            $vehiculo->tarjeta_operacion_pdf = $urls.$operacionNameFile.'.pdf';
            $vehiculo->tarjeta_operacion_sw = null;

        }else if($request->name=='tarjeta_propiedad_pdf') {

            $propiedadNameFile = 0;

            $propiedadNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $propiedadNameFile .= $characters[$rand];
            }
            
            //$propiedadNameFile = 1111;
            file_put_contents('images/prov_nuevos/vehiculos/'.$propiedadNameFile.'.pdf', base64_decode($request->file));
            $vehiculo->tarjeta_propiedad_pdf = $urls.$propiedadNameFile.'.pdf';
            $vehiculo->tarjeta_propiedad_sw = null;

        }else if($request->name=='soat_pdf') {

            $soatNameFile = 0;

            $soatNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $soatNameFile .= $characters[$rand];
            }

            //$soatNameFile = 2222;
            file_put_contents('images/prov_nuevos/vehiculos/'.$soatNameFile.'.pdf', base64_decode($request->file));
            $vehiculo->soat_pdf = $urls.$soatNameFile.'.pdf';
            $vehiculo->soat_sw = null;

        }else if($request->name=='tecnomecanica_pdf') {

            $tecnoNameFile = 0;

            $tecnoNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $tecnoNameFile .= $characters[$rand];
            }

            //$tecnoNameFile = 3333;
            file_put_contents('images/prov_nuevos/vehiculos/'.$tecnoNameFile.'.pdf', base64_decode($request->file));
            $vehiculo->tecnomecanica_pdf = $urls.$tecnoNameFile.'.pdf';
            $vehiculo->tecnomecanica_sw = null;

        }else if($request->name=='preventivo_pdf') {

            $preventivoNameFile = 0;

            $preventivoNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $preventivoNameFile .= $characters[$rand];
            }

            //$preventivoNameFile = 4444;
            file_put_contents('images/prov_nuevos/vehiculos/'.$preventivoNameFile.'.pdf', base64_decode($request->file));
            $vehiculo->preventivo_pdf = $urls.$preventivoNameFile.'.pdf';
            $vehiculo->preventivo_sw = null;

        }else if($request->name=='poliza_contractual_pdf') {

            $contraNameFile = 0;

            $contraNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $contraNameFile .= $characters[$rand];
            }

            //$contraNameFile = 5555;
            file_put_contents('images/prov_nuevos/vehiculos/'.$contraNameFile.'.pdf', base64_decode($request->file));
            $vehiculo->poliza_contractual_pdf = $urls.$contraNameFile.'.pdf';
            $vehiculo->poliza_contractual_sw = null;

        }else if($request->name=='poliza_extracontractual_pdf') {

            $extraNameFile = 0;

            $extraNameFile = "";
            $characters = array_merge(range('0','9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $rand = mt_rand(0, $max);
                $extraNameFile .= $characters[$rand];
            }

            //$extraNameFile = 6666;
            file_put_contents('images/prov_nuevos/vehiculos/'.$extraNameFile.'.pdf', base64_decode($request->file));
            $vehiculo->poliza_extracontractual_pdf = $urls.$extraNameFile.'.pdf';
            $vehiculo->poliza_extracontractual_sw = null;

        }
        
        $vehiculo->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function sendfiles(Request $request) {

        $conductor = Conductor::find($request->id);
        $sw = 0;


        if ($request->hasFile('licencia_conduccion_pdf')){

            $file_pdf = $request->file('licencia_conduccion_pdf');
            $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());

            $ubicacion_pdf = 'images/prov_nuevos/conductores/';
            $file_pdf->move($ubicacion_pdf, $name_pdf);
            $conductor->licencia_conduccion_pdf = $ubicacion_pdf.$name_pdf;

        }

        if ($request->hasFile('seguridad_social_pdf')){

            $file_pdf = $request->file('seguridad_social_pdf');
            $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());

            $ubicacion_pdf = 'images/prov_nuevos/conductores/';
            $file_pdf->move($ubicacion_pdf, $name_pdf);
            $conductor->seguridad_social_pdf = $ubicacion_pdf.$name_pdf;

        }

        if($conductor->save()) {

            /*ENVÍO DE CORREO AL PROVEEDOR*/
            $email = ['comercial@aotour.com.co','gustelo1@aotour.com.co'];
            //$email = ['talentohumano@aotour.com.co'];

            $nombre = Auth::user()->first_name;

            $data = [
                'id'    => 1,
                'nombre' => $nombre
            ];

            //Mail::send('portalproveedores.emails.documentos_actualizados', $data, function($message) use ($email){
            //$message->from('no-reply@aotour.com.co', 'PROVEEDORES');
            //$message->to($email)->subject('Documentación de Conductor');
            //$message->cc('aotourdeveloper@gmail.com');
            //});
            //FIN ENVÍO DE CORREO AL PROVEEDOR*/

        }
          
        return Response::json([
            'response' => true
        ]);

    }

    public function listdriversdate(Request $request) {

        $conductores = "SELECT conductores.id, conductores.primer_nombre, conductores.segundo_nombre, conductores.primer_apellido, conductores.segundo_apellido, conductores.fecha_inicio, ciudades.nombre as nombre_ciudad, departamentos.nombre as nombre_departamento, p.razonsocial, conductores.celular FROM `conductores` left join proveedores p on p.id = conductores.fk_proveedor left join estados e on e.id = conductores.fk_estado left join ciudades on ciudades.id = conductores.fk_ciudad left join departamentos on departamentos.id = conductores.fk_departamento where p.fk_estado = 50 and conductores.fecha_inicio is null";
        $conductores = DB::select($conductores);

        return Response::json([
            'response' => true,
            'conductores' => $conductores
        ]);

    }

    public function savedatestart(Request $request) {

        $conductor = Conductor::find($request->id);
        $conductor->fecha_inicio = $request->fecha;
        $conductor->save();

        return Response::json([
            'response' => true
        ]);
    }

    //old
    public function inscribedriver(Request $request) {

        $query = "SELECT id, primer_nombre FROM `conductores` WHERE numero_documento = ".$request->numero_documento."";
        $consulta = DB::select($query);

        if($consulta) {

            return Response::json([
                'response' => false,
                'message' => 'El número de identificación ingresado ya se encuentra registrado en el sistema.',
                'consulta' => $consulta
            ]);

        }else{

            $conductor = new Conductor;
            $conductor->fecha_vinculacion = date('Y-m-d');
            $conductor->primer_nombre = $request->primer_nombre;
            $conductor->segundo_nombre = $request->segundo_nombre;
            $conductor->primer_apellido = $request->primer_apellido;
            $conductor->segundo_apellido = $request->segundo_apellido;
            $conductor->fecha_vinculacion = $request->fecha_vinculacion;
            $conductor->fecha_de_nacimiento = $request->fecha_de_nacimiento;
            $conductor->fk_departamento = $request->fk_departamento;
            $conductor->fk_ciudad = $request->fk_ciudad;
            $conductor->fk_tipo_documento = $request->fk_tipo_documento;
            $conductor->numero_documento = $request->numero_documento;
            $conductor->celular = $request->celular;
            $conductor->direccion = $request->direccion;
            $conductor->fk_tipo_licencia = $request->fk_tipo_licencia;
            $conductor->fecha_licencia_expedicion = $request->fecha_licencia_expedicion;
            $conductor->fecha_licencia_vigencia = $request->fecha_licencia_vigencia;
            $conductor->fk_genero = $request->fk_genero;
            $conductor->experiencia = $request->experiencia;
            $conductor->accidentes = $request->accidentes;
            $conductor->descripcion_accidente = $request->descripcion_accidente;
            $conductor->foto = $request->foto;
            $conductor->fk_proveedor = $request->fk_proveedor;
            $conductor->usuario_id = $request->usuario_id;
            $conductor->fk_estado = 46; //POR INGRESAR
            $conductor->licencia_conduccion_pdf = $request->licencia_conduccion_pdf;
            $conductor->seguridad_social_pdf = $request->seguridad_social_pdf;
            $conductor->numero_documento_pdf = $request->numero_documento_pdf;
            $conductor->examenes_pdf = $request->examenes_pdf;

            if ($request->hasFile('foto')){

                $file_pdf = $request->file('foto');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/conductores/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $conductor->foto = $ubicacion_pdf.$name_pdf;
    
            }

            if ($request->hasFile('licencia_conduccion_pdf')){

                $file_pdf = $request->file('licencia_conduccion_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/conductores/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $conductor->licencia_conduccion_pdf = $ubicacion_pdf.$name_pdf;
    
            }

            if ($request->hasFile('seguridad_social_pdf')){

                $file_pdf = $request->file('seguridad_social_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/conductores/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $conductor->seguridad_social_pdf = $ubicacion_pdf.$name_pdf;
    
            }

            if ($request->hasFile('numero_documento_pdf')){

                $file_pdf = $request->file('numero_documento_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/conductores/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $conductor->numero_documento_pdf = $ubicacion_pdf.$name_pdf;
    
            }

            if ($request->hasFile('examenes_pdf')){

                $file_pdf = $request->file('examenes_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
    
                $ubicacion_pdf = 'images/prov_nuevos/conductores/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $conductor->examenes_pdf = $ubicacion_pdf.$name_pdf;
    
            }

            $conductor->save();

            return Response::json([
                'response' => true
            ]);

        }

    }

    public function inscribevehicle(Request $request) {

        $query = "SELECT id, placa FROM `vehiculos` WHERE placa = '".$request->placa."'";
        $consulta = DB::select($query);

        if($consulta) {

            return Response::json([
                'response' => false,
                'message' => 'La placa ingresada ya se encuentra registrada en el sistema.',
                'consulta' => $consulta
            ]);

        }else{

            $vehiculo = new Vehiculo;
            $vehiculo->placa = $request->placa;
            $vehiculo->numero_motor = $request->numero_motor;
            $vehiculo->fk_tipo_vehiculo = $request->fk_tipo_vehiculo;
            $vehiculo->marca = $request->marca;
            $vehiculo->modelo = $request->modelo;
            $vehiculo->ano = $request->ano;
            $vehiculo->capacidad = $request->capacidad;
            $vehiculo->color = $request->color;
            $vehiculo->empresa_afiliada = $request->empresa_afiliada;
            $vehiculo->tarjeta_operacion = $request->tarjeta_operacion;
            $vehiculo->fecha_vigencia_operacion = $request->fecha_vigencia_operacion;
            $vehiculo->fecha_vigencia_tecnomecanica = $request->fecha_vigencia_tecnomecanica;
            $vehiculo->mantenimiento_preventivo = $request->mantenimiento_preventivo;
            $vehiculo->poliza_todo_riesgo = $request->poliza_todo_riesgo;
            $vehiculo->poliza_contractual = $request->poliza_contractual;
            $vehiculo->poliza_extracontractual = $request->poliza_extracontractual;
            $vehiculo->fk_proveedor = $request->fk_proveedor;
            $vehiculo->numero_interno = $request->numero_interno;
            $vehiculo->fk_estado = 46;
            $vehiculo->numero_vin = $request->numero_vin;
            $vehiculo->cilindraje = $request->cilindraje;
            $vehiculo->tarjeta_operacion_pdf = $request->tarjeta_operacion_pdf;
            $vehiculo->soat_pdf = $request->soat_pdf;
            $vehiculo->tecnomecanica_pdf = $request->tecnomecanica_pdf;
            $vehiculo->preventivo_pdf = $request->preventivo_pdf;
            $vehiculo->poliza_contractual_pdf = $request->poliza_contractual_pdf;
            $vehiculo->poliza_extracontractual_pdf = $request->poliza_extracontractual_pdf;
            $vehiculo->fkv_conductor = $request->fkv_conductor;

            if ($request->hasFile('tarjeta_operacion_pdf')){
                $file_pdf = $request->file('tarjeta_operacion_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
                $ubicacion_pdf = 'images/prov_nuevos/vehiculos/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $vehiculo->tarjeta_operacion_pdf = $ubicacion_pdf.$name_pdf;
            }

            if ($request->hasFile('soat_pdf')){
                $file_pdf = $request->file('soat_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
                $ubicacion_pdf = 'images/prov_nuevos/vehiculos/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $vehiculo->soat_pdf = $ubicacion_pdf.$name_pdf;
            }

            if ($request->hasFile('tecnomecanica_pdf')){
                $file_pdf = $request->file('tecnomecanica_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
                $ubicacion_pdf = 'images/prov_nuevos/vehiculos/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $vehiculo->tecnomecanica_pdf = $ubicacion_pdf.$name_pdf;
            }

            if ($request->hasFile('preventivo_pdf')){
                $file_pdf = $request->file('preventivo_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
                $ubicacion_pdf = 'images/prov_nuevos/vehiculos/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $vehiculo->preventivo_pdf = $ubicacion_pdf.$name_pdf;
            }

            if ($request->hasFile('poliza_contractual_pdf')){
                $file_pdf = $request->file('poliza_contractual_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
                $ubicacion_pdf = 'images/prov_nuevos/vehiculos/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $vehiculo->poliza_contractual_pdf = $ubicacion_pdf.$name_pdf;
            }

            if ($request->hasFile('poliza_extracontractual_pdf')){
                $file_pdf = $request->file('poliza_extracontractual_pdf');
                $name_pdf = str_replace(' ', '', $file_pdf->getClientOriginalName());
                $ubicacion_pdf = 'images/prov_nuevos/vehiculos/';
                $file_pdf->move($ubicacion_pdf, $name_pdf);
                $vehiculo->poliza_extracontractual_pdf = $ubicacion_pdf.$name_pdf;
            }

            $vehiculo->save();

            return Response::json([
                'response' => true
            ]);

        }

    }

    public function updatedatedocument(Request $request) { //Pending

        if($request->tipo=='veh') {
            
            if($request->name=='fecha_vigencia_operacion') {
                
                $vehiculo = DB::table('vehiculos')
                ->where('id',$request->id)
                ->update([
                    'fecha_vigencia_operacion' => $request->fecha
                ]);

            }else if($request->name=='fecha_vigencia_soat') {
                
                $vehiculo = DB::table('vehiculos')
                ->where('id',$request->id)
                ->update([
                    'fecha_vigencia_soat' => $request->fecha
                ]);

            }else if($request->name=='fecha_vigencia_tecnomecanica') {
                
                $vehiculo = DB::table('vehiculos')
                ->where('id',$request->id)
                ->update([
                    'fecha_vigencia_tecnomecanica' => $request->fecha
                ]);

            }else if($request->name=='mantenimiento_preventivo') {
                
                $vehiculo = DB::table('vehiculos')
                ->where('id',$request->id)
                ->update([
                    'mantenimiento_preventivo' => $request->fecha
                ]);

            }else if($request->name=='poliza_contractual') {
                
                $vehiculo = DB::table('vehiculos')
                ->where('id',$request->id)
                ->update([
                    'poliza_contractual' => $request->fecha
                ]);

            }else if($request->name=='poliza_extracontractual') {
                
                $vehiculo = DB::table('vehiculos')
                ->where('id',$request->id)
                ->update([
                    'poliza_extracontractual' => $request->fecha
                ]);

            }

        }else if($request->tipo=='con') {

            if($request->name=='fecha_licencia_vigencia') {
                
                $conductor = DB::table('conductores')
                ->where('id',$request->id)
                ->update([
                    'fecha_licencia_vigencia' => $request->fecha
                ]);
                
            }else if($request->name=='seguridad_social') { //pending
    
            }else if($request->name=='examenes_vigencia') {
                
                $conductor = DB::table('conductores')
                ->where('id',$request->id)
                ->update([
                    'examenes_vigencia' => $request->fecha
                ]);

            }

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function createdriver(Request $request) {

        $conductor =  new Conductor;
        $conductor->fecha_vinculacion = date('Y-m-d');
        $conductor->primer_nombre = $request->primer_nombre;
        $conductor->segundo_nombre = $request->segundo_nombre;
        $conductor->primer_apellido = $request->primer_apellido;
        $conductor->segundo_apellido = $request->segundo_apellido;
        $conductor->fecha_de_nacimiento = $request->fecha_de_nacimiento;
        $conductor->fk_departamento = $request->fk_departamento;
        $conductor->fk_ciudad = $request->fk_ciudad;
        $conductor->fk_tipo_documento = $request->fk_tipo_documento;
        $conductor->numero_documento = $request->numero_documento;
        $conductor->celular = $request->celular;
        $conductor->direccion = $request->direccion;
        $conductor->fk_tipo_licencia = $request->fk_tipo_licencia;
        $conductor->fecha_licencia_expedicion = $request->fecha_licencia_expedicion;
        $conductor->fecha_licencia_vigencia = $request->fecha_licencia_vigencia;
        $conductor->fk_genero = $request->fk_genero;
        $conductor->experiencia = $request->experiencia;
        $conductor->accidentes = $request->accidentes;
        $conductor->descripcion_accidente = $request->descripcion_accidente;
        $conductor->fk_proveedor = $request->fk_proveedor;
        $conductor->fk_estado = 50;
        $conductor->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function listdrivers(Request $request) {

        $conductores = DB::table('conductores')->get();

        return Response::json([
            'response' => true,
            'conductores' => $conductores
        ]);

    }

    public function listvehicles(Request $request) {

        $vehiculos = DB::table('vehiculos')->get();

        return Response::json([
            'response' => true,
            'vehiculos' => $vehiculos
        ]);

    }

    public function createvehicle(Request $request) {
        
        $vehiculo = new Vehiculo;
        $vehiculo->placa = $vehiculos[$i]->placa;
        $vehiculo->numero_motor = $vehiculos[$i]->numero_motor;
        $vehiculo->fk_tipo_vehiculo = $vehiculos[$i]->fk_tipo_vehiculo;
        $vehiculo->marca = $vehiculos[$i]->marca;
        $vehiculo->modelo = $vehiculos[$i]->modelo;
        $vehiculo->ano = $vehiculos[$i]->ano;
        $vehiculo->capacidad = $vehiculos[$i]->capacidad;
        $vehiculo->color = $vehiculos[$i]->color;
        $vehiculo->empresa_afiliada = $vehiculos[$i]->empresa_afiliada;
        $vehiculo->tarjeta_operacion = $vehiculos[$i]->tarjeta_operacion;
        $vehiculo->fecha_vigencia_operacion = $vehiculos[$i]->fecha_vigencia_operacion;
        $vehiculo->fecha_vigencia_soat = $vehiculos[$i]->fecha_vigencia_soat;
        $vehiculo->fecha_vigencia_tecnomecanica = $vehiculos[$i]->fecha_vigencia_tecnomecanica;
        $vehiculo->mantenimiento_preventivo = $vehiculos[$i]->mantenimiento_preventivo;
        $vehiculo->poliza_todo_riesgo = $vehiculos[$i]->poliza_contractual;
        $vehiculo->poliza_contractual = $vehiculos[$i]->poliza_contractual;
        $vehiculo->poliza_extracontractual = $vehiculos[$i]->poliza_extracontractual;
        $vehiculo->fk_proveedor = $request->fk_proveedor;
        $vehiculo->numero_interno = $vehiculos[$i]->numero_interno;
        $vehiculo->fk_estado = 50;
        $vehiculo->numero_vin = $vehiculos[$i]->numero_vin;
        $vehiculo->cilindraje = $vehiculos[$i]->cilindraje;
        $vehiculo->save();

        return Response::json([
            'response' => true
        ]);
        
    }

}
