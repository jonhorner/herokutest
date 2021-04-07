<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Jobs\ProcessGuild;

class GuildController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function updateGuidMembers()
    {
    	ProcessGuild::dispatch();
    }

}
