<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array, array $array1)
 */
class SwUnitCategoryXrefs extends Model
{
    // protected $connection = 'sqlite';

    protected $fillable = ['sw_unit_categories_id','sw_unit_data_id'];
}
