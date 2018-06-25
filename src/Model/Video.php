<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'video';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'link',
        'name',
        'estVisible'
    ];

    public function user()
	{
		return $this->belongsTo('App\Model\User', 'user_id');
    }
    
    public function sequences()
    {
        return $this->hasMany('App\Model\Sequence');
    }
}
