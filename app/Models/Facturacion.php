<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Response;
Use DB;

class Facturacion extends Model
{
    protected $table = 'facturacion_de_viajes';

    public static function crearFacturaSiigoPersonaNatural($doc, $seller, $fecha, $identificacion, $centrodeCosto, $observa, $itemValue, $observaciones, $valor, $forma_pago, $totalfactura, $treintadias, $url) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"document\": {
            \"id\": ".$doc."
            },
            \"date\": \"".$fecha."\",
            \"customer\": {
            \"identification\": \"".$identificacion."\",
            \"branch_office\": 0
            },
            \"cost_center\": ".$centrodeCosto.",
            \"seller\": ".$seller.",
            \"observations\": \"".$observa."\",
            \"items\": [
            {
                \"code\": \"".$itemValue."\",
                \"description\": \"".$observaciones."\",
                \"quantity\": 1,
                \"price\": ".$valor.",
                \"taxes\": [

                ]
            }
            ],
            \"payments\": [
            {
                \"id\": ".$forma_pago.",
                \"value\": ".round($totalfactura, 2).",
                \"due_date\": \"".$treintadias."\"
            }
            ],

        }");

        $token = DB::table('siigo')->where('id',1)->first();
        $token = $token->token;

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ".$token."",
            "Partner-Id: AUTONET"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;

    }

    public static function crearFacturaSiigoEmpresa($doc, $seller, $fecha, $identificacion, $centrodeCosto, $retenciones, $observa, $itemValue, $observaciones, $valor, $retef, $forma_pago, $totalfactura, $treintadias, $url) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"document\": {
            \"id\": ".$doc."
            },
            \"date\": \"".$fecha."\",
            \"customer\": {
            \"identification\": \"".$identificacion."\",
            \"branch_office\": 0
            },
            \"cost_center\": ".$centrodeCosto."
            ".$retenciones.",
            \"seller\": ".$seller.",
            \"observations\": \"".$observa."\",
            \"items\": [
            {
                \"code\": \"".$itemValue."\",
                \"description\": \"".$observaciones."\",
                \"quantity\": 1,
                \"price\": ".$valor.",
                ".$retef."
            }
            ],
            \"payments\": [
            {
                \"id\": ".$forma_pago.",
                \"value\": ".$totalfactura.",
                \"due_date\": \"".$treintadias."\"
            }
            ],

        }");

        $token = DB::table('siigo')->where('id',1)->first();
        $token = $token->token;

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ".$token."",
            "Partner-Id: AUTONET"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;

    }

    public static function facturaSandbox() {

        $descripcion = 'hola';
        $identificacion = 819003685;
        $valor = 300000;
        $totalfactura = 300000;
        $treintadias = '2024-06-30';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://private-anon-f1a9a32227-siigoapi.apiary-proxy.com/v1/invoices");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
        \"document\": {
            \"id\": 14852
        },
        \"date\": \"2024-06-26\",
        \"customer\": {
            \"identification\": \"".$identificacion."\",
            \"branch_office\": 0
        },
        \"seller\": 862,
        \"observations\": \"Observaciones\",
        \"items\": [
            {
                \"code\": \"IPoster-is-45\",
                \"description\": \"".$descripcion."\",
                \"quantity\": 1,
                \"price\": ".$valor.",
                \"taxes\": [
                    
                ]
            }
        ],
        \"payments\": [
            {
            \"id\": 8397,
            \"value\": ".$totalfactura.",
            \"due_date\": \"".$treintadias."\"
            }
        ],

        }");

        $siigo = DB::table('siigo')->where('id',1)->first();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ".$siigo->token."",
            "Partner-Id: AUTONET"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;

    }
}
