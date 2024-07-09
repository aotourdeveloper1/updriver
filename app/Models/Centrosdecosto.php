<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Auth;
use Response;
Use DB;

class Centrosdecosto extends Model
{
    protected $table = 'centrosdecosto';

    public static function activo() {

        $proveedores = DB::table('centrosdecosto')
        ->select('centrosdecosto.id', 'centrosdecosto.razonsocial', 'centrosdecosto.inactivo', 'estados.nombre')
        ->leftjoin('estados', 'estados.id', '=', 'centrosdecosto.inactivo')
        ->where('centrosdecosto.inactivo',12)
        ->get();

        return $proveedores;
    }

    public static function activoFinanciero() {

        $proveedores = DB::table('centrosdecosto')
        ->select('centrosdecosto.id', 'centrosdecosto.razonsocial', 'centrosdecosto.inactivo', 'estados.nombre')
        ->leftjoin('estados', 'estados.id', '=', 'centrosdecosto.inactivo')
        ->whereIn('centrosdecosto.inactivo',[12,13])
        ->get();

        return $proveedores;
    }
}
