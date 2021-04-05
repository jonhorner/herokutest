<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Models\SwGuildMember;
use App\Models\SwGuildMembersRoster;
use App\Http\Controllers\PlayerController;

use App\Http\Middleware\SwgohHelp;

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

    public function updateGuidMembers(){
    	ProcessGuild::dispatch();
    }

    public function listGuildMembersFromDB(){

    	$content = '';
		foreach ($this->getGuildMembersFromDB() as $member) {

			$displayName = $member->username === 'null' ? $member->allyCode : $member->username;
		    $content .= '<a href="/member/'.$member->allyCode.'">' . $displayName . '</a><br/>';
		}

		return $content;

    }


    function getAll()
    {
        return SwGuildMember::all();
    }

}
