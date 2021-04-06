<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CodeKJ\Laravel\Traits\DateScopes\DateScopes;

class SwGuildTokens extends Model
{
	use DateScopes;

    // protected $connection = 'sqlite';

    protected $fillable = ['guild_id','allycode','total_tickets'];
}
