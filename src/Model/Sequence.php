<?php

namespace App\Model;

use Cartalyst\Sentinel\Users\EloquentUser;

class Sequence extends EloquentUser
{
    protected $table = 'sequence';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'start',
        'end',
        'expression',
        'video_id'
    ];

    public function video()
    {
        return $this->belongsTo('App\Model\Video', 'video_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Model\Comment');
    }
}
