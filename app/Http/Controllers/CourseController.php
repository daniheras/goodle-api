<?php

namespace App\Http\Controllers;

use App\Course;
use App\User;
use App\UserCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CourseController extends Controller{

    function index(Request $request, $list = 'all'){
        $courses = [];

        // Si el parametro opcional es 'all', devuelve los cursos publicos y los del usuario


        // Si el parametro opcional es 'user', devuelve solo los cursos del usuario
        if( $list == 'user' || $list == 'all' ){

          $user = User::find($request['current_user']);
          $courses['user_courses'] = $user->courses;

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

    function coursesId(Request $request, $id){
      $courses = DB::select("select * from courses where id in (select course_id from users_courses where user_id = ". $id .");");
      return response()->json($courses, 200);
    }

    // function course(Request $request, $id){
    //   $course = DB::select("select * from goodle.courses where id = ". $id .";");
    //   return response()->json($course, 200);
    // }

    function course(Request $request, $id_course){
      $id = $request->json()->get('user_id');
      $user_courses = DB::select("select id from courses where id in (select course_id from users_courses where user_id = ". $id .");");
      $id_course = intval($id_course);
      $aray = [];
      for ($i=0; $i < count($user_courses); $i++) {
        $array[] = $user_courses[$i]->id;
      }
      if (in_array($id_course, $array)) {
        $course = DB::select("select * from courses where id = ". $id_course .";");
        return response()->json($course, 200);
      }else {
        return response()->json(["error" => "This user is not registered in the required course"], 400);
      }
    }


    function addUserToCourse(Request $request) {
      $user_course = UserCourse::create([
        'user_id' => $request->json()->get('user_id'),
        'course_id' => $request->json()->get('course_id')
      ]);
      return response()->json($user_course, 201);
    }

    function unsubscribeCourse(Request $request) {
      $user_id = $request->json()->get('user_id');
      $course_id = $request->json()->get('course_id');
      $delete = DB::select('delete from users_courses where user_id = ' . $user_id . ' and course_id = '. $course_id .';');
      return response()->json($delete, 200);
    }

}
