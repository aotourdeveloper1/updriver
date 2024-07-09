<?php

namespace App\Http\Controllers;
use Response;
use App\Models\User;
use Auth;
Use DB;

use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function logout(Request $request) {

        $user = Auth::user();
    
        $user->tokens()->delete();

        return Response::json([
            'response' => true
        ]);

    }

}
