<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;
use App\Models\SwGuildMember;
use App\Models\SwGuildMembersRoster;

use App\Http\Middleware\SwgohHelp;

class PlayerController extends BaseController
{

    protected $player;
    protected $member_id;

    // protected $metaSquads = [
    //     ['name' => 'Drevan', 'units' => ['DARTHREVAN','BASTILASHANDARK','DARTHMALAK']],
    //     ['name' => 'Padme', 'units' => ['PADMEAMIDALA','AHSOKATANO','GENERALKENOBI']],
    // ];

    public function __construct($id='', $api = false)
    {

        if($api){
            $this->player = (new SwgohHelp)->getPlayer($id)[0];
        }

        // try {
        //      $this->member_id = SwGuildMember::where('allyCode', $id)->first()->id;

        //     $member = SwGuildMember::firstOrCreate(
        //         ['allyCode' => $id],
        //         ['delayed' => 1, 'arrival_time' => '11:30']
        //     );

        // } catch (Exception $e) {

        // }



    }

    public function showPlayer($id)
    {
        return View::make('player', [
            'roster' => $this->getRosterFromDB($id),
            'name' => $this->getPlayerNameFromDB($id)
        ]);
    }

     public function getPlayerNameFromDB($id) : string
    {
        $user = SwGuildMember::where('allyCode',$id)->first();
        return $user->username;
    }

    public function getPlayerName()
    {
        return $this->player['name'];
    }

    public function getPlayerId()
    {
        return $this->member_id;
    }


     public function getRosterFromDB(){
        return SwGuildMembersRoster::where('sw_guid_member_id', $this->member_id)->get();
    }

    public function getRoster(){
        return $this->player['roster'];
    }

    public function getRelicSquads(){
        $roster = [];
        foreach ($this->player['roster'] as $unit) {
            if((int)$unit['relic']['currentTier'] > 0){
                $roster[] = $unit['defId'];
            }
        }
        // return $this->checkMetaSquad($roster);
    }


    public function savePlayerName()
    {
        $member = SwGuildMember::updateOrCreate(
            [ 'allyCode' => $this->player['allyCode'] ],
            [
                'username' => $this->player['name'],
                'allyCode' => $this->player['allyCode'],
                'active' => '1'
            ]
        );

        return $member;
    }



    // private function checkMetaSquad($roster)
    // {
    //     $squads = [];

    //     foreach ($this->metaSquads as $squad) {
    //         $containsSearch = count(array_intersect($squad['units'], $roster)) == count($squad['units']);

    //         if($containsSearch){
    //            $squads[] = $squad['name'];
    //         }
    //     }
    //     return $squads;
    // }
}
