<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
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

	public function showMember($allycode): View
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


    /**
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        $members =  SwGuildMember::where('active','1')
            ->orderBy('username')
            ->get();

        return response()->json($members);
    }

    /**
     * @param $allycode
     * @return JsonResponse
     */
    public function getMember($allycode): JsonResponse
     {
        $member =  SwGuildMember::where('active','1')
                                ->where('allycode','=', $allycode)
                                ->first();

        return response()->json($member);
    }

    /**
     * @param $allycode
     * @return string|null
     */
    public function getPlayerNameFromDB($allycode): ?string
    {
        $user = SwGuildMember::where('allyCode', $allycode)->first();
        return $user->username;
    }


    public function getRosterFromDB($id){
        $roster =  SwGuildMembersRoster::where('sw_guild_member_id', $id)
                    ->join('sw_unit_data', 'sw_unit_data.baseId', '=', 'sw_guild_members_rosters.defId')
                    ->get();
        return $roster;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRelicRosterFromDB($id)
    {
        $roster =  SwGuildMembersRoster::where('sw_guild_member_id', $id)->relic()->get();
        return $roster;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getMetaRosterFromDB($id)
    {
        $roster =  SwGuildMembersRoster::where('sw_guild_member_id', $id)
                        ->mingear()->fullstars()->metalevel()
                        ->join('sw_unit_data', 'sw_unit_data.baseId', '=', 'sw_guild_members_rosters.defId')
                        ->get();

        return $roster;
    }


    /**
     * @param $id
     * @return $this
     */
    public function setMemberId($id): MemberController
    {
        $this->member_id = $id;

        return $this;
    }

    /**
     * @param $allycode
     * @return $this
     */
    public function setMemberIdFromAllycode($allycode): MemberController
    {
    	$user = SwGuildMember::where('allyCode', $allycode)->first();
    	$this->member_id = $user->id;

    	return $this;
    }

    public function getGuildMembersRelicSquads($allycode)
    {

        $squads = SwGuildSquad::ofType(Constants::ID_META_SQUAD)
            ->orderBy('priority')
            ->orderBy('ordering')
            ->get();
        $roster = $this->getMetaRosterFromDB($this->member_id);

        [$squads, $removed] = $this->selectUniqueSquads($squads, $roster);

        return $squads;
    }

    /**
     * @param $allycode
     * @return mixed
     */
    public function getGuildMembersNonLegendarySquads($allycode)
    {

        $squads = SwGuildSquad::ofType(Constants::ID_META_SQUAD)
                    ->whereNotIn('priority',[1,2,3])
                    ->orderBy('priority')
                    ->orderBy('ordering')
                    ->get();

        $roster = $this->getMetaRosterFromDB($this->member_id);
        $this->removedToons = collect();
        // $this->standardSquads = collect();

        [$squads, $removed] = $this->selectUniqueSquads($squads, $roster);

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


    /**
     * @param $allycode
     * @param array $priority
     * @param null $exclude
     * @return mixed
     */
    public function getGuildMembersLegendarySquads($allycode, $priority=[], $exclude=null)
    {

        if(!$priority){
            dd('no priority set');
        }

        $squads = SwGuildSquad::ofType(Constants::ID_META_SQUAD)
                    ->whereIn('priority', $priority)
                    ->orderBy('priority')
                    ->orderBy('ordering')
                    ->get();

        $roster = $this->getMetaRosterFromDB($this->member_id);

        if($exclude){
            $roster = $roster->diff($exclude);
        }

        [$squads, $removed] = $this->selectUniqueSquads($squads, $roster);

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

    /**
     * @param $squads
     * @param $roster
     * @param int $minGearLevel
     * @param null $minToons
     * @param null $collections
     * @return array
     */
    private function selectSquads($squads, $roster, $minGearLevel=13, $minToons=null, $collections=null): array
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
                return in_array($item->defId, $team, true);
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

    /**
     * @param $squads
     * @param $roster
     * @param int $minGearLevel
     * @param null $minToons
     * @return array
     */
    private function selectUniqueSquads(
            $squads,
            $roster,
            $minGearLevel=13,
            $minToons=null
        ): array {

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

            $wildcards = $this->hasCategories($team);
            if($wildcards){
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
                        return !in_array($item->defId, $team, true);
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

    /**
     * @param $wildcards
     * @param $roster
     * @param $minGearLevel
     * @return mixed
     */
    private function getWildcardToons($wildcards, $roster, $minGearLevel)
    {
        // Filter by category
        $toons = $roster->filter(function($item) use ($wildcards){
            return count(array_intersect(explode(',',$item->categories), $wildcards)) > 0;
        });

        // Get correct gear level
        $wildcardToons = $toons->filter(function($item) use ($wildcards, $minGearLevel){
            return (int)$item->tier >= (int)$minGearLevel;
        });

        $selectedToons = $wildcardToons->slice(0, count($wildcards));

        $this->removedToons = $this->removedToons->concat($selectedToons);

        //@todo - order these by GP
        return $selectedToons;
    }

    /**
     * @param $team
     * @return array
     */
    private function hasCategories($team): array
    {
        $wildcards = [];
        foreach ($team as $key => $value) {
            if(strpos($value, self::CATEGORY_STRING) !== false) {
                $wildcards[] = str_replace(self::CATEGORY_STRING, '', $value);
            }
        }
        return $wildcards;
    }


    /**
     * @param $allycode
     * @return mixed
     */
    public function getGuildMembersNonMetaSquadToons($allycode)
    {
        // Get all toons
        $roster = $this->getRosterFromDB($this->member_id);

        if(!$this->removedToons) $this->removedToons = collect();
        if($this->removedToons->count()>0){
            $usedToons = $this->removedToons->pluck('defId');
            $usedToons = $usedToons->toArray();

            // Remove toons which are already in the squad list
            [$roster, $remove] = $roster->partition(
                function ($item) use ($usedToons) {
                    return !in_array($item->defId, $usedToons, true);
                }
            );
        }
        return $roster;
    }

    public function getRecommendedSquadFarms($allycode)
    {
        $squads = SwGuildSquad::ofType(Constants::ID_META_SQUAD)
            ->orderBy('priority')
            ->orderBy('ordering')
            ->get();
        $roster = $this->getGuildMembersNonMetaSquadToons($allycode);

        return $this->selectSquads($squads, $roster, $minGearLevel=13, $minToons=3);
    }
}
