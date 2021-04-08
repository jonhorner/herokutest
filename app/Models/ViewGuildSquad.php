<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static whereIn(string $string, int[] $array)
 */
class ViewGuildSquad extends Model
{
    // protected $connection = 'sqlite';

    //protected $table = 'view_guild_squad';

    protected $readFrom = 'view_guild_squad';


    public function scopeLegends($query)
    {
        return $query->where('priority', 'IN', [1,2]);
    }
}
