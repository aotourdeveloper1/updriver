<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use DB;
use Auth;
use Mail;
use App\Models\Proveedor;
use App\Models\Conductor;
use App\Models\Vehiculo;

class VehiculosController extends Controller
{
    public function create(Request $request) {

        $vehiculo = new Vehiculo;
        $vehiculo->placa = $request->placa;
        $vehiculo->proveedores_id = $request->proveedor_id;
        $vehiculo->save();

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

    public function maintenancelock(Request $request) {

        return Response::json([
            'response' => true
        ]);

    }

    public function systemslock(Request $request) {

        return Response::json([
            'response' => true
        ]);

    }
}
