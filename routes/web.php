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

    // ## Rutas de cursos ##

    $router->get('/courses[/{list}[/{id}]]', 'CourseController@index'); // Listar todos los cursos

    $router->post('/courses', 'CourseController@addCourse'); // Crear un curso

    $router->put('/courses', 'CourseController@updateCourse'); // Actualizar/Modificar un curso

    $router->delete('/courses/{id}', 'CourseController@deleteCourse'); // Borrar un curso

    /* $router->get('/courses/{id}/users', 'CourseController@getMembers'); */ // Listar todos los usuarios de un curso

    $router->post('/courses/{course_id}/invite/{username}', 'CourseController@inviteUsers'); // Invitar un unico usuario al curso.

    $router->post('/courses/{course_id}/accept_invite/', 'CourseController@acceptInvite'); // Invitar un unico usuario al curso.

    //Ruta para comprobar el estado de la api y si estas autenticado

    $router->get('/test', function() {
        return response()->json([
            'message' => 'Ok!',
        ]);
    });


});
