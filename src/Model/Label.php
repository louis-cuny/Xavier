<?php

namespace App\Model;

use Cartalyst\Sentinel\Users\EloquentUser;

class Label extends EloquentUser
{
    protected $table = 'label';

    protected $primaryKey = 'id';

    protected $fillable = [
        'expression',
    ];

    public function sequences()
    {
        return $this->hasMany('App\Model\Sequence');
    }
}
