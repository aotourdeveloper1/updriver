<?php

namespace App\Http\Controllers;
use Response;
use App\Models\User;
use Auth;
Use DB;

use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function logout(Request $request) {

        $user = Auth::user();
    
        $user->tokens()->delete();

        return Response::json([
            'response' => true
        ]);

    }

    public function eliminarcuenta(Request $request) {

        $id = $request->conductor_id;

        $user = Conductor::find($id);
        $user->email = null;
        $user->password = null;
        $user->idregistratiodevice = null;

        if ($user->save()) {

            return Response::json([
                'response' => true
            ]);

        }

    }

    public function consultarproveedor(Request $request) {

        $identificacionProveedor = $request->identificacion;

        $proveedor =  DB::table('proveedores')
        ->where('nit', $identificacionProveedor)
        ->where('fk_estado', 50)
        ->first();

        if($proveedor!=null) {

            return Response::json([
                'response' => true,
                'proveedor' => $proveedor //Mostrar nombre en la app (razonsocial)
            ]);

        }else{

            $proveedor =  DB::table('proveedores')
            ->where('nit', $identificacionProveedor)
            ->first();

            if($proveedor!=null) {

                return Response::json([
                    'response' => 'no_habilitado',
                    'mensaje' => 'No es posible continuar con el proceso. Puede ser que el este proveedor no está habilitado en el sistema.<br><br> Comunícate con servicio al cliente.'
                ]);

            }else{

                return Response::json([
                    'respuesta' => false,
                    'mensaje' => 'No se encontró ningún registro con el número de indentificación '.$identificacionProveedor
                ]);

            }

        }

    }

    public function consultarconductor(Request $request) {

        $proveedor_id = $request->proveedor_id;

        $identificacionConductor = $request->identificacion_conductor;

        $conductor = DB::table('conductores')
        ->select('id', 'primer_nombre', 'fk_proveedor', 'numero_documento')
        ->where('fk_proveedor', $proveedor_id)
        ->where('numero_documento',$identificacionConductor)
        ->first();

        if($conductor!=null) {

            if($conductor->email!=null) {
            
                $codigo = '';
                $characters = array_merge(range('0','9'));
                $max = count($characters) - 1;
                for ($i = 0; $i < 6; $i++) {
                    $rand = mt_rand(0, $max);
                    $codigo .= $characters[$rand];
                }

                $numero = $conductor->celular;

                //Envío del código
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
                    \"name\": \"register\",
                    \"language\": {
                        \"code\": \"es\",
                    },
                    \"components\": [{
                        \"type\": \"body\",
                        \"parameters\": [{
                        \"type\": \"text\",
                        \"text\": \"".$codigo."\",
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

                $query = DB::table('conductores')
                ->where('id', $conductor->id)
                ->update([
                    'code' => $codigo
                ]);

                return Response::json([
                    'response' => 'mensaje_enviado',
                    'codigo' => $codigo,
                    'user' => $consultarUsuario,
                    'mensaje' => 'Hemos encontrado tu usuario.',
                    'mensaje_n2' => 'Ingresa el código de 6 dígitos que enviamos a tu celular terminado en '
                ]);

            }else{

                return Response::json([
                    'response' => 'usuario_sin_app',
                    'conductor' => $conductor,
                    'mensaje' => 'Actualmente no dispones de un usuario en nuestra APP.',
                    'mensaje_n2' => '¿Quieres crear tu usuario?'
                ]);

            }

        }else{

            return Response::json([
                'response' => false,
                'mensaje' => 'No se encontró ningún registró con tu número de cédula. <br>Valida la información e inténtalo de nuevo, o comunícate con servicio al cliente.'
            ]);

        }

    }

    public function validarcodigo(Request $request) {

        $conductor_id = $request->conductor_id;
        $code = $request->code;

        $query = DB::table('conductores')
        ->where('id', $conductor_id)
        ->where('code', $code)
        ->first();

        if($query) {

            return Response::json([
                'response' => true,
                'mensaje' => 'El código ingresado es correcto.'
            ]);

        }else{

            return Response::json([
                'response' => 'incorrecto',
                'mensaje' => 'El código ingresado está errado. Valida e inténtalo de nuevo.'
            ]);

        }

    }

    public function reestablecercontrasena(Request $request) {

        $password = $request->password;
        $conductor_id = $request->conductor_id;

        $user = Conductor::find($conductor_id);
        $user->password = Hash::make($password);

        if($user->save()) {

            return Response::json([
                'response' => true,
                'mensaje' => 'Tu contraseña ha sido actualizada de forma exitosa.'
            ]);

        }else{

            return Response::json([
                'response' => false
            ]);

        }

    }

}
