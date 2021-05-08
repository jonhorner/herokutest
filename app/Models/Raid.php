<?php


namespace App\Models;


use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Raid
 * @package App\Models
 *
 * @method static whereNotIn(string $string, int[] $array)
 * @method static whereIn(string $string, array|mixed $priority)
 * @method static find($id)
 * @method static where(string $string, mixed $get)
 */
class Raid extends Model
{
    protected $guarded = ['id'];

    /**
     * @return HasMany
     */
    public function squads(): HasMany
    {
        return $this->hasMany(SwGuildSquad::class);
    }
}
