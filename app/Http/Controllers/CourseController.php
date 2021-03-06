<?php

namespace App\Http\Controllers;

use App\Course;
use App\User;
use App\UserCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseController extends Controller{

    function index(Request $request, $course_id = null){
        //Parameters: , $list = 'all', $id = null 
        $courses = [];

        if ( !$course_id == null ){
          try{
            $course = Course::findOrFail( $course_id );
            
            //Añadimos un flag para decirle al front si el usuario que realiza la petición es admin del curso o no.
            if ( $course['admin_id'] == $request['current_user'] ){
              $course['admin'] = true;
              return $course;
            } else {
              $course['admin'] = false;
            }

            return $course;
          } catch (ModelNotFoundException $e) { 
            return response()->json(["Message" => 'Course not found or does not exist'], 404);
          }
        }

        $user_courses = User::find($request['current_user'])->courses;
        $courses['user_courses'] = $user_courses;

        $publicCourses = Course::where('public', 1)->get();
        $courses['public_courses'] = $publicCourses;

        return $courses;
    }

    function addCourse(Request $request){
        $this->validate($request, [
            'name' => 'required|max:255'
        ]);

        $input = $request->except('current_user');
        $input['admin_id'] = $request['current_user'];

        //dd($input);


        $course = Course::create($input);
        //TODO: eloquent
        DB::select('insert into course_user (user_id, course_id, confirmed) values ('. $request['current_user'] .', ' . $course->id . ', 1);');
        // UserCourse::create([
        //   'user_id' => $request['current_user'],
        //   'course_id' => $course->id,
        //   'confirmed' => 1
        // ]);

      return response()->json($course, 201);

    }

    function updateCourse(Request $request){
      $this->validate($request, [
        'id' => 'required'
      ]);


      $course = Course::find($request['id']);

      // Si el usuario no es el admin del curso le devuelve unauthorized.
      if ( $course['admin_id'] != $request['current_user'] ) {
          return response()->json(["Message" => 'You need to be the admin of this course to modify it'], 401);
      }

      $course['update'] = ["updated_at" => "2011-01-01 01:01:01"];

      // Actualiza en el curso todos los campos definidos en el JSON de la request
      $course->update($request['update']);

      return response()->json(["Message" => 'The course has been modified'], 201);
    }

    function deleteCourse(Request $request, $id) {
      //TODO: Añadir restriccion: solo el admin del curso puede borrarlo.

      $param = intval($id);

      try {
        if( $param == 0 ){
          return response()->json(["Message" => 'A valid course_id must be provided'], 401);
        }

        $course = Course::findOrFail($param);

        //Si el usuario no es el admin devuelve unauthorized
        if ( $course['admin_id'] != $request['current_user'] ) {
          return response()->json(["Message" => 'You need to be the admin of this course to delete it'], 401);
        }

        $course->delete();

        return response()->json(["Message" => 'The course has been deleted'], 200);

      } catch (ModelNotFoundException $e) { // Si el curso solicitado no existe devuelve una excepcion

        return response()->json(["Message" => 'Course not found or does not exist'], 404);
      }

      return response()->json(["Message" => 'Unexpected error'], 500);
    }

    function inviteUsers(Request $request, $username, $courseId){

      $courseId = intval($courseId);

      try {
        if( $courseId == 0 ){
          return response()->json(["Message" => 'A valid course_id must be provided'], 401);
        }

        // Guardamos el curso solicitado en la request
        $course = Course::findOrFail($courseId);

        //Si el usuario no es el admin devuelve unauthorized
        if ( $course['admin_id'] != $request['current_user'] ) {
          return response()->json(["Message" => 'You need to be the admin of this course to invite people'], 401);
        }

        /* INSERT USERS */

        try {

          // Comprobamos que el usuario no exista ya dentro del curso, bien sea invitado o como miembro.
          if ( $course->users->where('username', 'like', $username)->toArray() ){
            return response()->json(["Message" => "The user '".$username."' has been already invited."], 406);
          }

          $request_user = User::where('username', 'like', $username)->firstOrFail();

          $course->users()->save($request_user);

          return response()->json(["Message" => "User invited successfully"], 200);

        } catch (ModelNotFoundException $e) {
          return response()->json(["Message" => 'User not found or does not exist'], 404);
        }

        /* END OF INSERT USERS */

      } catch (ModelNotFoundException $e) { // Si el curso solicitado no existe devuelve una excepcion

        return response()->json(["Message" => 'Course not found or does not exist'], 404);
      }

      return response()->json(["Message" => 'Unexpected error'], 500);

    }

    function acceptInvite( Request $request, $course_id ){
      try {

        $course_id = intval($course_id);
        $course = Course::findOrFail($course_id);

        $user = $course->users->find($request["current_user"]);

        // Comprueba que el usuario haya sido invitado al curso
        if ( !$user ) {
          return response()->json(["Message" => 'Sorry, you have not been invited to this course'], 404);
        }

        // Actualizamos el atributo confirmed para que el usuario pase a ser miembro del curso
        $user->pivot["confirmed"] = 1;
        $user->pivot->save();

        return response()->json(["Message" => 'Invitation accepted successfully'], 200);

      } catch( ModelNotFoundException $e ) {
        return response()->json(["Message" => 'Course not found or does not exist'], 404);
      }

      return response()->json(["Message" => 'Something went wrong'], 500);

    }

}
