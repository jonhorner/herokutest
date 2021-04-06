<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwGuildMember extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['allyCode','username','active'];

    public function units()
    {
    	return $this->hasMany('App\Unit');
    }
}
