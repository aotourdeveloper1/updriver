<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ConfigController;

use Auth;
use Response;
Use DB;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    public $timestamps = false;

    public static function todos() {

        $proveedores = DB::table('proveedores')
        ->select('proveedores.id', 'proveedores.razonsocial', 'proveedores.fk_estado', 'estados.nombre')
        ->leftjoin('estados', 'estados.id', '=', 'proveedores.fk_estado')
        ->get();

        return $proveedores;
    }

    public static function activoTotal() {

        $proveedores = DB::table('proveedores')
        ->select('proveedores.id', 'proveedores.razonsocial', 'proveedores.fk_estado', 'estados.nombre')
        ->leftjoin('estados', 'estados.id', '=', 'proveedores.fk_estado')
        ->where('proveedores.fk_estado',50)
        ->get();

        return $proveedores;
    }

    public static function activoFinanciero() {

        $proveedores = DB::table('proveedores')
        ->select('proveedores.id', 'proveedores.razonsocial', 'proveedores.fk_estado', 'estados.nombre')
        ->leftjoin('estados', 'estados.id', '=', 'proveedores.fk_estado')
        ->whereIn('proveedores.fk_estado',[51,50])
        ->get();

        return $proveedores;
    }

    public static function NotificarPago($celularProveedor, $nombreProveedor) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v15.0/109529185312847/messages");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"messaging_product\": \"whatsapp\",
            \"to\": \"".$celularProveedor."\",
            \"type\": \"template\",
            \"template\": {
            \"name\": \"pago\",
            \"language\": {
                \"code\": \"es\",
            },
            \"components\": [{
                \"type\": \"header\",
                \"parameters\": [{
                \"type\": \"text\",
                \"text\": \"".$nombreProveedor."\",
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

        return $response;

    }

}
