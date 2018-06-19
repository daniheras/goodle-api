<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'title', 'description', 'order'
    ];

    protected $hidden = [];

    // 1:N
    // Un tema puede tener varias tareas pero una tarea solo puede estar en un tema.
    public function tasks()
    {
        return $this->hasMany('App\Task', 'subject_id');
    }
}
