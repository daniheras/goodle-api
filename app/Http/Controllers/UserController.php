<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller{

    function register(Request $request){

        $user = User::create([
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => Hash::make($request['password'])
            ]);

        return response()->json($user, 201);

    }
}
