<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Entidad;
use App\Models\UserEntidad;
use App\Models\Perfil;
use App\Models\PerfilAccion;
use App\Models\Proyecto;
use Auth;
use Response;
Use DB;
Use Config;
use Hash;
use Barryvdh\DomPDF\Facade as PDF;

class UsersController extends Controller
{
    public function createuser(Request $request) {

        try{

            $usuario = new User;
            $usuario->fk_tipo_usuario = $request->user_type;
            $usuario->id_perfil = $request->profile;
            $usuario->activated = true;

            if($request->user_type==1) {

                $consulta = "select * from users where fk_tipo_usuario = 1 order by id desc limit 1";

                $ultimo = DB::select($consulta);

                $numero = intval(str_replace('AO','',$ultimo[0]->username))+1;

                $empleado = DB::table('empleados')->where('id',$request->employ)->first();

                $usuario->email = $empleado->correo;
                $usuario->first_name = $empleado->nombres;
                $usuario->last_name = $empleado->apellidos;
                $usuario->username = 'AO'.$numero;
                $usuario->password = Hash::make($empleado->cedula);
                $usuario->id_empleado = $empleado->id;
                $usuario->master = $request->master;

            }else{

                $usuario->email = $request->email;
                $usuario->first_name = $request->first_name;
                $usuario->last_name = $request->last_name;
                $usuario->username = $request->email;
                $usuario->password = Hash::make($request->password);
                $usuario->master = 0;

            }

            $usuario->save();
            
            if ($usuario) {

                for ($i=0; $i <count($request->entity) ; $i++) {
                    $entidad = New UserEntidad;
                    $entidad->fk_user_id = $usuario->id;
                    $entidad->fk_entidad_id = $request->entity[$i];
                    $entidad->estado = true;
                    $entidad->save();
                }

                return Response::json([
                    'response'=>true
                ]);

            }else{

                return Response::json([
                    'response'=>false
                ]);

            }

        }
        catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
        {
            echo 'Login field is required.';
        }
        catch (Cartalyst\Sentry\Users\PasswordRequiredException $e)
        {
            echo 'Password field is required.';
        }
        catch (Cartalyst\Sentry\Users\UserExistsException $e)
        {
            echo 'User with this login already exists.';
        }
        catch (Cartalyst\Sentry\Groups\GroupNotFoundException $e)
        {
            echo 'Group was not found.';
        }

    }

    public function listusers(Request $request) {

        $usuarios = DB::table('users')
        ->leftjoin('tipo_usuario', 'tipo_usuario.id', '=', 'users.fk_tipo_usuario')
        ->select('users.*', 'tipo_usuario.nombre as nombre_tipo_usuario')
        ->get();

        /*$value = "SELECT
        *,
        CONCAT(
            '[',
            GROUP_CONCAT(
                CONCAT(
                    '{id: , ue.fk_entidad_id,', codigo: ', e.codigo, ', estado: ', ue.estado, '}'
                )
                SEPARATOR ','
            ),
            ']'
        ) AS entidades
        FROM
            users u
        LEFT JOIN
            user_entidad ue ON u.id = ue.fk_user_id
            
            left  join entidades e on e.id = ue.fk_entidad_id
        GROUP BY
            u.id, u.username;";

        $consulta = DB::select($value);*/

        return Response::json([
            'response' => true,
            'users' => $usuarios
        ]);

    }

    public function listemploy(Request $request) {

        $sw = $request->valor;

        if($sw==1) {

            $emplados = DB::table('empleados')
            ->leftjoin('users', 'users.id_empleado', '=', 'empleados.id')
            ->select('empleados.*', 'users.email')
            ->get();

        }else{

            $emplados = DB::table('empleados')
            ->leftjoin('users', 'users.id_empleado', '=', 'empleados.id')
            ->select('empleados.*', 'users.email')
            ->whereNull('users.id_empleado')
            ->get();

        }

        return Response::json([
            'response' => true,
            'employ' => $emplados
        ]);

    }

    public function edituser(Request $request) {

        $user_id = $request->user_id; //Auth::user()->id;

        $entidades = $request->entity;

        $user = User::find($request->user_id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->id_perfil = $request->profile;
        if($request->master==1) {
            $user->master = 1;
        }else{
            $user->master = 0;
        }
        $user->save();

        for ($i=0; $i < count($entidades); $i++) {

            $valor = $entidades[$i]['valor'];

            if($valor=='true') {

                $consulta = DB::table('user_entidad')
                ->where('fk_user_id',$user_id)
                ->where('fk_entidad_id',$entidades[$i]['id'])
                ->first();

                if($consulta!=null) {

                    if($consulta->estado!=1) { //Existe actualmente

                        $update = DB::table('user_entidad')
                        ->where('fk_user_id',$user_id)
                        ->where('fk_entidad_id',$entidades[$i]['id'])
                        ->update([
                            'estado' => 1
                        ]);
    
                    }

                }else{

                    $userEntidad = new UserEntidad;
                    $userEntidad->fk_user_id = $user_id;
                    $userEntidad->fk_entidad_id = $entidades[$i]['id'];
                    $userEntidad->estado = 1;
                    $userEntidad->save();
                }

            }else{

                $consulta = DB::table('user_entidad')
                ->where('fk_user_id',$user_id)
                ->where('fk_entidad_id',$entidades[$i]['id'])
                ->first();

                if($consulta) {

                    $update = DB::table('user_entidad')
                    ->where('fk_user_id',$user_id)
                    ->where('fk_entidad_id',$entidades[$i]['id'])
                    ->update([
                        'estado' => 2
                    ]);

                }
            }

        }

        return Response::json([
            'response' => true,
            'user' => $user,
            //'entidades' => $entidades,
            //'values' => $valor
        ]);

    }

    public function changepassword(Request $request) {

        try {

            $contrasena = $request->contrasena;
            $id = $request->id;
            $user = User::find($id);

            $user->password = Hash::make($contrasena);

            if ($user->save()){

                return Response::json([
                'response'=>true
                ]);
            }

        } catch (Exception $e) {

            return Response::json([
                'e'=>$e
            ]);

        }

    }
    
    public function listentity(Request $request) {

        $entidades = DB::table('entidades')
        ->where('estado',1)
        ->get();

        return Response::json([
            'response' => true,
            'entities' => $entidades
        ]);
    }

    public function listentityuser(Request $request) {

        $entidades = DB::table('user_entidad')
        ->leftjoin('users', 'users.id', '=', 'user_entidad.fk_user_id')
        ->leftjoin('entidades', 'entidades.id', '=', 'user_entidad.fk_entidad_id')
        ->select('entidades.id', 'entidades.codigo', 'entidades.nombre', 'entidades.estado')
        ->where('users.id',$request->user_id)
        ->where('user_entidad.estado',1)
        ->get();

        return Response::json([
            'response' => true,
            'entities' => $entidades
        ]);
    }

    public function listtypeuser(Request $request) {

        $tipos = DB::table('tipo_usuario')
        ->where('estado',1)
        ->get();

        return Response::json([
            'response' => true,
            'types' => $tipos
        ]);
    }

    public function createprofile(Request $request) {

        $nombre = $request->nombre;
        $acciones = $request->acciones;

        $perfil = new Perfil;
        $perfil->codigo = substr($request->nombre, 0, 3);
        $perfil->nombre = $request->nombre;
        $perfil->fk_tipo = 1;
        $perfil->activo = 1;
        $perfil->save();

        $accions = DB::table('acciones')->get();

        foreach ($accions as $action) {
            
            $accion = new PerfilAccion;
            $accion->fk_perfil = $perfil->id;
            $accion->fk_accion = $action->id;
            $accion->activo = 0;
            $accion->save();

        }

        for ($i=0; $i < count($acciones); $i++) {
            
            $update = DB::table('perfiles_acciones')
            ->where('fk_perfil', $perfil->id)
            ->where('fk_accion', $acciones[$i])
            ->update([
                'activo' => 1
            ]);

        }
        
        return Response::json([
            'response' => true
        ]);

    }

    public function editprofile(Request $request) {

        $acciones = $request->acciones;

        $update = DB::table('perfiles_acciones')
        ->where('fk_perfil', $request->id)
        ->update([
            'activo' => 0
        ]);

        for ($i=0; $i < count($acciones); $i++) {
            
            $update = DB::table('perfiles_acciones')
            ->where('fk_perfil', $request->id)
            ->where('fk_accion', $acciones[$i])
            ->update([
                'activo' => 1
            ]);

        }

        $channel = 'perfil_'.$request->id;
        $name = 'per'.$request->id;

        $data = json_encode([
            'perfil' => $request->id
        ]);

        Proyecto::notificationPusher($channel, $name, $data);

        return Response::json([
            'response' => true
        ]);

    }

    public function changestatusprofile(Request $request) {

        $actual = DB::table('perfil')
        ->where('id', $request->id)
        ->first();

        if($actual->activo==1) {
            $cambio = 0;
        }else{
            $cambio = 1;
        }

        $update = DB::table('perfil')
        ->where('id', $request->id)
        ->update([
            'activo' => $cambio
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function listprofile(Request $request) {

        $perfiles = DB::table('perfil')
        ->where('activo',1)
        ->get();

        return Response::json([
            'response' => true,
            'profiles' => $perfiles
        ]);
    }

}
