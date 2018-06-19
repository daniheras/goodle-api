<?php

namespace App\Http\Controllers;

use App\Course;
use App\User;
use App\UserCourse;
use App\Subject;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SubjectController extends Controller{

    function index(Request $request, $course_id){

        try{
            $course_id = intval($course_id);
            $course = Course::findOrFail($course_id);

            // Buscamos en el curso al usuario que realiza la peticion
            $user = $course->users->where('id', $request['current_user'])->first();

            // Comprueba que el usuario que realiza la peticion pertenece al curso o que el curso sea publico
            if ($course->public == 0 && (!$user || $user['pivot']['confirmed'] == 0)) {
                return response()->json(["message" => 'Unauthorized'], 401);
            }

            $subjects = $course->subjects;

            return $subjects;

        } catch( ModelNotFoundException $e ) {
            return response()->json(["Message" => 'Course not found or does not exist'], 404);
        }

        return response()->json(["Message" => 'Something went wrong'], 500);
    }

}
