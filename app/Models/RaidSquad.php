<?php


namespace App\Models;


use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class RaidSquad
 * @package App\Models
 *
 * @method static whereNotIn(string $string, int[] $array)
 * @method static whereIn(string $string, array|mixed $priority)
 * @method static find($id)
 * @method static where(string $string, mixed $get)
 * @method static has(string $string)
 */
class RaidSquad extends Model
{
    /**
     * @param $query
     * @param $type
     * @return mixed
     */
    public function scopeRancor($query, $type)
    {
        return $query->where('sw_squad_types_id', '=', $type);
    }

    public function scopeRaid($query, $raid)
    {
        return $query->where('raid_name_id', '=', $raid);
    }

    /**
     * @return HasMany
     */
    public function squads(): HasMany
    {
        return $this->hasMany(
            SwGuildSquad::class,
            'id',
            'sw_guild_squads_id'
        );
    }

    /**
     * @return HasOne
     */
    public function squadDetails(): HasOne
    {
        return $this->hasOne(
            SwGuildSquad::class,
            'id',
            'sw_guild_squads_id'
        );
    }
}
