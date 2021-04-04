<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwUnitCategories extends Model
{
    protected $connection = 'sqlite';

    protected $fillable = ['category_type','category'];
}
