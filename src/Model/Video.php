<?php

namespace App\Model;

use Cartalyst\Sentinel\Users\EloquentUser;

class Video extends EloquentUser
{
    protected $table = 'video';

    protected $primaryKey = 'id';

    protected $fillable = [
        'link'
    ];
}
