<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CostcenterController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CotizacionesController;
use App\Http\Controllers\ProveedoresController;
use App\Http\Controllers\ConductoresController;
use App\Http\Controllers\VehiculosController;
use App\Http\Controllers\ViajesController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\ContabilidadController;
use App\Models\Cotizacion;
use App\Models\GestionesCotizacion;
use App\Models\GestionesPortafolio;
use App\Models\Portafolio;
use App\Models\Centrosdecosto;
use App\Models\Traslado;
use App\Models\Tarifa;
use App\Models\Conductor;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', function (Request $request) {

    $usuario = DB::table('conductores')
    ->where('email',$request->email)
    ->first();

    if($usuario==null) {

        return Response::json([
            'response' => false,
            'message' => 'El correo -'.$request->email.'- no se encuentra registrado.'
        ]);

    }else{

        $credentials = $request->validate([
            'email' => [''],
            'password' => [''],
        ]);
        
        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            
            if(1>2) { //baneado$user->baneado==1

                return Response::json([
                    'response' => false,
                    'message' => 'Este usuario está desactivado. Póngase en contacto con el administrador del sistema o con el personal de soporte técnico.'
                ]);

            }else{
        
                $user->tokens()->delete();

                $token = $user->createToken('auth_token')->plainTextToken;

                Auth::logoutOtherDevices($request->password);
                
                $update = DB::table('conductores')
                ->where('id' , $user->id)
                ->update([
                    'last_login' => date('Y-m-d H:i')
                ]);
                
                $conductor = Conductor::find($user->id);

                return Response::json([
                    'response' => true,
                    'token' => $token,
                    'acceso' => true,
                    'id_usuario' => Auth::user()->id,
                    'conductor' => $conductor
                ]);

            }
            
        }else{
            
            return Response::json([
                'response' => false,
                'message' => 'Encontramos tu usuario, pero parece que la clave que ingresaste no es correcta. Intenta con tu número de identificación. Si continuas con la restricción de ingreso, comunícate con tu proveedor.'
            ]);

        }
    }
    
});

Route::post('/buscarproveedor', function (Request $request) {

    $identificacion = $request->identificacion;

    $proveedor = DB::table('proveedores')
    ->where('nit', $identificacion)
    ->first();

    if($proveedor!=null){

        //El proveedor está habilitado
        return Response::json([
            'response' => true,
            'proveedor' => $proveedor
        ]);

    }else{

        //El proveedor NO está habilitado
        return Response::json([
            'response' => false
        ]);

    }

});

Route::post('/buscarconductor', function (Request $request) {

    $identificacion = $request->identificacion;
    $proveedor_id = $request->proveedor_id;

    $conductor = DB::table('conductores')
    ->where('fk_proveedor',$proveedor_id)
    ->where('numero_documento', $identificacion)
    ->first();

    if($conductor!=null) { //Conductor en sistema

        if($conductor->email!=null){ //Tiene app creada

            $usuarioApp = $conductor->email;

            return Response::json([
                'response' => 'usuario_con_app',
                'usuario' => $usuarioApp //Se envía usuario para pegar en el campo email, preguntar si quiere iniciar sesión
            ]);

        }else{ //No tiene app creada

            return Response::json([
                'response' => 'usuario_sin_app',
                'conductor_id' => $conductor->id
            ]); //Se envía id y se pregunta si quiere crear su app

        }

    }else{ //Conductor no está en sistema

        return Response::json([
            'response' => false,
            'mensaje' => 'No se encontró ningún usuario con su cédula.<br><br>Valida tu identificación o comunícate con servicio al cliente.'
        ]);

    }

});

Route::post('/crearapp', function (Request $request) {

    $conductor_id = $request->conductor_id;

    $username = $request->username;

    $data = strtolower($username.'@aotour.com.co');

    $consulta = DB::table('conductores')
    ->where('email',$data)
    ->first();

    if($consulta!=null){

        return Response::json([
            'response' => 'existe' //Correo ya tomado por otro usuario
        ]);

    }else{

        $conductor = Conductor::find($conductor_id);
        $conductor->email = $data;
        $conductor->password = $conductor->numero_documento;
        $conductor->save();

        return Response::json([
            'response' => true,
            'email' => $conductor->email,
            'password' => $conductor->numero_documento
        ]);

    }

});

Route::post('/crearconductor', function (Request $request) {

    $driver = DB::table('conductores')
    ->where('email', $request->email)
    ->first();

    if($driver) {

        return Response::json([
            'response' => false,
            'id_conductor' => $driver->id,
            'message' => 'El correo digitado ya está tomado por: '.$driver->primer_nombre.' '.$driver->primer_apellido
        ]);

    }else{

        $driver = DB::table('conductores')
        ->where('numero_documento', $request->cedula)
        ->first();

        if($driver) {

            $conductor = DB::table('conductores')
            ->where('numero_documento', $request->cedula)
            ->update([
                'email' => $request->email,
                'password' => Hash::make($request->cedula)
            ]);

            return Response::json([
                'response' => true,
                'password' => Hash::make($request->cedula)
            ]);

        }else{

            return Response::json([
                'response' => false,
                'message' => 'La cédula ingresada no existe'
            ]);

        }

    }

});

Route::post('auth/consultarproveedor', [AuthController::class, 'consultarproveedor']);
Route::post('auth/consultarconductor', [AuthController::class, 'consultarconductor']);
Route::post('auth/validarcodigo', [AuthController::class, 'validarcodigo']);
Route::post('auth/reestablecercontrasena', [AuthController::class, 'reestablecercontrasena']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/eliminarcuenta', [AuthController::class, 'eliminarcuenta']);

    //Viajes
    Route::post('v1/viajesporentendido', [ViajesController::class, 'viajesporentendido']);
    Route::post('v1/proximosviajes', [ViajesController::class, 'proximosviajes']);
    Route::post('v1/viajeentendido', [ViajesController::class, 'viajeentendido']);
    Route::post('v1/listarpasajeros', [ViajesController::class, 'listarpasajeros']);
    Route::post('v1/listarpasajerosejecutivos', [ViajesController::class, 'listarpasajerosejecutivos']);
    Route::post('v1/escanearqr', [ViajesController::class, 'escanearqr']);
    Route::post('v1/iniciarviaje', [ViajesController::class, 'iniciarviaje']);
    Route::post('v1/viajeactivo', [ViajesController::class, 'viajeactivo']);
    Route::post('v1/usuarioactual', [ViajesController::class, 'usuarioactual']);
    Route::post('v1/esperaejecutivo', [ViajesController::class, 'esperaejecutivo']);
    Route::post('v1/esperaruta', [ViajesController::class, 'esperaruta']);
    Route::post('v1/dejarpasajero', [ViajesController::class, 'dejarpasajero']);
    Route::post('v1/historialdia', [ViajesController::class, 'historialdia']);
    Route::post('v1/historialmes', [ViajesController::class, 'historialmes']);
    Route::post('v1/listarnovedades', [ViajesController::class, 'listarnovedades']);
    Route::post('v1/registrarnovedad', [ViajesController::class, 'registrarnovedad']);
    Route::post('v1/registrarnovedadruta', [ViajesController::class, 'registrarnovedadruta']);
    Route::post('v1/descargarconstancia', [ViajesController::class, 'descargarconstancia']);
    Route::post('v1/pasajerorecogido', [ViajesController::class, 'pasajerorecogido']);
    Route::post('v1/finalizarviaje', [ViajesController::class, 'finalizarviaje']);
    Route::post('v1/obtenerconductor', [ViajesController::class, 'obtenerconductor']);
    Route::post('v1/guardaridregistration', [ViajesController::class, 'guardaridregistration']);
    Route::post('v1/notificaciones', [ViajesController::class, 'notificaciones']);
    Route::post('v1/listarnotificaciones', [ViajesController::class, 'listarnotificaciones']);
    Route::post('v1/tracker', [ViajesController::class, 'tracker']);
    Route::post('v1/gps', [ViajesController::class, 'gps']);
    Route::post('v1/listartiposnovedades', [ViajesController::class, 'listartiposnovedades']);
    Route::post('v1/contactos', [ViajesController::class, 'contactos']);
    Route::post('v1/listarnovedadesejecutivos', [ViajesController::class, 'listarnovedadesejecutivos']);

});