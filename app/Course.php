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



    public function admins()
    {
        return $this->hasMany('App\Course', 'admin_id');
    }
}
