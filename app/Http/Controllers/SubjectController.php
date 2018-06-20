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
                return response()->json(["message" => 'Unauthorized, you need to belong to the course'], 401);
            }

            $subjects = $course->subjects;

            return $subjects;

        } catch( ModelNotFoundException $e ) {
            return response()->json(["Message" => 'Course not found or does not exist'], 404);
        }

        return response()->json(["Message" => 'Something went wrong'], 500);
    }

    function addSubject(Request $request, $course_id){

        try{
            $course_id = intval($course_id);
            $course = Course::findOrFail($course_id);

            // Buscamos en el curso al usuario que realiza la peticion
            $user = $course->users->where('id', $request['current_user'])->first();

            // Comprueba que el usuario que realiza la peticion pertenece al curso o que el curso sea publico
            if ($course->public == 0 && (!$user || $user['pivot']['confirmed'] == 0)) {
                return response()->json(["message" => 'Unauthorized'], 401);
            }

            // Comprobamos que el usuario que realiza la peticion es el admin del curso
            if( $course->admin_id !== $request['current_user'] ) {
                return response()->json(["message" => 'Unauthorized, you must be the admin of this course'], 401);
            }

            // Validamos
            $this->validate($request, [
                'title' => 'required|max:255',
                'order' => 'required'
            ]);
    
            // Guardamos en una variable todo el cuerpo de la peticion del usuario.
            $input = $request->except('current_user');

            // Creamos el recurso en la base de datos
            $subject = $course->subjects()->create($input);

            return $subject;

        } catch( ModelNotFoundException $e ) {
            return response()->json(["Message" => 'Course not found or does not exist'], 404);
        }

        return response()->json(["Message" => 'Something went wrong'], 500);
    }

    function updateSubject(Request $request, $course_id, $subject_id){

        try{
            $course_id = intval($course_id);
            $subject_id = intval($subject_id);

            $course = Course::findOrFail($course_id);

            // Buscamos en el curso al usuario que realiza la peticion
            $user = $course->users->where('id', $request['current_user'])->first();

            // Comprueba que el usuario que realiza la peticion pertenece al curso o que el curso sea publico
            if ($course->public == 0 && (!$user || $user['pivot']['confirmed'] == 0)) {
                return response()->json(["message" => 'Unauthorized'], 401);
            }

            // Comprobamos que el usuario que realiza la peticion es el admin del curso
            if( $course->admin_id !== $request['current_user'] ) {
                return response()->json(["message" => 'Unauthorized, you must be the admin of this course'], 401);
            }

            // Buscamos en el curso el tema solicitado
            $subject = $course->subjects->find($subject_id);

            // Si el tema no existe en el curso devuelve un 404
            if ( !$subject ){
                return response()->json(["message" => 'The provided subject does not exists in the provided course'], 404);
            }
    
            // Guardamos en una variable todo el cuerpo de la peticion del usuario.
            $input = $request->except('current_user');

            // Actualizamos el recurso en la base de datos
            $subject->update($input);

            return 'Updated';

        } catch( ModelNotFoundException $e ) {
            return response()->json(["Message" => 'Course not found or does not exist'], 404);
        }

        return response()->json(["Message" => 'Something went wrong'], 500);
    }

    function deleteSubject(Request $request, $course_id, $subject_id){

        try{
            $course_id = intval($course_id);
            $subject_id = intval($subject_id);

            $course = Course::findOrFail($course_id);

            // Buscamos en el curso al usuario que realiza la peticion
            $user = $course->users->where('id', $request['current_user'])->first();

            // Comprueba que el usuario que realiza la peticion pertenece al curso o que el curso sea publico
            if ($course->public == 0 && (!$user || $user['pivot']['confirmed'] == 0)) {
                return response()->json(["message" => 'Unauthorized'], 401);
            }

            // Comprobamos que el usuario que realiza la peticion es el admin del curso
            if( $course->admin_id !== $request['current_user'] ) {
                return response()->json(["message" => 'Unauthorized, you must be the admin of this course'], 401);
            }

            // Buscamos en el curso el tema solicitado
            $subject = $course->subjects->find($subject_id);

            // Si el tema no existe en el curso devuelve un 404
            if ( !$subject ){
                return response()->json(["message" => 'The provided subject does not exists in the provided course'], 404);
            }
    
            // Guardamos en una variable todo el cuerpo de la peticion del usuario.
            $input = $request->except('current_user');

            // Actualizamos el recurso en la base de datos
            $subject->delete();

            return 'Deleted';

        } catch( ModelNotFoundException $e ) {
            return response()->json(["Message" => 'Course not found or does not exist'], 404);
        }

        return response()->json(["Message" => 'Something went wrong'], 500);
    }

}
