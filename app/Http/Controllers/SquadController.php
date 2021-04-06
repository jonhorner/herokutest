<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Http\Request;
use Illuminate\Routing;
use Illuminate\Support\Collection;

// use Illuminate\Session;
use App\Models\SwGuildSquad;
use App\Models\SwGuildMember;
use App\Models\SwUnitData;
use App\Models\SwUnitCategories;
use App\Models\SwGuildMembersRoster;
use App\Models\ViewGuildSquad;
use App\Http\Controllers\GuildController;
use App\Http\Controllers\MemberController;


class SquadController extends BaseController
{

    protected $charcterData;

    public function viewSquads()
    {
        return View::make('squadbuilder', [
            'content' => $this->getSquadsFromView(),
            'units' => $this->getSquadsFromView(),
        ]);
    }

    private	function getSquadsFromView(){

        $squads = ViewGuildSquad::all()->sortBy('ordering')->sortBy('priority');

    	return $squads;
    }

    private function getLegendarySquadsFromView(){

        $legends = ViewGuildSquad::whereIn('priority', [1,2])
                    ->orderBy('priority')
                    ->orderBy('ordering')
                    ->get();

        return $legends;
    }

    private function getSquads()
    {

        $squads = SwGuildSquad::all();

        return $squads;

    }

    private function generateCharacterSelect($withCategories = true)
    {
        // $characters = SwUnitData::all();
        $characters = SwUnitData::orderBy('nameKey')->characters()->get();
        $data = [];

        foreach ($characters as $character) {
           $data[$character->baseId] = $character->nameKey;
        }

        // Add unit categories
        if($withCategories){
            $categories = SwUnitCategories::orderBy('category')->get();

            foreach ($categories as $category) {
               $data['category_'.$category->category] = $category->category;
            }
        }
        return $data;
    }


    public function index()
    {

        // if($id){

        // }

        $squads = $this->getSquadsFromView();

        return View::make('squads.index', [
            'content' => '',
            'units' => $squads,
            'selectdata' => $this->generateCharacterSelect()
        ]);
    }

    public function showGuildMetaSquads()
    {
        $members = SwGuildMember::where('active','1')->orderBy('username')->get();
        // $members = SwGuildMember::where('id', 3);
        $content = '';
        $recommendedSquads = '';
        $MemberController = new MemberController;

        $data= [];
        $headers = [now()->toFormattedDateString(),'','','','',''];
        $data[] = $headers;

        foreach ($members as $member) {
            $MemberController->setMemberIdFromAllycode($member->allyCode);

            $legendaries = $MemberController->getGuildMembersLegendarySquads($member->allyCode,[1,2]);

            $squads = $MemberController->getGuildMembersNonLegendarySquads($member->allyCode);

            // Get wildcard teams
            $wildcards = $MemberController->getGuildMembersLegendarySquads($member->allyCode,[3],$MemberController->standardSquads);

            $legendaries = array_merge($legendaries,$wildcards);

            //$recommended = $MemberController->getRecommendedSquadFarms($member->allyCode);

            $content .= '<h2>'.$member->username.'</h2>';

            $data[] = [$member->username,'','','','',''];

            if(isset($squads) && !empty($squads)){
                $content .= View::make('guild.guild-squadrow-csv', [
                    'squads'   => $squads,
                ]);
                $content .= '<br/>';

                foreach ($squads as $squad) {
                    $data[] = $this->createSquadArray($squad);
                }

            }

            if(isset($legendaries) && !empty($legendaries)){

                $collection = collect();

                foreach (array_column($squads,'members') as $members) {
                    $collection = $collection->concat($members);
                }

                foreach ($legendaries as $legendary) {
                    $intersect = $collection->intersect($legendary['members']);

                    if($intersect->count() === 0){
                        $data[] = $this->createSquadArray($legendary);

                    $content .= View::make('guild.guild-squadrow-csv', [
                                    'squads'   => $legendaries,
                                ]);
                    $content .= '<br/>';
                    }
                }

            }

            // $recommendedSquads .= '<h2>'.$member->username.' (recommended)</h2>';
            // if(isset($recommended) && !empty($recommended)){

            //     $recommendedSquads .= View::make('guild.guild-squadrow-csv', [
            //         'squads'   => $recommended,
            //     ]);
            // }

            $data[] = ['','','','','',''];
        }



        $this->sendToSheets($data);

        return View::make('guild.metasquads', [
            'content'   => $content,
            'recommended' => $recommendedSquads,
        ]);

    }

    private function createSquadArray($squad)
    {
        $data = [];
        $data[] = $squad['name'];
        foreach ($squad['members'] as $member) {
            $data[] = $member->nameKey . " (G" . $member->tier ." R".$member->relic . ")";
        }

        return $data;
    }

    private function sendToSheets($data)
    {
        $sheet = Sheets::spreadsheet('1D-baQNmzJNfUBArr7fpQC7m_hPCI7KMnAITHVqJyL9c')
                    ->sheet('Squads');

        $sheet->update($data);
    }

    public function showGuildSquads()
    {
        $squads = $this->getSquads();
        // var_dump($squads);

        $relicSquads = [];

        foreach ($squads as $squad) {

            $players = $this->getPlayersWithFullSquad($squad);

            if($players){
                $relicSquads[] = [
                    'name' => $squad->name.' ('.count($players).')',
                    'members' => $players
                ];
            }

        }


        return View::make('squads.list', [
            'content' => 'content',
            'squads' => $relicSquads
        ]);

    }

    private function getPlayersWithFullSquad($squad)
    {

        $guildmembers = (new GuildController)->getGuildMembersFromDB();

        $squad = $squad->makeHidden(['name']);
        // var_dump($squad->count);

        $squadArray = [$squad->p1,$squad->p2,$squad->p3,$squad->p4,$squad->p5];
        // var_dump($squadArray);
        $squadMatches = [];

        foreach ($guildmembers as $guildmember) {
            // var_dump($guildmember->id);

            $team =  (new SwGuildMembersRoster)->relic()
                ->where('sw_guild_member_id', '=', $guildmember->id)
                ->where(function($query) use ($squad)
                {
                    $query->where('defId', '=', $squad->p1)
                          ->orWhere('defId', '=', $squad->p2)
                          ->orWhere('defId', '=', $squad->p3)
                          ->orWhere('defId', '=', $squad->p4)
                          ->orWhere('defId', '=', $squad->p5);
                })
                ->count();

            // var_dump($team);

            if ((int)$team === 5) {
                // var_dump($guildmember->username);
                $squadMatches[] = $guildmember->username;
            }

        }

        return $squadMatches;

    }

    /**
     * $return Response
     */

    public function create()
    {

    }

    /**
     * $return Response
     */

    public function store(Request $request)
    {
        // validate
        // read more on validation at http://laravel.com/docs/validation
        // $rules = array(
        //     'p1' => 'required',
        //     'p2' => 'required',
        //     'p3' => 'required'
        // );
        // $validator = Validator::make(Input::all(), $rules);

        // // process the login
        // if ($validator->fails()) {
        //     return Redirect::to('squad-builder/' . $id . '/edit')
        //         ->withErrors($validator)
        //         ->withInput(Input::except('password'));
        // } else {
            // store
            $squad = new SwGuildSquad;
            $squad->name = $request->get('name');
            $squad->p1 = $request->get('p1');
            $squad->p2 = $request->get('p2');
            $squad->p3 = $request->get('p3');
            $squad->p4 = $request->get('p4');
            $squad->p5 = $request->get('p5');
            $squad->save();

            // redirect
            // Session::flash('message', 'Successfully updated squad!');
            return Redirect::to('squad-builder');
        // }

    }

    /**
     * $return Response
     */

    public function update(Request $request, $id)
    {
        // validate
        // read more on validation at http://laravel.com/docs/validation
        // $rules = array(
        //     'p1' => 'required',
        //     'p2' => 'required',
        //     'p3' => 'required'
        // );
        // $validator = Validator::make(Input::all(), $rules);

        // // process the login
        // if ($validator->fails()) {
        //     return Redirect::to('squad-builder/' . $id . '/edit')
        //         ->withErrors($validator)
        //         ->withInput(Input::except('password'));
        // } else {
            // store
            $squad = SwGuildSquad::find($id);
            $squad->name = $request->get('name');
            $squad->p1 = $request->get('p1');
            $squad->p2 = $request->get('p2');
            $squad->p3 = $request->get('p3');
            $squad->p4 = $request->get('p4');
            $squad->p5 = $request->get('p5');
            $squad->save();

            // redirect
            // Session::flash('message', 'Successfully updated squad!');
            return Redirect::to('squad-builder');
        // }

    }

    /**
        * Show the form for editing the specified resource.
        *
        * @param  int  $id
        * @return Response
        */
    public function edit($id)
    {
        $squad = SwGuildSquad::find($id);

        return View::make('squads.edit')
            ->with('squad', $squad)
            ->with('selectdata', $this->generateCharacterSelect());
        }


    public function destroy($id)
    {
        // delete
        $squad = SwGuildSquad::find($id);
        $squad->delete();

        // redirect
        // Session::flash('message', 'Successfully deleted the shark!');
        return Redirect::to('squad-builder');
    }


}
