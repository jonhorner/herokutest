<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, string $string1)
 */
class SwGuildMember extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['allyCode','username','active'];

    public function units()
    {
    	return $this->hasMany('App\Unit');
    }
}
