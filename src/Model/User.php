<?php

namespace App\Model;

use Cartalyst\Sentinel\Users\EloquentUser;

class User extends EloquentUser
{
    protected $table = 'user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'permissions',
    ];

    protected $loginNames = ['username', 'email'];

    public function video()
    {
        return $this->hasMany('App\Model\Video');
    }
}
