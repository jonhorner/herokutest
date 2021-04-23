<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Http\Request;


use App\Models\SwGuildSquad;
use App\Models\SwGuildMember;
use App\Models\SwUnitData;
use App\Models\SwUnitCategories;
use App\Models\SwGuildMembersRoster;
use App\Models\ViewGuildSquad;


class SquadController extends BaseController
{

    protected $charcterData;
    protected $squads;
    protected $submitToGoogle;
    protected $isCron;

    /**
     * SquadController constructor.
     * @param $submitToGoogle
     */
    public function __construct()
    {
        $this->setSubmitToGoogle(false)
            ->setIsCron(false);
    }


    /**
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
//        $squads = SwGuildSquad::all();
        $squads = ViewGuildSquad::all()
            ->sortBy('ordering')
            ->sortBy('priority');

        return response()->json($squads);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function getOne($id): JsonResponse
    {
        try {
            $squad = SwGuildSquad::where('id', $id)->first();
        } catch (\Throwable $e){
            return response()->json($e);
        }

        if (!empty($squad)) {
            return response()->json($squad);
        }

        return response()->json(
            [
                ['status' => 'Error'],
                ['message' => 'Could not fnd team']
            ]
        );
    }

    public function viewSquads()
    {
        return View::make('squadbuilder', [
            'content' => $this->getSquadsFromView(),
            'units' => $this->getSquadsFromView(),
        ]);
    }

    private	function getSquadsFromView(){

        $squads = ViewGuildSquad::all()
            ->sortBy('ordering')
            ->sortBy('priority');

    	return $squads;
    }

    /**
     * @return mixed
     */
    public function getSubmitToGoogle()
    {
        return $this->submitToGoogle;
    }

    /**
     * @param mixed $submitToGoogle
     * @return SquadController
     */
    public function setSubmitToGoogle($submitToGoogle): SquadController
    {
        $this->submitToGoogle = $submitToGoogle;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsCron()
    {
        return $this->isCron;
    }

    /**
     * @param mixed $isCron
     * @return SquadController
     */
    public function setIsCron($isCron): SquadController
    {
        $this->isCron = $isCron;

        return $this;
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

    /**
     * @param bool $withCategories
     * @return array
     */
    private function generateCharacterSelect($withCategories = true): array
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


    /**
     * @return View
     */
    public function index(): View
    {
        $squads = $this->getSquadsFromView();

        return View::make(
            'squads.index',
            [
                'content' => '',
                'units' => $squads,
                'selectdata' => $this->generateCharacterSelect()
            ]
        );
    }


    /**
     * @return JsonResponse
     */
    public function submitGuildMetaSquadsToGoogle(): JsonResponse
    {
        return $this->setSubmitToGoogle(true)
            ->getGuildMetaSquads();
    }

    /**
     * @return JsonResponse
     */
    public function submitGuildMetaSquadsToGoogleCron(): JsonResponse
    {
        return $this->setSubmitToGoogle(true)
            ->setIsCron(true)
            ->getGuildMetaSquads();
    }



    /**
     * @return JsonResponse
     */
    public function returnGuildMetaSquads(): JsonResponse
    {
        return $this->setSubmitToGoogle(false)
            ->getGuildMetaSquads();
    }

    /**
     * @return JsonResponse|null
     */
    public function getGuildMetaSquads(): ?JsonResponse
    {
        $members = SwGuildMember::where('active','1')
            ->orderBy('username')
            ->get();
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

//                    $content .= View::make('guild.guild-squadrow-csv', [
//                                    'squads'   => $legendaries,
//                                ]);
//                    $content .= '<br/>';
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

        if ($this->getSubmitToGoogle() === true){
            $this->sendToSheets($data);
        }

        if ($this->getIsCron() === true){
        return response()->json();
    }

        return null;
    }

    /**
     * @param $squad
     * @return array
     */
    private function createSquadArray($squad): array
    {
        $data = [];
        $data[] = $squad['name'];
        foreach ($squad['members'] as $member) {
            $data[] = $member->nameKey . " (G" . $member->tier ." R".$member->relic . ")";
        }

        return $data;
    }

    /**
     * @param $data
     */
    private function sendToSheets($data): void
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

    /**
     * @param $squad
     * @return array
     */
    private function getPlayersWithFullSquad($squad): array
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
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
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
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
    public function edit($id): Response
    {
        $squad = SwGuildSquad::find($id);

        return View::make('squads.edit')
            ->with('squad', $squad)
            ->with('selectdata', $this->generateCharacterSelect());
        }


    /**
     * @param $id
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        // delete
        $squad = SwGuildSquad::find($id);
        $squad->delete();

        // redirect
        // Session::flash('message', 'Successfully deleted the shark!');
        return Redirect::to('squad-builder');
    }


}
