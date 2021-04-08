<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static firstOrCreate(array $array)
 * @method static orderBy(string $string)
 */
class SwUnitCategories extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['category_type','category'];
}
