<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siigo extends Model
{
    const URL_SIIGO = "https://private-anon-3e8aca8745-siigoapi.apiary-proxy.com/";

    const KEY_SIIGO = "OWE1OGNkY2QtZGY4ZC00Nzg1LThlZGYtNmExMzUzMmE4Yzc1Omt2YS4yJTUyQEU="; //Pruebas
    //const KEY_SIIGO = "OGM0NDViNGQtMzIzNC00ZTdmLTllMjEtZmRjN2Y2ODFlYjRjOmY2ZDZyZTQvKUQ="; //Producción
    
    protected $table = 'siigo';
}
