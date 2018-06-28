<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Mots extends Model
{
    protected $table = 'mots';

    protected $primaryKey = 'id';

    protected $fillable = [
    	'type',
        'expression',
    ];

}
