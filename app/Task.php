<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title', 'text_content', 'order', 'finish_date'
    ];

    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot(['file', 'uploaded_at']);;
    }

    protected $hidden = [];
}
