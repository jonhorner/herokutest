<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class GuildController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function showGuild()
    {

    	$sampleData = [];
    	$sampleData[] = [ 'id' => 1, 'name' => 'username' ];
    	$sampleData[] = [ 'id' => 2, 'name' => 'username2' ];

        return json_encode($sampleData);
    }
}
