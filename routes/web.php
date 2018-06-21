<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'Goodle API v1';
});


//Ruta para loguearse con un usuario ya creado y recibir un token para poder usar el resto de endpoints

$router->post('/auth/login', 'AuthController@postLogin');

//Ruta para registrarse, crear un nuevo usuario.

$router->post('/register', 'UserController@register');

// Middleware que protege todos los endpoints de peticiones no autorizadas

$router->group(['middleware' => 'auth:api'], function($router)
{

    // ## Rutas de usuarios ##

    $router->get('/user', 'UserController@userInfo'); // Get user info
    $router->post('/user/update', 'UserController@updateUser'); // Update user
    $router->post('/user/resetPassword', 'UserController@resetPassword'); // Update user
    $router->get('/user/getInvitations', 'UserController@getInvitations'); // Update user




    // ## Rutas de cursos ##

    $router->get('/courses[/{course_id}]', 'CourseController@index'); // Listar cursos
    /* [/{list}[/{id}]] */

    $router->post('/courses', 'CourseController@addCourse'); // Crear un curso

    $router->put('/courses', 'CourseController@updateCourse'); // Actualizar/Modificar un curso

    $router->delete('/courses/{id}', 'CourseController@deleteCourse'); // Borrar un curso

    /* $router->get('/courses/{id}/users', 'CourseController@getMembers'); */ // Listar todos los usuarios de un curso

    $router->post('/courses/{course_id}/invite/{username}', 'CourseController@inviteUsers'); // Invitar un unico usuario al curso.

    $router->post('/courses/{course_id}/accept_invite/', 'CourseController@acceptInvite'); // Invitar un unico usuario al curso.


    // ## Rutas de temas ##

    $router->get('/courses/{course_id}/subjects/', 'SubjectController@index'); //Listar todos los temas de un curso

    $router->post('/courses/{course_id}/subjects/', 'SubjectController@addSubject'); //Crear un tema en un curso
    
    $router->put('/courses/{course_id}/subjects/{subject_id}', 'SubjectController@updateSubject'); //Actualizar un tema en un curso

    $router->delete('/courses/{course_id}/subjects/{subject_id}', 'SubjectController@deleteSubject'); //Borrar un tema de un curso

    // ## Rutas de tareas ##

    $router->get('/courses/{course_id}/subjects/{subject_id}/tasks[/{task_id}]', 'TaskController@index'); //Listar todas las tareas de un curso o la solicitada

    $router->post('/courses/{course_id}/subjects/{subject_id}/tasks/', 'TaskController@addTask'); //Crear una tarea en un tema de un curso
    
    $router->put('/courses/{course_id}/subjects/{subject_id}/tasks/{task_id}', 'TaskController@updateTask'); //Actualizar una tarea de un tema de un curso

    $router->delete('/courses/{course_id}/subjects/{subject_id}/tasks/{task_id}', 'TaskController@deleteTask'); //Borrar una tarea de un tema de un curso


    // ## Rutas de archivos de tareas ##

    $router->post('/courses/{course_id}/subjects/{subject_id}/tasks/{task_id}', 'TaskController@uploadFile'); //Subir un archivo

    $router->get('/courses/{course_id}/subjects/{subject_id}/tasks/{task_id}/files', 'TaskController@listFiles'); //Listar archivos por usuario en una tarea


    

    //Ruta para comprobar el estado de la api y si estas autenticado

    $router->get('/test', function() {
        return response()->json([
            'message' => 'Ok!',
        ]);
    });


});
