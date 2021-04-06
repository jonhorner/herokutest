<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;
use App\Models\SwGuildMember;
use App\Models\SwGuildMembersRoster;
use App\Models\SwGuildSquad;

class MemberController extends BaseController
{

	protected $member_id;
	protected $allycode;
    protected $requiredForFull = 5;
    protected $minRelics = 4;
    protected $removedToons;
    public $standardSquads;
    protected const CATEGORY_STRING = 'category_';

    public function __construct()
    {
        $this->removedToons = collect();
    }

	public function showMember($allycode)
    {

    	$this->setMemberIdFromAllycode($allycode);

        return View::make('member.overview', [
            'roster' => $this->getRelicRosterFromDB($this->member_id),
            'squads' => $this->getGuildMembersNonLegendarySquads($this->member_id),
            'legendary' => $this->getGuildMembersLegendarySquads($this->member_id,[1,2,3],$this->standardSquads),
            'recommended' => $this->getGuildMembersNonMetaSquadToons($this->member_id),
            'name' => $this->getPlayerNameFromDB($allycode),
            'allycode' => $allycode
        ]);
    }

	public function getPlayerNameFromDB($allycode)
    {
        $user = SwGuildMember::where('allyCode',$allycode)->first();
        return $user->username;
    }


    public function getRosterFromDB($id){
        $roster =  SwGuildMembersRoster::where('sw_guild_member_id', $id)
                    ->join('sw_unit_data', 'sw_unit_data.baseId', '=', 'sw_guild_members_rosters.defId')
                    ->get();
        return $roster;
    }

    public function getRelicRosterFromDB($id){
        $roster =  SwGuildMembersRoster::where('sw_guild_member_id', $id)->relic()->get();
        return $roster;
    }

    public function getMetaRosterFromDB($id){
        $roster =  SwGuildMembersRoster::where('sw_guild_member_id', $id)
                        ->mingear()->fullstars()->metalevel()
                        ->join('sw_unit_data', 'sw_unit_data.baseId', '=', 'sw_guild_members_rosters.defId')
                        ->get();

        return $roster;
    }


    public function setMemberId($id)
    {
        $this->member_id = $id;
    }

    public function setMemberIdFromAllycode($allycode)
    {
    	$user = SwGuildMember::where('allyCode', $allycode)->first();
    	$this->member_id = $user->id;
    }

    public function getGuildMembersRelicSquads($allycode)
    {

        $squads = SwGuildSquad::orderBy('priority')->orderBy('ordering')->get();
        $roster = $this->getMetaRosterFromDB($this->member_id);

        list($squads,$removed) = $this->selectUniqueSquads($squads, $roster);

        return $squads;
    }

    public function getGuildMembersNonLegendarySquads($allycode)
    {

        $squads = SwGuildSquad::whereNotIn('priority',[1,2,3])
                    ->orderBy('priority')
                    ->orderBy('ordering')
                    ->get();

        $roster = $this->getMetaRosterFromDB($this->member_id);
        $this->removedToons = collect();
        // $this->standardSquads = collect();

        list($squads,$removed) = $this->selectUniqueSquads($squads, $roster);

        $this->standardSquads = $removed;

        return $squads;
    }

    // public function getGuildMembersLegendarySquads($allycode)
    // {

    //     $squads = SwGuildSquad::whereIn('priority',[1,2,3])
    //                 ->orderBy('priority')
    //                 ->orderBy('ordering')
    //                 ->get();

    //     $roster = $this->getMetaRosterFromDB($this->member_id);
    //     $this->removedToons = collect();

    //     list($squads,$removed) = $this->selectUniqueSquads($squads, $roster);
    //     // dd($removed);
    //     return $squads;
    // }


     public function getGuildMembersLegendarySquads($allycode, $priority=[], $exclude=null)
    {

        if(!$priority){
            dd('no priority set');
        }

        $squads = SwGuildSquad::whereIn('priority',$priority)
                    ->orderBy('priority')
                    ->orderBy('ordering')
                    ->get();

        $roster = $this->getMetaRosterFromDB($this->member_id);

        // dd($roster);
        if($exclude){
            $roster = $roster->diff($exclude);
        }

        list($squads,$removed) = $this->selectUniqueSquads($squads, $roster);
        // dd($removed);
        return $squads;
    }


    // public function getGuildMembersSeeLegendarySquads($allycode)
    // {

    //     $squads = SwGuildSquad::whereIn('priority',[3])
    //                 ->orderBy('priority')
    //                 ->orderBy('ordering')
    //                 ->get();

    //     $roster = $this->getMetaRosterFromDB($this->member_id);
    //     $this->removedToons = collect();

    //     return $this->selectUniqueSquads($squads, $roster);
    // }

    private function selectSquads($squads, $roster, $minGearLevel=13, $minToons=null, $collections=null)
    {

        if(is_null($minToons)){
            $minToons = $this->requiredForFull;
        }

        $userSquads = [];

        foreach ($squads as $squad) {

            $team = [
                $squad->p1,
                $squad->p2,
                $squad->p3,
                $squad->p4,
                $squad->p5
            ];

            $toons = $roster->filter(function($item) use ($team){
                return in_array($item->defId, $team);
            });

            // Order toons correctly
            $toons = $toons->sortBy(function($model) use ($team){
                return array_search($model->defId, $team);
            });


            // Do we have the minimum relic characters required
            if((int)$toons->count() >= $minToons){

                // Check the toons meet the min gear requirement
                $relic = $toons->filter(function($item) use ($minGearLevel){
                    return (int)$item->tier >= (int)$minGearLevel;
                });



                if($relic->count() >= $this->minRelics){

                    $userSquads[$squad->id]['name'] = $squad->name;
                    $userSquads[$squad->id]['members'] = $toons;

                }
            }
        }

        return  $userSquads;
    }

    private function selectUniqueSquads(
            $squads,
            $roster,
            $minGearLevel=13,
            $minToons=null
        )
    {

        if(is_null($minToons)){
            $minToons = $this->requiredForFull;
        }

        $userSquads = [];
        foreach ($squads as $squad) {

            // Does the squad contain a wild card - process these last so we don't cannibalise
            // other teams


            $team = [
                $squad->p1,
                $squad->p2,
                $squad->p3,
                $squad->p4,
                $squad->p5
            ];


            $toons = $roster->filter(function($item) use ($team){
                return in_array($item->defId, $team);
            });

            // Order toons correctly
            $toons = $toons->sortBy(function($model) use ($team){
                return array_search($model->defId, $team);
            });

            // var_dump($team);
            $wildcards = $this->hasCategories($team);
            if($wildcards){
                // dd($wildcards);
                $minToons = $minToons - count($wildcards);
            }

            // dd(array_intersect(
            //     array_map('strtolower', array('category_')
            //     ), $team)
            // );

            // // @todo - add handling of wildcards here
            // if(0 < count(array_intersect(array_map('strtolower', array('category_')), $team)))
            //     {
            //         dd($team);
            //     }

            // Do we have the minimum relic characters required
            if((int)$toons->count() >= $minToons){

                // Check the toons meet the min gear requirement
                $relic = $toons->filter(function($item) use ($minGearLevel){
                    return (int)$item->tier >= (int)$minGearLevel;
                });



                if($relic->count() >= $this->minRelics){

                    // If we've matched the squad remove the characters from the collection
                    // so we don't match them in another team
                    list($roster, $remove) = $roster->partition(function ($item) use ($team){
                        return !in_array($item->defId, $team);
                    });


                    $this->removedToons = $this->removedToons->concat($remove);

                    // Check for wild card toons and add
                    if($wildcards){
                        $wildcardToons = $this->getWildcardToons($wildcards, $roster, $minGearLevel);

                        $toons = $toons->concat($wildcardToons);

                    }

                    $userSquads[$squad->id]['name'] = $squad->name;
                    $userSquads[$squad->id]['members'] = $toons;
                }
            }
        }

        return array($userSquads, $this->removedToons);
    }

    private function getWildcardToons($wildcards, $roster, $minGearLevel)
    {
        // dd($wildcards);
        // Filter by category
        $toons = $roster->filter(function($item) use ($wildcards){
            // dd(explode(',',$item->categories));
            // dd(array_intersect(explode(',',$item->categories), array(19)));
            return count(array_intersect(explode(',',$item->categories), $wildcards)) > 0;
        });

        // Get correct gear level
        $wildcardToons = $toons->filter(function($item) use ($wildcards, $minGearLevel){
            return (int)$item->tier >= (int)$minGearLevel;
        });

        $selectedToons = $wildcardToons->slice(0, count($wildcards));

        $this->removedToons = $this->removedToons->concat($selectedToons);

        //@todo - order thes by GP
        return $selectedToons;
    }

    private function hasCategories($team)
    {
        $wildcards = [];
        foreach ($team as $key => $value) {
            if(strpos($value, self::CATEGORY_STRING) !== false) {
                $wildcards[] = str_replace(self::CATEGORY_STRING, '', $value);
            }
        }
        return $wildcards;
    }


    public function getGuildMembersNonMetaSquadToons($allycode)
    {
        // Get all toons
        $roster = $this->getRosterFromDB($this->member_id);

        if(!$this->removedToons) $this->removedToons = collect();
        if($this->removedToons->count()>0){
            $usedToons = $this->removedToons->pluck('defId');
            $usedToons = $usedToons->toArray();

            // Remove toons which are already in the squad list
            list($roster, $remove) = $roster->partition(function ($item) use ($usedToons){
                return !in_array($item->defId, $usedToons);
            });
        }
        return $roster;
    }

    public function getRecommendedSquadFarms($allycode)
    {
        $squads = SwGuildSquad::orderBy('priority')->orderBy('ordering')->get();
        $roster = $this->getGuildMembersNonMetaSquadToons($allycode);

        return $this->selectSquads($squads, $roster, $minGearLevel=13, $minToons=3);
    }
}
