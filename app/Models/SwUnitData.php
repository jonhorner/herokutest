<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array, array $array1)
 * @method static orderBy(string $string)
 */
class SwUnitData extends Model
{

    protected $fillable = ['thumbnailName','baseId','nameKey','combatType'];

    public function scopeShips($query)
    {
        return $query->where('combatType', '=', 2);
    }

    public function scopeCharacters($query)
    {
        return $query->where('combatType', '=', 1);
    }
}
