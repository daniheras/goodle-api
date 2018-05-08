<?php

namespace App\Http\Controllers;

use App\Course;
use App\User;
use App\UserCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CourseController extends Controller{

    function index(Request $request){
        $courses = [];

        $user = User::find($request['current_user']);

        $courses['user_courses'] = $user->courses;

        $publicCourses = Course::where('public', 1)->get();

        $courses['public_courses'] = $publicCourses;

        return $courses;
    }

    function addCourse(Request $request){
        $course = Course::create([
            'name' => $request->json()->get('name')
        ]);

    return response()->json($course, 201);

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
