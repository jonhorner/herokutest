<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\SwGuildMember;
use App\Models\SwGuildMembersRoster;
use App\Http\Controllers\PlayerController;
use App\Http\Middleware\SwgohHelp;


class ProcessGuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    //public $uniqueFor = 3600;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
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
                                $tierLevel = $unit['relic']['currentTier'] ??  0;

                                switch ((int) $tierLevel) {
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
    }
}
