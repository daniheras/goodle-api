<?php

namespace App\Http\Controllers;

use App\Course;
use App\User;
use App\UserCourse;
use App\Subject;
use App\Task;
use SpacesConnect;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class TaskController extends Controller{

    function index(Request $request, $course_id, $subject_id, $task_id = null){

        $course_id = intval($course_id);
        $subject_id = intval($subject_id);

        $course;
        $subject;

        // Comprobamos que existe el curso
        try{
            $course = Course::findOrFail($course_id);
        } catch( ModelNotFoundException $e ) {
            return response()->json(["Message" => 'Course doesnt exists.'], 404);
        }
        
        // Comprobamos que existe el tema
        try{
            $subject = Subject::findOrFail($subject_id);
        } catch( ModelNotFoundException $e ) {
            return response()->json(["Message" => 'Subject doesnt exists.'], 404);
        }

        // Buscamos en el curso el tema solicitado
        $subject = $course->subjects->find($subject_id);

        // Si el tema no existe en el curso devuelve un 404
        if ( !$subject ){
            return response()->json(["message" => 'The provided subject does not exists in the provided course'], 404);
        }

        // Buscamos en el curso al usuario que realiza la peticion
        $user = $course->users->where('id', $request['current_user'])->first();

        // Comprueba que el usuario que realiza la peticion pertenece al curso o que el curso sea publico
        if ($course->public == 0 && (!$user || $user['pivot']['confirmed'] == 0)) {
            return response()->json(["message" => 'Unauthorized'], 401);
        }

        // Si no se especifica una id para la task devuelve todas las tasks del tema
        if ( $task_id == null ){
            $tasks = $subject->tasks;

            return $tasks;
        }

        $task_id = intval($task_id);

        $task = $subject->tasks->find($task_id);

        return $task;
    }

    function uploadFile(Request $request, $course_id, $subject_id, $task_id){
        $course_id = intval($course_id);
        $subject_id = intval($subject_id);
        $task_id = intval($task_id);

        try{
            $course = Course::findOrFail($course_id);
        } catch( ModelNotFoundException $e ) {
            return response()->json(["Message" => 'Course not found or does not exist'], 404);
        }

        if( !$course->users->find($request['current_user']) ){
            return response()->json(["Message" => 'Unauthorized. You need to belongs to this course'], 401);
        }

        $subject = $course->subjects->find($subject_id);

        $task = $subject->tasks->find($task_id);

        $key = env('DROPLET_KEY');
        $secret = env('DROPLET_SECRET');

        $space_name = "goodle";
        $region = "ams3";

        $space = new SpacesConnect($key, $secret, $space_name, $region);

        $file = $request->file('file');

        $file_fullname = $file->getClientOriginalName();

        $space->UploadFile($file, "public", "files/user".$request['current_user']."/task".$task_id."/".$file_fullname);

        // Si la tarea ya estaba subida simplemente la actualiza
        if ( $task->users->find($request['current_user']) ){
            $user = $task->users->find($request['current_user']);

            $user->pivot['file'] = "https://goodle.ams3.digitaloceanspaces.com/files/user1/task1/".$file_fullname;
            $user->pivot->save();
            return 'Ya existe';
        }

        $user = User::find($request['current_user']);

        $task->users()->save($user, ['file' => "https://goodle.ams3.digitaloceanspaces.com/files/user1/task1/".$file_fullname]);

        return  'okey';
    }

    function addTask(Request $request, $course_id, $subject_id){

        $course = Course::find($course_id);

        $subject = $course->subjects->find($subject_id);

        $input = $request->except('current_user');

        $task = $subject->tasks()->create($input);

        return $task;
    }

    function updateTask(Request $request, $course_id, $subject_id, $task_id){

        $course = Course::find($course_id);

        

        $subject = $course->subjects->find($subject_id);

        

        $input = $request->except('current_user');

        

        $task = $subject->tasks()->find($task_id)->update($input);

        return 'Updated';

    }

    function deleteTask(Request $request, $course_id, $subject_id, $task_id){

        $course = Course::find($course_id);

        $subject = $course->subjects->find($subject_id);

        $input = $request->except('current_user');

        $task = $subject->tasks()->find($task_id)->delete();

        return 'Deleted';

    }

    function listFiles(Request $request, $course_id, $subject_id, $task_id){

        return 'okey';

        $course = Course::find($course_id);

        $subject = $course->subjects->find($subject_id);

        $input = $request->except('current_user');

        $task = $subject->tasks()->find($task_id)->delete();

        return 'Deleted';

    }
}
