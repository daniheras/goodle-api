<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name', 'category', 'public', 'description', 'picture', 'public', 'admin_id', 'theme', 'color'
    ];

    protected $hidden = [];

    // Definimos la relacion NM entre cursos / usuarios.
    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot(['confirmed', 'member_since']);;
    }

    // Esto está mal, hay que hacerlo tal y como esta hecho en subjects
    public function admins()
    {
        return $this->hasMany('App\User', 'id', 'admin_id');
    }

    // Forma correcta de hacer el hasMany
    public function subjects()
    {
        return $this->hasMany('App\Subject', 'course_id');
    }
}
