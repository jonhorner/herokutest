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

    	try{
	    	$guilds = (new SwgohHelp)->getGuild('151869943');

	    	foreach ($guilds as $guild) {

	            $memberTotal = 0;

	             // Disable all guild members
	            // they are reactivated in save player name
	            SwGuildMember::where('active', '1')
	                  ->update(['active' => '0']);

	    		foreach ($guild['roster'] as $member) {

	                if($member['allyCode']){
	                    $PlayerController = new PlayerController($member['allyCode'], true);
	                }

	                if($PlayerController){
	                    $roster = $PlayerController->getRoster();
	                    $name = $PlayerController->getPlayerName();
	                    if($name){
	                        $member = $PlayerController->savePlayerName();
	                    }

	                    $id = $member->id;
	                    if($roster){
	                        // dd($roster);
	                        foreach ($roster as $unit) {

	                            // Relic tiers are enums- convert them to real values
	                            switch ((int)$unit['relic']['currentTier']) {
	                                case  0 :
	                                case  1 :
	                                case  2 :
	                                    $unit['relic']['currentTier'] = 0;
	                                    break;

	                                default:
	                                    $unit['relic']['currentTier'] = (int)$unit['relic']['currentTier']-2;
	                                    break;
	                            }

	                            SwGuildMembersRoster::updateOrCreate(
	                                [
	                                 'sw_guild_member_id' => $id,
	                                 'defId' => $unit['defId']
	                                ],
	                                [
	                                    'sw_guild_member_id' => $id,
	                                    'defId' => $unit['defId'],
	                                    'tier' => $unit['gear'],
	                                    'level' => $unit['level'],
	                                    'stars' => $unit['rarity'],
	                                    'relic' => $unit['relic']['currentTier'],
	                                ]
	                            );
	                        }

	                        $memberTotal++;
	                    }
	                }
	    		}
	    	}

	    	$data = [];
	    	$data['total_updated'] = $memberTotal;

	    	return  json_encode($data);

	    } catch (Exception $e){
	    	return  json_encode($e);
	    }
    }
}
