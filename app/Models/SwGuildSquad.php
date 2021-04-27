<?php

namespace App\Models;

use App\Constants\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static whereNotIn(string $string, int[] $array)
 * @method static whereIn(string $string, array|mixed $priority)
 * @method static find($id)
 * @method static where(string $string, mixed $get)
 * @method static ofType(int $type)
 */
class SwGuildSquad extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['name','p1','p2','p3','p4','p5','ordering','priority'];

    protected $casts = [
        'updated_at' => 'datetime:d-m-Y',
    ];

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(SwSquadType::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeMetaSquad($query)
    {
        return $query->where('sw_squad_types_id', '=', Constants::ID_META_SQUAD);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCrancorSquad($query)
    {
        return $query->where('sw_squad_types_id', '=', Constants::ID_CRANCOR_SQUAD);
    }

    /**
     * @param $query
     * @param $type
     * @return mixed
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('sw_squad_types_id', '=', $type);
    }
}
