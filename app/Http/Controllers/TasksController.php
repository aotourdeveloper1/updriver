<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Entidad;
use App\Models\UserEntidad;
use App\Models\Grupo;
use App\Models\Proyecto;
use App\Models\SubProyecto;
use App\Models\EvidenciasProyecto;
use App\Models\EvidenciasSubProyecto;
use App\Models\NotificacionesUpnet;
use App\Models\Blog;
use App\Models\BlogUser;
use App\Models\UserProyecto;
use App\Models\UserSubProyecto;
use Auth;
use Response;
Use DB;
Use Config;
use Hash;

class TasksController extends Controller
{
    public function statuslist(Request $request) {

        $estados = DB::table('estados')
        ->join('estados_maestros', 'estados_maestros.id', '=', 'estados.fk_estados_maestros')
        ->select('estados.*', 'estados_maestros.codigo as codigo_estados_maestros', 'estados_maestros.nombre as nombre_estados_maestros')
        ->where('estados_maestros.codigo',$request->codigo)
        ->where('estados.activo',1)
        ->get();

        return Response::json([
            'response' => true,
            'estados' => $estados
        ]);

    }

    public function listresponsible(Request $request) {

        $responsable = DB::table('users')
        ->join('tipo_usuario', 'tipo_usuario.id', '=', 'users.fk_tipo_usuario')
        ->select('users.*', 'tipo_usuario.codigo')
        ->where('tipo_usuario.codigo','EMPL')
        ->whereNull('users.baneado')
        ->get();

        return Response::json([
            'response' => true,
            'responsable' => $responsable
        ]);

    }

    public function creategroup(Request $request) {

        $know = "select * from grupos order by orden asc";
        $mysql = DB::select($know);
        $total = count($mysql);

        $grupo = new Grupo;
        $grupo->nombre_grupo = $request->nombre_grupo;
        $grupo->fk_creado_por_users = $request->user_id;
        $grupo->orden = intval($total)+1;
        $grupo->save();

        /*$update = DB::table('grupos')
        ->where('id',$grupo->id)
        ->update([
            'orden' => intval($grupo->id)
        ]);*/

        return Response::json([
            'response' => true,
            'total' => $total
        ]);

    }
    //Customisar para consultar por usuario o hacer una adicional para listar por usuario
    public function listgroups(Request $request) {

        $grupos = DB::table('grupos')
        ->where('activo',1)
        ->get();

        return Response::json([
            'response' => true,
            'grupos' => $grupos
        ]);

    }

    public function listgroupsuser(Request $request) {

        $grupos = DB::table('grupos')
        ->where('activo',1)
        ->where('fk_creado_por_users',Auth::user()->id)
        ->get();

        return Response::json([
            'response' => true,
            'grupos' => $grupos
        ]);

    }

    public function createproject(Request $request) {

        $know = "select * from proyectos order by orden asc";
        $mysql = DB::select($know);
        $total = count($mysql);

        $proyecto = new Proyecto;
        $proyecto->fk_responsable = $request->fk_responsable;
        $proyecto->fecha_inicial = $request->fecha_inicial;
        $proyecto->fecha_final = $request->fecha_final;
        $proyecto->proyecto = $request->proyecto;
        $proyecto->fk_prioridad = $request->fk_prioridad;
        $proyecto->fk_estado = $request->fk_estado;
        $proyecto->nota = $request->nota;
        $proyecto->orden = intval($total)+1;
        $proyecto->fk_asignado_por = $request->fk_asignado_por;
        $proyecto->fk_grupos = $request->fk_grupos;
        
        if($proyecto->save()){
            
            if($request->fk_asignado_por!=$request->fk_responsable) {

                $asignador = Auth::user()->first_name;
                $tarea = $request->proyecto;
                $asunto = 'Tarea Asignada';
                $cuerpo = ''.strtoupper($asignador).' te ha asignado la tarea '.strtoupper($tarea).'';

                Proyecto::saveNotification($asunto, $cuerpo, $request->fk_responsable, 11);

                $channel = 'notificaciones_'.$request->fk_responsable;
                $name = 'not'.$request->fk_responsable;

                $data = json_encode([
                    'asunto' => $asunto,
                    'cuerpo' => $cuerpo,
                ]);

                Proyecto::notificationPusher($channel, $name, $data);

            }

            $blog = new Blog;
            $blog->nombre = $request->proyecto;
            $blog->estado = 1;
            $blog->fk_proyecto = $proyecto->id;
            $blog->save();

            if($request->copy!=null) {

                for ($i=0; $i < count($request->copy); $i++) {

                    $copia = new UserProyecto;
                    $copia->fk_user = $request->copy[$i];
                    $copia->fk_proyecto = $proyecto->id;
                    $copia->save();

                    if($request->copy[$i]!=Auth::user()->id) {

                        $asignador = User::find($request->fk_asignado_por);
                        $tarea = $proyecto->proyecto;
                        $asunto = 'Tarea Compartida';
                        $cuerpo = ''.strtoupper($asignador->first_name).' te ha agregado como copia en la tarea '.strtoupper($tarea).'';

                        Proyecto::saveNotification($asunto, $cuerpo, $request->copy[$i], 11);

                        $channel = 'notificaciones_'.$request->copy[$i];
                        $name = 'not'.$request->copy[$i];

                        $data = json_encode([
                            'asunto' => $asunto,
                            'cuerpo' => $cuerpo,
                        ]);

                        Proyecto::notificationPusher($channel, $name, $data);

                    }

                }

            }

            return Response::json([
                'response' => true
            ]);
        }

    }
    //Customisar para consultar por usuario o hacer una adicional para listar por usuario
    public function listprojects(Request $request) {

        $proyecto = "select  count(ep.fk_proyecto) as cantidad_evidencia, count(sp.fk_proyectos) as cantidad_subtareas,
		a.*, e.first_name, 
		e.last_name, bg.id as blog_id, bg.nombre as blog_name,
        CASE
        when a.fk_estado = 2 then 'LISTO'
        when a.fk_estado = 3 then 'EN CURSO'
        when a.fk_estado = 4 then 'DETENIDO'
        when a.fk_estado = 5 then 'NO INICIADO'
        when a.fk_estado = 9 then 'PENDIENTE'
        else 'SIN ESTADO'
        END
        AS 'estado',
        case 
        when a.fk_prioridad = 6 then 'ALTA'
        when a.fk_prioridad = 7 then 'MEDIA'
        when a.fk_prioridad = 8 then 'BAJA'
        else 'SIN PRIORIDAD'
        END
        AS 'prioridad'
        from proyectos a
        left join users e on a.fk_responsable =e.id
        left join blog bg on bg.fk_proyecto = a.id
        left join evidencias_proyecto ep on ep.fk_proyecto = a.id
        left join sub_proyectos sp on sp.fk_proyectos = a.id where a.fk_estado <> 2 group by proyecto";

        $proyectos = DB::select($proyecto);

        return Response::json([
            'response' => true,
            'proyectos' => $proyectos
        ]);

    }
    //Mandar en una key cuantas subtareas tiene esa tarea

    public function listprojectsuser(Request $request) {

        /*$proyecto = "select  count(ep.fk_proyecto) as cantidad_evidencia, count(sp.fk_proyectos) as cantidad_subtareas,
		a.*, e.first_name, 
		e.last_name, bg.id as blog_id, bg.nombre as blog_name,
        CASE
        when a.fk_estado = 2 then 'LISTO'
        when a.fk_estado = 3 then 'EN CURSO'
        when a.fk_estado = 4 then 'DETENIDO'
        when a.fk_estado = 5 then 'NO INICIADO'
        when a.fk_estado = 9 then 'PENDIENTE'
        else 'SIN ESTADO'
        END
        AS 'estado',
        case 
        when a.fk_prioridad = 6 then 'ALTA'
        when a.fk_prioridad = 7 then 'MEDIA'
        when a.fk_prioridad = 8 then 'BAJA'
        else 'SIN PRIORIDAD'
        END
        AS 'prioridad'
        from proyectos a
        left join users e on a.fk_responsable =e.id
        left join blog bg on bg.fk_proyecto = a.id
        left join evidencias_proyecto ep on ep.fk_proyecto = a.id 
        left join sub_proyectos sp on sp.fk_proyectos = a.id WHERE a.fk_responsable = ".Auth::user()->id." group by proyecto";*/

        $proyecto = "select
        count(ep.fk_proyecto) as cantidad_evidencia,
        count(sp.fk_proyectos) as cantidad_subtareas,
            a.*,
        e.first_name, 
            e.last_name,
        bg.id as blog_id,
        bg.nombre as blog_name,
        CASE
            when a.fk_estado = 2 then 'LISTO'
            when a.fk_estado = 3 then 'EN CURSO'
            when a.fk_estado = 4 then 'DETENIDO'
            when a.fk_estado = 5 then 'NO INICIADO'
            when a.fk_estado = 9 then 'PENDIENTE'
            else 'SIN ESTADO'
        END
            AS 'estado',
        case
            when a.fk_prioridad = 6 then 'ALTA'
            when a.fk_prioridad = 7 then 'MEDIA'
            when a.fk_prioridad = 8 then 'BAJA'
            else 'SIN PRIORIDAD'
        END
            AS 'prioridad'
    from
        proyectos a
    left join users e on
        a.fk_responsable = e.id
    left join blog bg on
        bg.fk_proyecto = a.id
    left join sub_proyectos sp2 on
        sp2.fk_proyectos = a.id
    left join evidencias_proyecto ep on
        ep.fk_proyecto = a.id
    left join sub_proyectos sp on
        sp.fk_proyectos = a.id
    WHERE
        (a.fk_responsable = ".Auth::user()->id."
            OR sp2.fk_responsable = ".Auth::user()->id.")
        AND (a.fecha_inicial BETWEEN '".$request->fecha_inicial."' AND '".$request->fecha_final."'
        OR a.fecha_final BETWEEN '".$request->fecha_inicial."' AND '".$request->fecha_final."')
    GROUP BY a.proyecto";

        $proyectos = DB::select($proyecto);
        
        return Response::json([
            'response' => true,
            'proyectos' => $proyectos
        ]);

    }

    public function createsubproject(Request $request) {

        $know = "select * from sub_proyectos order by orden asc";
        $mysql = DB::select($know);
        $total = count($mysql);

        $proyecto = new SubProyecto;
        $proyecto->fk_responsable = $request->fk_responsable;
        $proyecto->fecha_inicial = $request->fecha_inicial;
        $proyecto->fecha_final = $request->fecha_final;
        $proyecto->proyecto = $request->proyecto;
        $proyecto->fk_prioridad = $request->fk_prioridad;
        $proyecto->fk_estado = $request->fk_estado;
        $proyecto->nota = $request->nota;
        $proyecto->orden = intval($total)+1;
        $proyecto->fk_asignado_por = $request->fk_asignado_por;
        $proyecto->fk_proyectos = $request->fk_proyectos;
        
        if($proyecto->save()) {

            $blog = new Blog;
            $blog->nombre = $request->proyecto;
            $blog->estado = 1;
            $blog->fk_sub_proyecto = $proyecto->id;
            $blog->save();

            if($request->copy!=null) {

                for ($i=0; $i < count($request->copy); $i++) {

                    $copia = new UserSubProyecto;
                    $copia->fk_user = $request->copy[$i];
                    $copia->fk_sub_proyecto = $proyecto->id;
                    $copia->save();

                    if($request->copy[$i]!=Auth::user()->id) {

                        $asignador = User::find($request->fk_asignado_por);
                        $tarea = $proyecto->proyecto;
                        $asunto = 'Sub Tarea Compartida';
                        $cuerpo = ''.strtoupper($asignador->first_name).' te ha agregado como copia en la tarea '.strtoupper($tarea).'';

                        //Proyecto::saveNotification($asunto, $cuerpo, $request->copy[$i], 11);

                        $channel = 'notificaciones_'.$request->copy[$i];
                        $name = 'not'.$request->copy[$i];

                        $data = json_encode([
                            'asunto' => $asunto,
                            'cuerpo' => $cuerpo,
                        ]);

                        Proyecto::notificationPusher($channel, $name, $data);

                    }

                }

            }
        }
        
        return Response::json([
            'response' => true
        ]);

    }
    //Customisar para consultar por usuario o hacer una adicional para listar por usuario
    public function listsubprojects(Request $request) {

        //no incluye el blog de mensajes ni sus campos de la tabla

        //bg.id as blog_id, bg.nombre as blog_name,

        $sub_proyecto = "select  count(ep.fk_sub_proyecto) as cantidad_evidencia,
		a.*, e.first_name, 
		e.last_name, bg.id as blog_id, bg.nombre as blog_name,
        CASE
        when a.fk_estado = 2 then 'LISTO'
        when a.fk_estado = 3 then 'EN CURSO'
        when a.fk_estado = 4 then 'DETENIDO'
        when a.fk_estado = 5 then 'NO INICIADO'
        when a.fk_estado = 9 then 'PENDIENTE'
        else 'SIN ESTADO'
        END
        AS 'estado',
        case 
        when a.fk_prioridad = 6 then 'ALTA'
        when a.fk_prioridad = 7 then 'MEDIA'
        when a.fk_prioridad = 8 then 'BAJA'
        else 'SIN PRIORIDAD'
        END
        AS 'prioridad'
        from sub_proyectos a
        left join users e on a.fk_responsable =e.id
        left join blog bg on bg.fk_sub_proyecto = a.id
        left join sub_evidencias_proyecto ep on ep.fk_sub_proyecto = a.id group by proyecto";

        //left join blog bg on bg.fk_proyecto = a.id

        $sub_proyectos = DB::select($sub_proyecto);

        return Response::json([
            'response' => true,
            'sub_proyectos' => $sub_proyectos
        ]);

    }

    /*Evidencias Proyectos*/
    public function createevidenceproject(Request $request) {

        $evidencia = new EvidenciasProyecto;
        $evidencia->tipo_archivo = $request->tipo_archivo;
        $evidencia->fk_usuario_subida = $request->fk_usuario_subida;
        $evidencia->url_archivo = 'evidencia';
        $evidencia->fecha_subida = $request->fecha_subida;
        $evidencia->fk_proyecto = $request->fk_proyecto;
        $evidencia->save();

        if($request->hasFile('archivo')){

            $file = $request->file('archivo');
            $name_file = str_replace(' ', '', $file->getClientOriginalName());
            
            $numero = $evidencia->id;

            $ubicacion_pdf = 'images/soportes_tareas/';
            $file->move($ubicacion_pdf, $numero.$name_file);
            $ubicacion_archivo = $numero.$ubicacion_pdf;

            $update = DB::table('evidencias_proyecto')
            ->where('id',$evidencia->id)
            ->update([
                'url_archivo' => $numero.$name_file
            ]);

            $copias = DB::table('user_proyectos')
            ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
            ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
            ->where('user_proyectos.fk_proyecto',$request->fk_proyecto)
            ->where('users.id', '!=', Auth::user()->id)
            ->get();

            if($copias!=null) {

                $project = DB::table('proyectos')->where('id',$request->fk_proyecto)->first();

                foreach ($copias as $user) {
                    
                    if(Auth::user()->id!=$user->id) {
                        
                        $channel = 'notificaciones_'.$user->id_usuario;
                        $name = 'not'.$user->id_usuario;
                        
                        $asunto = 'Actualización de Tarea';
            
                        $data = json_encode([
                            'asunto' => $asunto,
                            'cuerpo' => 'La tarea '.$project->proyecto.' tiene una nueva evidencia subida por '.Auth::user()->first_name.'.'
                        ]);
            
                        Proyecto::notificationPusher($channel, $name, $data);

                    }
        
                }

            }
  
        }else{
            $ubicacion_archivo = null;
        }

        return Response::json([
            'response' => true
        ]);

    }

    public function listevidenceproject(Request $request) {

        $evidencias_proyecto = DB::table('evidencias_proyecto')
        ->where('fk_proyecto', $request->project_id)
        ->get();

        return Response::json([
            'response' => true,
            'evidencias_proyecto' => $evidencias_proyecto
        ]);

    }

    /*Evidencias Sub Proyectos*/
    public function createevidencesubproject(Request $request) {

        $evidencia = new EvidenciasSubProyecto;
        $evidencia->tipo_archivo = $request->tipo_archivo; //ok
        $evidencia->fk_usuario_subida = $request->fk_usuario_subida; //ok
        $evidencia->url_archivo = 'evidencia'; //ok
        $evidencia->fecha_subida = $request->fecha_subida; //ok
        $evidencia->fk_sub_proyecto = $request->fk_sub_proyecto; //ok
        $evidencia->save();

        if($request->hasFile('archivo')){

            $file = $request->file('archivo');
            $name_file = str_replace(' ', '', $file->getClientOriginalName());
            
            $numero = $evidencia->id;

            $ubicacion_pdf = 'images/soportes_sub_tareas/';
            $file->move($ubicacion_pdf, $numero.$name_file);
            $ubicacion_archivo = $numero.$ubicacion_pdf;

            $update = DB::table('sub_evidencias_proyecto')
            ->where('id',$evidencia->id)
            ->update([
                'url_archivo' => $numero.$name_file
            ]);

        }
        //Guardar archivo en el servidor - PENDING

        return Response::json([
            'response' => true
        ]);

    }

    public function listevidencesubproject(Request $request) {

        $evidencias_sub_proyecto = DB::table('sub_evidencias_proyecto')
        ->where('fk_sub_proyecto', $request->project_id)
        ->get();

        return Response::json([
            'response' => true,
            'evidencias_proyecto' => $evidencias_sub_proyecto
        ]);

    }

    public function editpriority(Request $request) {

        $project_id = $request->project_id;
        $new_priority = $request->new_priority;

        $copias = DB::table('user_proyectos')
        ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
        ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
        ->where('user_proyectos.fk_proyecto',$project_id)
        ->where('users.id', '!=', Auth::user()->id)
        ->get();

        $nuevoEstado = '...';

        if($new_priority==6) {
            $nuevoEstado = 'ALTA';
        }else if($new_priority==7) {
            $nuevoEstado = 'MEDIA';
        }else if($new_priority==8) {
            $nuevoEstado = 'BAJA';
        }
        if($copias!=null) {

            $project = DB::table('proyectos')->where('id',$project_id)->first();

            foreach ($copias as $user) {
                
                if(Auth::user()->id!=$user->id) {
                    
                    $channel = 'notificaciones_'.$user->id_usuario;
                    $name = 'not'.$user->id_usuario;
                    
                    $asunto = 'Actualización de Tarea';
        
                    $data = json_encode([
                        'asunto' => $asunto,
                        'cuerpo' => 'La tarea '.$project->proyecto.' fue modificada de PRIORIDAD a '.$nuevoEstado.'.'
                    ]);
        
                    Proyecto::notificationPusher($channel, $name, $data);

                }
    
            }

        }

        $update = DB::table('proyectos')
        ->where('id',$project_id)
        ->update([
            'fk_prioridad' => $new_priority
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function editprioritysub(Request $request) {

        $sub_project_id = $request->project_id;
        $new_priority = $request->new_priority;

        $update = DB::table('sub_proyectos')
        ->where('id',$sub_project_id)
        ->update([
            'fk_prioridad' => $new_priority
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function editstatus(Request $request) {

        $project_id = $request->project_id;
        $new_status = $request->new_status;
        $old_status = $request->old_status;

        $project = DB::table('proyectos')->where('id',$project_id)->first();

        if($new_status==9 or $new_status==2 or $new_status==3) {

            if($new_status==9 and Auth::user()->master!=1){

                $masters = DB::table('users')
                ->where('master',1)
                ->get();

                $update = DB::table('proyectos')
                ->where('id',$project_id)
                ->update([
                    'fecha_terminado' => date('Y-m-d H:i')
                ]);

                foreach ($masters as $user) {
                    
                    $responsable = DB::table('users')
                    ->where('id',$project->fk_responsable)
                    ->first();

                    $asunto = 'Tarea Pendiente por Aprobar';
                    $cuerpo = 'Tienes una nueva tarea finalizada de '.$responsable->first_name.' pendiente por aprobar';
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

                $copias = DB::table('user_proyectos')
                ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
                ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
                ->where('user_proyectos.fk_proyecto',$project_id)
                ->where('users.id', '!=', Auth::user()->id)
                ->where('users.master',0)
                ->get();

                if($copias!=null) { //Si tiene copias la tarea

                    foreach ($copias as $user) { //Envío a las copias de la tarea
                        
                        if(Auth::user()->id!=$user->id) {
                            
                            $channel = 'notificaciones_'.$user->id_usuario;
                            $name = 'not'.$user->id_usuario;
                            
                            $asunto = 'Actualización de Tarea ';
                
                            $data = json_encode([
                                'asunto' => $asunto,
                                'cuerpo' => 'La tarea '.$project->proyecto.' de '.$responsable->first_name.' fue enviada a aprobación.'
                
                            ]);
                
                            Proyecto::notificationPusher($channel, $name, $data);

                        }
            
                    }

                }

            }else if($new_status==2){

                $aceptador = Auth::user()->first_name.' '.Auth::user()->last_name;
                $tarea = $project->proyecto;
                $asunto = 'Tarea Aprobada';
                $cuerpo = 'Tu tarea '.$tarea.' fue aprobada por '.$aceptador;
                $usuario = $project->fk_responsable;

                Proyecto::saveNotification($asunto, $cuerpo, $usuario, 11);

                $channel = 'notificaciones_'.$usuario;
                $name = 'not'.$usuario;

                $data = json_encode([
                    'asunto' => $asunto,
                    'cuerpo' => $cuerpo,
                ]);

                Proyecto::notificationPusher($channel, $name, $data);

                //Notificar a las copias el cambio de estado a aprobada
                $copias = DB::table('user_proyectos')
                ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
                ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
                ->where('user_proyectos.fk_proyecto',$project_id)
                ->where('users.id', '!=', Auth::user()->id)
                ->get();

                if($copias!=null) { //Si tiene copias la tarea

                    foreach ($copias as $user) { //Envío a las copias de la tarea
                        
                        if(Auth::user()->id!=$user->id) {
                            
                            $channel = 'notificaciones_'.$user->id_usuario;
                            $name = 'not'.$user->id_usuario;
                            
                            $asunto = 'Actualización de Tarea ';
                
                            $data = json_encode([
                                'asunto' => $asunto,
                                'cuerpo' => 'La tarea '.$project->proyecto.' FUE APROBADA.'
                
                            ]);
                
                            Proyecto::notificationPusher($channel, $name, $data);

                        }
            
                    }

                }

            }else if($new_status==3 and $old_status==9){

                $aceptador = Auth::user()->first_name.' '.Auth::user()->last_name;
                $tarea = $project->proyecto;
                $asunto = 'Tarea no Aprobada';
                $cuerpo = 'Tu tarea '.$tarea.' no fue aprobada por '.$aceptador;
                $usuario = $project->fk_responsable;

                Proyecto::saveNotification($asunto, $cuerpo, $usuario, 11);

                $channel = 'notificaciones_'.$usuario;
                $name = 'not'.$usuario;

                $data = json_encode([
                    'asunto' => $asunto,
                    'cuerpo' => $cuerpo,
                ]);

                Proyecto::notificationPusher($channel, $name, $data);

                //Notificar a las copias el cambio de estado a no aprobada
                $copias = DB::table('user_proyectos')
                ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
                ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
                ->where('user_proyectos.fk_proyecto',$project_id)
                ->where('users.id', '!=', Auth::user()->id)
                ->get();

                if($copias!=null) { //Si tiene copias la tarea

                    foreach ($copias as $user) { //Envío a las copias de la tarea
                        
                        if(Auth::user()->id!=$user->id) {
                            
                            $channel = 'notificaciones_'.$user->id_usuario;
                            $name = 'not'.$user->id_usuario;
                            
                            $asunto = 'Actualización de Tarea ';
                
                            $data = json_encode([
                                'asunto' => $asunto,
                                'cuerpo' => 'La tarea '.$project->proyecto.' de NO FUE APROBADA.'
                
                            ]);
                
                            Proyecto::notificationPusher($channel, $name, $data);

                        }
            
                    }

                }

            }else{

                $copias = DB::table('user_proyectos')
                ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
                ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
                ->where('user_proyectos.fk_proyecto',$project_id)
                ->where('users.id', '!=', Auth::user()->id)
                ->get();

                $nuevoEstado = '...';

                if($new_status==3) {
                    $nuevoEstado = 'EN CURSO';
                }else if($new_status==4) {
                    $nuevoEstado = 'DETENIDO';
                }else if($new_status==5) {
                    $nuevoEstado = 'NO INICIADO';
                }
                if($copias!=null) {

                    foreach ($copias as $user) {
                        
                        if(Auth::user()->id!=$user->id) {
                            
                            $channel = 'notificaciones_'.$user->id_usuario;
                            $name = 'not'.$user->id_usuario;
                            
                            $asunto = 'Actualización de Tarea ';
                
                            $data = json_encode([
                                'asunto' => $asunto,
                                'cuerpo' => 'La tarea '.$project->proyecto.' fue modificada de ESTADO a '.$nuevoEstado.'.'
                            ]);
                
                            Proyecto::notificationPusher($channel, $name, $data);

                        }
            
                    }

                }
            }

        }else{

            $copias = DB::table('user_proyectos')
            ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
            ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
            ->where('user_proyectos.fk_proyecto',$project_id)
            ->where('users.id', '!=', Auth::user()->id)
            ->get();

            $nuevoEstado = '...';

            if($new_status==3) {
                $nuevoEstado = 'EN CURSO';
            }else if($new_status==4) {
                $nuevoEstado = 'DETENIDO';
            }else if($new_status==5) {
                $nuevoEstado = 'NO INICIADO';
            }
            if($copias!=null) {

                foreach ($copias as $user) {
                    
                    if(Auth::user()->id!=$user->id) {
                        
                        $channel = 'notificaciones_'.$user->id_usuario;
                        $name = 'not'.$user->id_usuario;
                        
                        $asunto = 'Actualización de Tarea ';
            
                        $data = json_encode([
                            'asunto' => $asunto,
                            'cuerpo' => 'La tarea '.$project->proyecto.' fue modificada de ESTADO a '.$nuevoEstado.'.'
            
                        ]);
            
                        Proyecto::notificationPusher($channel, $name, $data);

                    }
        
                }

            }

        }

        if($new_status==2) {

            $update = DB::table('proyectos')
            ->where('id',$project_id)
            ->update([
                'fk_estado' => $new_status
            ]);

        }else{

            $update = DB::table('proyectos')
            ->where('id',$project_id)
            ->update([
                'fk_estado' => $new_status
            ]);

        }

        return Response::json([
            'response' => true
        ]);

    }

    public function editstatussub(Request $request) {

        $sub_project_id = $request->project_id;
        $new_status = $request->new_status;

        $update = DB::table('sub_proyectos')
        ->where('id',$sub_project_id)
        ->update([
            'fk_estado' => $new_status
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function editresponsible(Request $request) {

        $project_id = $request->project_id;
        $responsible_new = $request->responsible_new;
        $update = DB::table('proyectos')
        ->where('id',$project_id)
        ->update([
            'fk_responsable' => $responsible_new
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function editresponsiblesub(Request $request) {

        $sub_project_id = $request->sub_project_id;
        $responsible_new = $request->responsible_new;
        $update = DB::table('sub_proyectos')
        ->where('id',$sub_project_id)
        ->update([
            'fk_responsable' => $responsible_new
        ]);

        return Response::json([
            'response' => true,
            'responsible_new' => $responsible_new,
            'update' => $update
        ]);

    }

    public function editordengroup(Request $request) {

        $group_id = $request->group_id;
        $orden = DB::table('grupos')->where('id',$group_id)->first();

        $new_position = $request->new_position;

        if($orden->orden<$new_position) { //El orden actual es menor al nuevo
            $consulta = "select id, orden from grupos where orden <= ".$new_position." and orden != ".$orden->orden." order by orden desc";
        }else{
            $consulta = "select id, orden from grupos where orden >= ".$new_position." and orden != ".$orden->orden." order by orden asc";
        }

        $consulta = DB::select($consulta);

        $iterador = intval($new_position);

        foreach ($consulta as $key) {
            
            if($orden->orden<$new_position) { //El orden actual es menor al nuevo
                $iterador--;
            }else{
                $iterador++;
            }

            $update = DB::table('grupos')
            ->where('id',$key->id)
            ->update([
                'orden' => $iterador
            ]);
        }

        $update2 = DB::table('grupos')
        ->where('id',$group_id)
        ->update([
            'orden' => $new_position
        ]);

        return Response::json([
            'response' => true,
            'consulta' => $consulta
        ]);

    }

    public function editordenproject(Request $request) {

        $project_id = $request->project_id;
        $orden = DB::table('proyectos')->where('id',$project_id)->first();

        $new_position = $request->new_position;

        if($orden->orden<$new_position) { //El orden actual es menor al nuevo
            $consulta = "select id, orden from proyectos where orden <= ".$new_position." and orden != ".$orden->orden." order by orden desc";
        }else{
            $consulta = "select id, orden from proyectos where orden >= ".$new_position." and orden != ".$orden->orden."";
        }

        $consulta = DB::select($consulta);

        $iterador = intval($new_position);

        foreach ($consulta as $key) {
            
            if($orden->orden<$new_position) { //El orden actual es menor al nuevo
                $iterador--;
            }else{
                $iterador++;
            }

            $update = DB::table('proyectos')
            ->where('id',$key->id)
            ->update([
                'orden' => $iterador
            ]);
        }

        $update2 = DB::table('proyectos')
        ->where('id',$project_id)
        ->update([
            'orden' => $new_position
        ]);

        return Response::json([
            'response' => true,
            'consulta' => $consulta
        ]);

    }

    public function editordensubproject(Request $request) {

        $sub_project_id = $request->sub_project_id;
        $orden = DB::table('sub_proyectos')->where('id',$sub_project_id)->first();

        $new_position = $request->new_position;

        if($orden->orden<$new_position) {
            $consulta = "select id, orden from sub_proyectos where orden <= ".$new_position." and orden != ".$orden->orden." order by orden desc";
        }else{
            $consulta = "select id, orden from sub_proyectos where orden >= ".$new_position." and orden != ".$orden->orden."";
        }

        $consulta = DB::select($consulta);

        $iterador = intval($new_position);

        foreach ($consulta as $key) {
            
            if($orden->orden<$new_position) {
                $iterador--;
            }else{
                $iterador++;
            }

            $update = DB::table('sub_proyectos')
            ->where('id',$key->id)
            ->update([
                'orden' => $iterador
            ]);
        }

        $update2 = DB::table('sub_proyectos')
        ->where('id',$sub_project_id)
        ->update([
            'orden' => $new_position
        ]);

        return Response::json([
            'response' => true,
            'consulta' => $consulta
        ]);

    }

    public function createnotification(Request $request) {

        $notificacion = new NotificacionesUpnet;
        $notificacion->asunto = $request->asunto;
        $notificacion->cuerpo = $request->cuerpo;
        $notificacion->fk_users = $request->fk_users;
        $notificacion->estado = $request->estado;
        $notificacion->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function readnotification(Request $request) {
        
        $notification = DB::table('notificaciones_upnet')
        ->where('id',$request->notification_id)
        ->update([
            'estado' => $request->estado
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function readnotifications(Request $request) {

        $notification = DB::table('notificaciones_upnet')
        ->where('fk_users',Auth::user()->id)
        ->where('estado',11)
        ->update([
            'estado' => 10
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function listnotifications(Request $request) {

        $notificaciones = DB::table('notificaciones_upnet')
        ->where('fk_users',$request->user_id)
        ->get();

        return Response::json([
            'response' => true,
            'notifications' => $notificaciones
        ]);

    }

    public function deletenotification(Request $request) {

        $notification_id = $request->notification_id;

        $delete = DB::table('notificaciones_upnet')
        ->where('id',$notification_id)
        ->update([
            'estado_eliminacion' => 1
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function deletenotifications(Request $request) {

        $notification = DB::table('notificaciones_upnet')
        ->where('fk_users',Auth::user()->id)
        ->whereNull('estado_eliminacion')
        ->update([
            'estado_eliminacion' => 1
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    //REVISAR O ELIMINAR
    public function createblog(Request $request) {

        $proyecto = Proyecto::find($request->fk_proyecto);

        $blog = new Blog;
        $blog->nombre = $proyecto->proyecto;
        $blog->estado = 1;
        $blog->fk_proyecto = $request->fk_proyecto;
        if($request->fk_sub_proyecto!=null){
            $blog->fk__sub_proyecto = $request->fk_sub_proyecto;
        }
        $blog->save();

        return Response::json([
            'response' => true,
            'proyecto' => $proyecto,
            'blog' => $blog
        ]);
    }

    public function createbloguser(Request $request) {

        $blogUser = new BlogUser;
        $blogUser->fk_user = $request->fk_user;
        $blogUser->fk_blog = $request->fk_blog;
        $blogUser->mensaje = $request->mensaje;
        if($request->reaccion!=null){
            $blogUser->reaccion = $request->reaccion;
        }
        $blogUser->save();

        //Proyecto::saveNotification($asunto, $cuerpo, $request->fk_responsable, 11);

        $blog = Blog::find($request->fk_blog);

        $proyecto = DB::table('proyectos')
        ->where('id',$blog->fk_proyecto)
        ->first();

        $users = DB::table('users')
        ->where('id',$proyecto->fk_responsable)
        ->get();

        if($proyecto->fk_responsable==$request->fk_user) { //Le llega a las copias (si aplica)

            $copias = DB::table('user_proyectos')
            ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
            ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
            ->where('user_proyectos.fk_proyecto',$proyecto->id)
            ->where('users.id', '!=', $request->fk_user)
            ->get();

            if($copias!=null) { //Si tiene copias la tarea

                foreach ($copias as $user) { //Envío a las copias de la tarea
                    
                    if($request->fk_user!=$user->id) { //Si el usuario que envió el mensaje no está en las copias
                        
                        $channel = 'notificaciones_'.$user->id_usuario;
                        $name = 'not'.$user->id_usuario;
            
                        $user = User::find($request->fk_user);
                        
                        $asunto = 'Mensaje de: '.ucwords($user->first_name);
            
                        $data = json_encode([
                            'asunto' => $asunto,
                            'cuerpo' => $request->mensaje,
                            'comment' => true,
            
                        ]);
            
                        Proyecto::notificationPusher($channel, $name, $data);

                    }
        
                }

            }

        }else{

            foreach ($users as $user) { //Envío a los responsables de la tarea
            
                $channel = 'notificaciones_'.$user->id;
                $name = 'not'.$user->id;
    
                $user = User::find($request->fk_user);
                
                $asunto = 'Mensaje de: '.ucwords($user->first_name);
    
                $data = json_encode([
                    'asunto' => $asunto,
                    'cuerpo' => $request->mensaje,
                    'comment' => true,
    
                ]);
    
                Proyecto::notificationPusher($channel, $name, $data);
    
            }

            $copias = DB::table('user_proyectos')
            ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
            ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
            ->where('user_proyectos.fk_proyecto',$proyecto->id)
            ->where('users.id', '!=', $request->fk_user)
            ->get();

            if($copias!=null) { //Si tiene copias la tarea

                foreach ($copias as $user) { //Envío a las copias de la tarea
                    
                    if($request->fk_user!=$user->id) { //Si el usuario que envió el mensaje no está en las copias
                        
                        $channel = 'notificaciones_'.$user->id_usuario;
                        $name = 'not'.$user->id_usuario;
            
                        $user = User::find($request->fk_user);
                        
                        $asunto = 'Mensaje de: '.ucwords($user->first_name);
            
                        $data = json_encode([
                            'asunto' => $asunto,
                            'cuerpo' => $request->mensaje,
                            'comment' => true,
            
                        ]);
            
                        Proyecto::notificationPusher($channel, $name, $data);

                    }
        
                }

            }
        
        }

        return Response::json([
            'response' => true,
            'blogUser' => $blogUser,
            'users' => $users
        ]);
    }

    //sub
    public function createblogusersub(Request $request) {

        $blogUser = new BlogUser;
        $blogUser->fk_user = $request->fk_user;
        $blogUser->fk_blog = $request->fk_blog;
        $blogUser->mensaje = $request->mensaje;
        if($request->reaccion!=null){
            $blogUser->reaccion = $request->reaccion;
        }
        $blogUser->save();

        //Proyecto::saveNotification($asunto, $cuerpo, $request->fk_responsable, 11);

        $blog = Blog::find($request->fk_blog);

        $proyecto = DB::table('sub_proyectos')
        ->where('id',$blog->fk_sub_proyecto)
        ->first();

        $users = DB::table('users')
        ->where('id',$proyecto->fk_responsable)
        ->get();

        if($proyecto->fk_responsable==$request->fk_user) { //Le llega a las copias (si aplica)

            $copias = DB::table('user_proyectos')
            ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
            ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
            ->where('user_proyectos.fk_proyecto',$proyecto->id)
            ->where('users.id', '!=', $request->fk_user)
            ->get();

            if($copias!=null) { //Si tiene copias la tarea

                foreach ($copias as $user) { //Envío a las copias de la tarea
                    
                    if($request->fk_user!=$user->id) { //Si el usuario que envió el mensaje no está en las copias
                        
                        $channel = 'notificaciones_'.$user->id_usuario;
                        $name = 'not'.$user->id_usuario;
            
                        $user = User::find($request->fk_user);
                        
                        $asunto = 'Mensaje de: '.ucwords($user->first_name);
            
                        $data = json_encode([
                            'asunto' => $asunto,
                            'cuerpo' => $request->mensaje,
                            'comment' => true,
            
                        ]);
            
                        Proyecto::notificationPusher($channel, $name, $data);

                    }
        
                }

            }

        }else{

            foreach ($users as $user) { //Envío a los responsables de la tarea
            
                $channel = 'notificaciones_'.$user->id;
                $name = 'not'.$user->id;
    
                $user = User::find($request->fk_user);
                
                $asunto = 'Mensaje de: '.ucwords($user->first_name);
    
                $data = json_encode([
                    'asunto' => $asunto,
                    'cuerpo' => $request->mensaje,
                    'comment' => true,
    
                ]);
    
                Proyecto::notificationPusher($channel, $name, $data);
    
            }

            $copias = DB::table('user_proyectos')
            ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
            ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
            ->where('user_proyectos.fk_proyecto',$proyecto->id)
            ->where('users.id', '!=', $request->fk_user)
            ->get();

            if($copias!=null) { //Si tiene copias la tarea

                foreach ($copias as $user) { //Envío a las copias de la tarea
                    
                    if($request->fk_user!=$user->id) { //Si el usuario que envió el mensaje no está en las copias
                        
                        $channel = 'notificaciones_'.$user->id_usuario;
                        $name = 'not'.$user->id_usuario;
            
                        $user = User::find($request->fk_user);
                        
                        $asunto = 'Mensaje de: '.ucwords($user->first_name);
            
                        $data = json_encode([
                            'asunto' => $asunto,
                            'cuerpo' => $request->mensaje,
                            'comment' => true,
            
                        ]);
            
                        Proyecto::notificationPusher($channel, $name, $data);

                    }
        
                }

            }
        
        }

        return Response::json([
            'response' => true,
            'blogUser' => $blogUser,
            'users' => $users
        ]);
    }
    //sub

    public function listbloguser(Request $request) {
        
        $project_id = $request->project_id;

        $mensajes = DB::table('blog_user')
        ->leftjoin('blog', 'blog.id', '=', 'blog_user.fk_blog')
        ->leftjoin('users', 'users.id', '=', 'blog_user.fk_user')
        ->leftjoin('empleados', 'empleados.id', '=', 'users.id_empleado')
        ->select('blog_user.*', 'blog.nombre', 'users.first_name', 'users.last_name', 'empleados.foto')
        ->where('blog.fk_proyecto', $project_id)
        ->orderBy('blog_user.id', 'desc')
        ->get();

        return Response::json([
            'response' => true,
            'mensajes' => $mensajes
        ]);
    }

    public function listblogusersub(Request $request) {
        
        $project_id = $request->project_id;

        $mensajes = DB::table('blog_user')
        ->leftjoin('blog', 'blog.id', '=', 'blog_user.fk_blog')
        ->leftjoin('users', 'users.id', '=', 'blog_user.fk_user')
        ->leftjoin('empleados', 'empleados.id', '=', 'users.id_empleado')
        ->select('blog_user.*', 'blog.nombre', 'users.first_name', 'users.last_name', 'empleados.foto')
        ->where('blog.fk_sub_proyecto', $project_id)
        ->orderBy('blog_user.id', 'desc')
        ->get();

        return Response::json([
            'response' => true,
            'mensajes' => $mensajes
        ]);
    }

    public function editblog(Request $request) {

        $blog = DB::table('blog')
        ->where('id',$request->blog_id)
        ->update([
            'nombre' => $request->nombre
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function editcopy(Request $request) {

        $users = $request->copy;
        $proyecto = $request->project_id;

        for ($i=0; $i < count($users); $i++) {
            
            $person = DB::table('user_proyectos')
            ->select('id')
            ->where('fk_proyecto',$proyecto)
            ->where('fk_user',$users[$i])
            ->first();

            if(!$person) { //Crear persona que no esaba en la tarea

                $nuevo = new UserProyecto;
                $nuevo->fk_user = $users[$i];
                $nuevo->fk_proyecto = $proyecto;
                $nuevo->save();

            }

        }

        $actualUsers = DB::table('user_proyectos')
        ->where('fk_proyecto',$proyecto)
        ->get();

        if($actualUsers) { //Usuarios copia actuales

            foreach ($actualUsers as $actuals) {
                
                $iteracion = $actuals->fk_user;
                $sw = 0;

                for ($i=0; $i < count($users); $i++) { 
                    
                    if($users[$i]==$iteracion) {
                        $sw = 1;
                    }
                    
                }

                if($sw!=1) { //Si no está, se elimina

                    $delete = DB::table('user_proyectos')->where('fk_proyecto',$proyecto)->where('fk_user',$iteracion)->delete();

                    //DB::delete('delete from user_proyectos where id = '.$delete->id);
                    
                    $channel = 'notificaciones_'.$actuals->fk_user;
                    $name = 'not'.$actuals->fk_user;

                    $asunto = 'USUARIO ELIMINADO';
            
                    $data = json_encode([
                        'asunto' => $asunto,
                        'cuerpo' => $iteracion,
                        'comment' => true,
        
                    ]);
        
                    Proyecto::notificationPusher($channel, $name, $data);
                }
            }
        }

        return Response::json([
            'response' => true,
            'actualUsers' => $actualUsers
        ]);

    }

    public function accesschat(Request $request) {

        $project_id = $request->project_id;

        $access = DB::table('user_proyectos')
        ->where('fk_user',Auth::user()->id)
        ->where('fk_proyecto',$project_id)
        ->first();

        if($access) {
            $access = true;
        }else{
            $access = false;
        }

        return Response::json([
            'response' => $access
        ]);

    }

    public function listcopy(Request $request) {

        $project_id = $request->project_id;

        $copias = DB::table('user_proyectos')
        ->where('fk_proyecto',$project_id)
        ->get();

        if($copias) {

            return Response::json([
                'response' => true,
                'copias' => $copias
            ]);

        }else{

            return Response::json([
                'response' => false,
                'copias' => $copias
            ]);

        }

    }

    public function listcopysub(Request $request) {

        $sub_project_id = $request->project_id;

        $copias = DB::table('user_sub_proyectos')
        ->where('fk_sub_proyecto',$sub_project_id)
        ->get();

        if($copias) {

            return Response::json([
                'response' => true,
                'copias' => $copias
            ]);

        }else{

            return Response::json([
                'response' => false,
                'copias' => $copias
            ]);

        }

    }

    public function editcopysub(Request $request) {

        /*return Response::json([
            'response' => true
        ]);*/

        $users = $request->copy;
        $proyecto = $request->project_id;

        for ($i=0; $i < count($users); $i++) {
            
            $person = DB::table('user_sub_proyectos')
            ->select('id')
            ->where('fk_sub_proyecto',$proyecto)
            ->where('fk_user',$users[$i])
            ->first();

            if(!$person) { //Crear persona que no esaba en la tarea

                $nuevo = new UserSubProyecto;
                $nuevo->fk_user = $users[$i];
                $nuevo->fk_sub_proyecto = $proyecto;
                $nuevo->save();

            }

        }

        $actualUsers = DB::table('user_sub_proyectos')
        ->where('fk_sub_proyecto',$proyecto)
        ->get();

        if($actualUsers) { //Usuarios copia actuales

            foreach ($actualUsers as $actuals) {
                
                $iteracion = $actuals->fk_user;
                $sw = 0;

                for ($i=0; $i < count($users); $i++) { 
                    
                    if($users[$i]==$iteracion) {
                        $sw = 1;
                    }
                    
                }

                if($sw!=1) { //Si no está, se elimina

                    $delete = DB::table('user_sub_proyectos')->where('fk_sub_proyecto',$proyecto)->where('fk_user',$iteracion)->delete();

                    //DB::delete('delete from user_proyectos where id = '.$delete->id);
                    
                    $channel = 'notificaciones_'.$actuals->fk_user;
                    $name = 'not'.$actuals->fk_user;

                    $asunto = 'USUARIO ELIMINADO';
            
                    $data = json_encode([
                        'asunto' => $asunto,
                        'cuerpo' => $iteracion,
                        'comment' => true,
        
                    ]);
        
                    Proyecto::notificationPusher($channel, $name, $data);
                }
            }
        }

        return Response::json([
            'response' => true,
            'actualUsers' => $actualUsers
        ]);

    }

    public function listall(Request $request) {

        $year = $request->year;
        $month = $request->month;

        if($month<10) {
            $month = '0'.$month;
        }

        $fechaInicial = $year.'-'.$month.'-01';
        $fechaFinal = $year.'-'.$month.'-31';

        if($request->user_id!=null) {
            $complement = "and a.fk_responsable = ".$request->user_id."";
        }else{
            $complement = "";
        }

        $proyecto = "select  count(ep.fk_proyecto) as cantidad_evidencia,
		a.*, e.first_name, 
		e.last_name, bg.id as blog_id, bg.nombre as blog_name,
        CASE
        when a.fk_estado = 2 then 'LISTO'
        when a.fk_estado = 3 then 'EN CURSO'
        when a.fk_estado = 4 then 'DETENIDO'
        when a.fk_estado = 5 then 'NO INICIADO'
        when a.fk_estado = 9 then 'PENDIENTE'
        else 'SIN ESTADO'
        END
        AS 'estado',
        case 
        when a.fk_prioridad = 6 then 'ALTA'
        when a.fk_prioridad = 7 then 'MEDIA'
        when a.fk_prioridad = 8 then 'BAJA'
        else 'SIN PRIORIDAD'
        END
        AS 'prioridad'
        from proyectos a
        left join users e on a.fk_responsable =e.id
        left join blog bg on bg.fk_proyecto = a.id
        left join evidencias_proyecto ep on ep.fk_proyecto = a.id where a.fecha_final between '".$fechaInicial."' and '".$fechaFinal."' ".$complement." group by proyecto";

        $proyectos = DB::select($proyecto);

        return Response::json([
            'response' => true,
            'proyecto' => $proyecto,
            'proyectos' => $proyectos
        ]);

    }

    public function push(Request $request) {

        $servicios = DB::table('proyectos')
        ->select('fk_responsable')
        ->where('id',$request->id)
        ->first();

        return Response::json([
            'response' => true,
            'servicios' => $servicios
        ]);

        $copias = DB::table('user_proyectos')
        ->leftjoin('users', 'users.id', '=', 'user_proyectos.fk_user')
        ->select('user_proyectos.*', 'users.id as id_usuario', 'users.first_name')
        ->where('user_proyectos.fk_proyecto',$project_id)
        ->where('users.id', '!=', Auth::user()->id)
        ->get();

        return Response::json([
            'response' => true,
            'copias' => $copias
        ]);

        //Prueba de Pusher
        $idpusher = "578229";
        $keypusher = "a8962410987941f477a1";
        $secretpusher = "6a73b30cfd22bc7ac574";

        //CANAL DE NOTIFICACIÓN DE RECONFIRMACIONES
        $channel = 'notificaciones_2';
        $name = 'not2';

        $data = json_encode([
          'mensaje' => 'Betty luz carrillo te ha asignado la tarea COMPRAR IPAD',
        ]);

        $app_id = $idpusher;
        $key = $keypusher;
        $secret = $secretpusher;

        $body = [
        'data' => $data,
        'name' => $name,
        'channel' => $channel
        ];

        $auth_timestamp =  strtotime('now');
        //$auth_timestamp = '1534427844';

        $auth_version = '1.0';

        //Body convertido a md5 mediante una funcion
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

        return Response::json([
            'response' => true,
            'pusher' => $response
        ]);

    }

}
