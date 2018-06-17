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

    function index(Request $request, $list = 'all', $id = null){
        $courses = [];

        if ($list == 'user' && $id != null) {
          $course = Course::find($id);
          $user_courses = User::find($request['current_user'])->courses;

          // Si el usuario que pide este curso es el admin retorna el curso y un flag a true
          // Si el curso no es publico y el usuario no esta registrado en el curso solicitado retorna un mensaje de error
          // Si el curso es publico o el usuaio esta registrado en el curso solicitado y no es el admin retorna el curso
          if ($course->admin_id == $request['current_user']) {
            $course['admin'] = true;
            return $course;
          }else if ($course->public == 0 && !in_array($course, (array) $user_courses)) {
            return response()->json(["message" => 'This user are not registered in this course'], 400);
          }

          return $course;
        }

        // Si el parametro opcional es 'user', devuelve solo los cursos del usuario
        if( $list == 'user' || $list == 'all') {

          $user_courses = User::find($request['current_user'])->courses;
          $courses['user_courses'] = $user_courses;
        }

        // Si el parametro opcional es 'public', devuelve solo los cursos publicos
        if ( $list == 'public' || $list == 'all' ){

          $publicCourses = Course::where('public', 1)->get();
          $courses['public_courses'] = $publicCourses;
        }

        return $courses;
    }

    function addCourse(Request $request){
        $this->validate($request, [
            'name' => 'required|max:255',
            'category' => 'required'
        ]);

        // Sets a default image if no one provided
        if( !array_key_exists('picture', $request) ){
          $request['picture'] = 'https://placeholdit.co//i/500x200?&bg=ecf0f1&fc=e74c3c&text=Goodle%20Course';
        }

        // Sets a default description if no one provided
        if( !array_key_exists('description', $request ) ){
          $request['description'] = 'This course has no description';
        }

        $course = Course::create([
            'name' => $request['name'],
            'admin_id' => $request['current_user'],
            'category' => $request['category'],
            'picture' => $request['picture'],
            'description' => $request['description']
        ]);
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



    }

}
