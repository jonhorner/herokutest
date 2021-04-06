<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwGuildSquad extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['name','p1','p2','p3','p4','p5','ordering','priority'];

}
