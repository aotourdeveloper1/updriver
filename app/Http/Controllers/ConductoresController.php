<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use DB;
use Auth;
use Mail;
use App\Models\Proveedor;
use App\Models\Conductor;

class ConductoresController extends Controller
{
    public function create(Request $request) {

        $conductor = new Conductor;
        $conductor->nombre_completo = $request->nombre_completo;
        $conductor->proveedores_id = $request->proveedor_id;
        $conductor->save();

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

        return Response::json([
            'response' => true
        ]);

    }

    public function list(Request $request) {

        return Response::json([
            'response' => true
        ]);

    }

    public function createuserapp(Request $request) {

        return Response::json([
            'response' => true
        ]);

    }

    public function socialsecurity(Request $request) {

        return Response::json([
            'response' => true
        ]);

    }
}
