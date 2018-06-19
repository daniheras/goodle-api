<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller{

    function userInfo(Request $request){
        return response()->json(Auth::user());
    }

    function register(Request $request){

        $user = User::create([
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => Hash::make($request['password'])
            ]);

        return response()->json($user, 201);

    }
}
