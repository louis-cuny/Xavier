<?php

namespace App\Model;

use Cartalyst\Sentinel\Users\EloquentUser;

class Comment extends EloquentUser
{
    protected $table = 'comment';

    protected $primaryKey = 'id';

    protected $fillable = [
        'comment',
        'sequence_id'
    ];

    public function sequence()
    {
        return $this->belongsTo('App\Model\Sequence', 'sequence_id');
    }
}
