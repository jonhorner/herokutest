<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static whereNotIn(string $string, int[] $array)
 * @method static whereIn(string $string, array|mixed $priority)
 * @method static find($id)
 * @method static where(string $string, mixed $get)
 */
class SwGuildSquad extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['name','p1','p2','p3','p4','p5','ordering','priority'];

    protected $casts = [
        'updated_at' => 'datetime:d-m-Y',
    ];

}
