<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $id)
 * @method relic()
 * @method crancor()
 */
class SwGuildMembersRoster extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['sw_guild_member_id', 'defId', 'relic', 'tier', 'stars', 'level', 'gp', 'skills'];


    public function scopeRelic($query)
    {
        return $query->where('tier', '=', 13);
    }

    public function scopeCrancor($query)
    {
        return $query->where('relic', '>=', 5);
    }

    public function scopeMingear($query)
    {
        return $query->where('tier', '>=', 8);
    }

    public function scopeFullstars($query)
    {
        return $query->where('stars', '=', 7);
    }

    public function scopeMetalevel($query)
    {
        return $query->where('level', '=', 85);
    }

    // public function scopeMetaReady($query)
    // {
    // 	$query->where('level', '=', 85);
    // 	$query->where('stars', '=', 7);
    // 	$query->where('tier', '>=', 12);
    // }
}
