<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cargo;
use App\Models\CargoProyecto;
use App\Models\PivotCargoProyecto;
use App\Models\Notas;
use App\Models\Siigo;
use App\Models\SiigoDepartamento;
use App\Models\SiigoCiudad;
use App\Models\Sede;
use Auth;
use Response;
Use DB;

class ConfigController extends Controller
{
    const FIREBASE_KEY_DRIVER = "AAAABsrVRW8:APA91bHeyqFdFTYzPuSQe6SXB-FO1bLqJ_cQcNTWim-oBShNazh00NagwyKA0ouZC94lre12goZcjSzyqwpDJsGPiVa4voh7xm3-DOkl11u2YF9f3PnLXFRdmb59vXYj6cHeafkuqeA-";
    const FIREBASE_KEY_CLIENT = "AAAAXZ-yM9s:APA91bGhnV8patuAqhFUFD0VUxmfNS65-8rkck3_Dngwb8LMsK1QomoZQsXHF5-Z4lXuPMMogTqpPD5KDqZzSL5tux6GcN5Rvugv0yGVtLHORIZxl_Pw4mB2JbZHXufQvkefloY1-IeS";
    const VIBRATION_PATTERN = [2000, 2000, 500, 1000];
    const LED_COLOR = [0, 0, 255, 0];
    const KEY_WHATSAPP = "EAAHPlqcJlZCMBAMDLjgTat7TlxvpmDq1fgzt2gZBPUnEsTyEuxuJw9uvGJM1WrWtpN7fmpmn3G2KXFZBRIGLKEDhZBPZAeyUSy2OYiIcNEf2mQuFcW67sgGoU95VkYayreD5iBx2GbnZBgaGvS8shX6f2JKeBp7pm9TNLm2EZBEbcx0Sdg47miONZCpUNZCfqEWlZAFxkltEOBPAZDZD";

    public function createposition(Request $request) {

        $cargo =  new Cargo;
        $cargo->codigo = 'ABC';
        $cargo->nombre_cargo = $request->nombre_cargo;
        $cargo->save();

        $first3 = substr($request->nombre_cargo, 0, 3);

        $updateCargo = DB::table('cargos')
        ->where('id',$cargo->id)
        ->update([
            'codigo' => $first3.$cargo->id
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function createproject(Request $request) {

        $project = new CargoProyecto;
        $project->codigo = 'ABC';
        $project->nombre_proyecto = $request->nombre_proyecto;
        $project->periodo = $request->periodo;
        $project->dias = $request->dias;

        $project->save();

        $first3 = substr($request->nombre_proyecto, 0, 3);

        $updateCargo = DB::table('cargo_proyectos')
        ->where('id',$project->id)
        ->update([
            'codigo' => $first3.$project->id
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function relationship(Request $request) {

        $unir = new PivotCargoProyecto;
        $unir->fk_cargo = $request->fk_cargo;
        $unir->fk_cargo_proyectos = $request->fk_cargo_proyectos;
        $unir->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function listpositions(Request $request) {

        $positions = DB::table('cargos')
        ->where('estado',1)
        ->get();

        return Response::json([
            'response' => true,
            'positions' => $positions
        ]);

    }

    public function listpositionprojects(Request $request) {

        $projects = DB::table('cargo_proyectos')
        ->where('estado',1)
        ->where('activo',1)
        ->get();

        return Response::json([
            'response' => true,
            'projects' => $projects
        ]);

    }

    public function listaccionsbyuser(Request $request) {

        $query = "SELECT
        a.id,
        a.codigo,
        a.nombre,
        a.id_padre,
        a.`path` AS ruta,
        a.icon,
        a.orden,
        pa.activo,
        JSON_OBJECTAGG(
        sub.id,
            JSON_OBJECT(
                'codigo', sub.codigo,
                'nombre', sub.nombre,
                'id_padre', sub.id_padre,
                'ruta', sub.path,
                'icono', sub.icon,
                'orden', sub.orden,
                'activo', sub.activo
            )
        ) AS sub_acciones
    FROM
        acciones a
    INNER JOIN 
        perfiles_acciones pa ON
        pa.fk_accion = a.id
    INNER JOIN 
        perfil p ON
        p.id = pa.fk_perfil
    LEFT JOIN 
        users u ON
        u.id_perfil = p.id
    LEFT JOIN 
        (
        SELECT
            DISTINCT acc.id,
            acc.codigo,
            acc.nombre,
            acc.id_padre,
            acc.path,
            acc.icon,
            acc.orden,
            pa_sub.activo
        FROM
            acciones acc
        INNER JOIN perfiles_acciones pa_sub ON
            pa_sub.fk_accion = acc.id
        INNER JOIN perfil pp ON
            pp.id = pa_sub.fk_perfil
        WHERE
        pp.id = ".$request->perfil."
        ) sub ON
        a.id = sub.id_padre
    WHERE
        p.id = ".$request->perfil."
        AND a.id_padre is null
    GROUP BY
        a.id";

        $consulta = DB::select($query);

        return Response::json([
            'response' => true,
            'consulta' => $consulta
        ]);

    }

    public function listfirst(Request $request) {

        $query = "SELECT
        a.id,
        a.codigo,
        a.nombre,
        a.id_padre,
        a.`path` AS ruta,
        a.icon,
        a.orden,
        false AS active,
        JSON_OBJECTAGG(
        sub.id,
            JSON_OBJECT(
                'codigo', sub.codigo,
                'nombre', sub.nombre,
                'id_padre', sub.id_padre,
                'ruta', sub.path,
                'icono', sub.icon,
                'active', false
            )
        ) AS sub_acciones
    FROM
        acciones a
    LEFT JOIN 
        acciones sub on sub.id_padre = a.id
    WHERE a.id_padre is null
    GROUP BY
        a.id;";

        $consulta = DB::select($query);

        return Response::json([
            'response' => true,
            'consulta' => $consulta
        ]);

    }

    public function listprofileusersandaccions(Request $request) {
        
        $query = "SELECT
        p.id,
        p.codigo,
        p.nombre,
        p.activo,
        COUNT(DISTINCT u.id) AS cantidad_usuarios,
        JSON_OBJECTAGG(a.id, JSON_OBJECT('nombre', a.nombre, 'orden', a.orden)) AS acciones
        FROM
            perfil p
        LEFT JOIN users u ON
            u.id_perfil = p.id
        LEFT JOIN perfiles_acciones pa ON
            pa.fk_perfil = p.id
        LEFT JOIN acciones a ON
            a.id = pa.fk_accion
        WHERE a.id_padre IS NULL
        GROUP BY p.id";

        $consulta = DB::select($query);

        return Response::json([
            'response' => true,
            'consulta' => $consulta
        ]);

    }

    public function listprojectsbyposition(Request $request) {

        $query = "select cargo_proyectos.*, pivot_cargo_proyectos.fk_cargo from cargo_proyectos left join pivot_cargo_proyectos on pivot_cargo_proyectos.fk_cargo_proyectos = cargo_proyectos.id where pivot_cargo_proyectos.fk_cargo = ".$request->cargo_id."";
        $consulta = DB::select($query);

        return Response::json([
            'response' => true,
            'consulta' => $consulta
        ]);

    }

    public function createnote(Request $request) {

        $know = "select * from notas order by orden asc";
        $mysql = DB::select($know);
        $total = count($mysql);

        $nota = new Notas;
        $nota->descripcion = $request->descripcion;
        $nota->estado = $request->estado;
        $nota->fk_user = $request->user_id;
        $nota->orden = intval($total)+1;
        if($request->notificar==1) {
            $nota->notificar = 1;
            $nota->fecha_notificacion = $request->fecha_notificacion;
            $nota->hora_notificacion = $request->hora_notificacion;
        }
        $nota->save();
        
        $update = DB::table('notas')
        ->where('id',$nota->id)
        ->update([
            'codigo' => $nota->id.substr($request->descripcion, 0, 3)
        ]);

        return Response::json([
            'response' => true
        ]);

    }

    public function editnote(Request $request) {

        $note = Notas::find($request->id);
        $note->descripcion = $request->descripcion;
        if($request->notificar==1) {
            $note->notificar = 1;
            $note->fecha_notificacion = $request->fecha_notificacion;
            $note->hora_notificacion = $request->hora_notificacion;
        }else{
            $note->notificar = null;
        }
        $note->save();

        return Response::json([
            'response' => true
        ]);
        
    }

    public function changenotestatus(Request $request) {

        $note = Notas::find($request->id);
        $note->estado = $request->estado;
        $note->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function deletenote(Request $request) {

        $delete = DB::table('notas')->where('id',$request->id)->delete();

        return Response::json([
            'response' => true
        ]);

    }

    public function listnotes(Request $request) {

        $notas = "select id, codigo, orden, descripcion, estado, fk_user, notificar, fecha_notificacion, hora_notificacion from notas where fk_user = ".$request->user_id."";
        $notas = DB::select($notas);

        return Response::json([
            'response' => true,
            'notas' => $notas
        ]);

    }

    public function listheadquarters(Request $request) {

        $sedes = DB::table('sedes')
        ->where('estado',1)
        ->get();

        return Response::json([
            'response' => true,
            'sedes' => $sedes
        ]);

    }

    public function departamento(Request $request) {

        $dep = new SiigoDepartamento;
        $dep->nombre = $request->nombre;
        $dep->codigo = $request->codigo;
        $dep->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function ciudad(Request $request) {

        $dep = new SiigoCiudad;
        $dep->nombre = $request->nombre;
        $dep->codigo = $request->codigo;
        $dep->fk_departamento = $request->departamento;
        $dep->save();

        return Response::json([
            'response' => true
        ]);

    }

    public function liststatus(Request $request) {

        $query = "SELECT * from estados where fk_estados_maestros = ".$request->id." and activo = 1";
        $select = DB::select($query);

        return Response::json([
            'response' => true,
            'estados' => $select
        ]);

    }

    public function listtypes(Request $request) {

        $query = "SELECT * from tipos where fk_tipo_maestros = ".$request->id." and activo = 1";
        $select = DB::select($query);

        return Response::json([
            'response' => true,
            'tipos' => $select
        ]);

    }

}
