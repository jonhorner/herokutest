<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array, array $array1)
 */
class SwUnitData extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['thumbnailName','baseId','nameKey','combatType'];

    public $timestamps = false;

    public function scopeShips($query)
    {
        return $query->where('combatType', '=', 2);
    }

    public function scopeCharacters($query)
    {
        return $query->where('combatType', '=', 1);
    }
}
