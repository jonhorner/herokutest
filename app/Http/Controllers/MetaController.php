<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Revolution\Google\Sheets\Facades\Sheets;
use App\Models\SwGuildMember;
use App\Models\SwGuildMembersRoster;

class MetaController extends BaseController
{

    public const GALACTIC_LEGENDS = ['GLREY','SUPREMELEADERKYLOREN','GRANDMASTERLUKE','SITHPALPATINE'];
    public const GALACTIC_LEGENDS_KEY = ['GLREY'=>'REY',
        'SUPREMELEADERKYLOREN'=>'SLKR',
        'GRANDMASTERLUKE' => 'GML',
        'SITHPALPATINE'=>'SEE'
    ];
    public const GAS = 'GENERALSKYWALKER';
    public const JKL = 'JEDIKNIGHTLUKE';

    private $debug;
    private $sendToGoogle;

    public function __construct()
    {
        $this->setDebug(false)
             ->setSendToGoogle(false);

    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get guild members
        $members = SwGuildMember::where('active','1')->orderBy('username')->get();
//        $this->csv_content = '';

        $data= [];
        // $headers = [now()->toFormattedDateString(),'GAS','GLs','GL list','Meta Squads','Legendary Squads','Unique Legendary','Most squads','Best squads'];
        $headers = [now()->toFormattedDateString(),'GAS','JKL','GLs','GL list','Meta Squads','Legendary Squads',];
        $data[] = $headers;
        foreach ($members as $member) {

            // Do they have GAS?
            $hasGas = $this->hasGas($member->id);

            $hasJkl = $this->hasJkl($member->id);

            // Galactic legends - how many & which ones
            $hasLegends = $this->hasLegends($member->id);

            $MemberController = new MemberController();
            $MemberController->setMemberId($member->id);

            // $squads = $MemberController->getGuildMembersNonLegendarySquads($member->allyCode);
            // $legendaries = $MemberController->getGuildMembersLegendarySquads($member->allyCode);

            // Should this run on squad report and meta report?
            // $allsquads = $MemberController->getGuildMembersRelicSquads($member->allyCode);

            // if($member->username==='Coolwards'){
            //     dd($allsquads);
            // }

            // dd($member);
            // if($member->username==='Coolwards') dd($countsquads);

            $legendaries = $MemberController->getGuildMembersLegendarySquads($member->allyCode,[1,2]);

            $squads = $MemberController->getGuildMembersNonLegendarySquads($member->allyCode);



            // Get wildcard teams
            $wildcards = $MemberController->getGuildMembersLegendarySquads($member->allyCode,[3],$MemberController->standardSquads);

            $legendaries = array_merge($legendaries,$wildcards);

//
            // roster' => $this->getRelicRosterFromDB($this->member_id),
            // 'squads' => $this->getGuildMembersNonLegendarySquads($this->member_id),
            // 'legendary' => $this->getGuildMembersLegendarySquads($this->member_id,[1,2,3],$this->standardSquads),


            $collection = collect();

            foreach (array_column($squads,'members') as $members) {
                $collection = $collection->concat($members);
            }


            $uniqueLegend = 0;
            foreach ($legendaries as $legendary) {
                $intersect = $collection->intersect($legendary['members']);

                if($intersect->count() === 0){
                    $uniqueLegend++;
                }
            }

//            $this->csv_content .= $member->username.','.$hasGas.','.$hasJkl.','.$hasLegends['count'].','.$hasLegends['list'].','.count($squads).','.count($legendaries).','.$uniqueLegend."<br/>";

            $mostsquads = count($squads);
            $bestsquads = count($squads)+$uniqueLegend;

            // $userData = [$member->username,$hasGas,$hasLegends['count'],$hasLegends['list'], $mostsquads.' / '.$bestsquads,count($legendaries)];
            $userData = [$member->username,$hasGas,$hasJkl,$hasLegends['count'],$hasLegends['list'], $mostsquads,count($legendaries)];

            $data[] = $userData;
        }


        if ($this->getSendToGoogle()) {
            $this->sendToSheets($data);
        }
        return response()->json($data);
    }


    public function googleReport()
    {
        $this->setSendToGoogle(true)
            ->index();
    }


    private function sendToSheets($data): void
    {

        $sheet = Sheets::spreadsheet('1D-baQNmzJNfUBArr7fpQC7m_hPCI7KMnAITHVqJyL9c')
                    ->sheet('Overview');

        $sheet->update($data);
    }

    private function hasGas($id): string
    {
        $hasGas = SwGuildMembersRoster::where('sw_guild_member_id', $id)
                    ->where('defId','=', self::GAS)->get();

        if((int)$hasGas->count() === 1){
            return 'Yes';
        }

        return 'No';
    }


     private function hasJkl($id): string
     {
        $hasJkl = SwGuildMembersRoster::where('sw_guild_member_id', $id)
                    ->where('defId','=', self::JKL)->get();

        if((int)$hasJkl->count() === 1){
            return 'Yes';
        }

        return 'No';
    }

    private function hasLegends($id): array
    {
        $legends = SwGuildMembersRoster::where('sw_guild_member_id', $id)
                    ->whereIn('defId', self::GALACTIC_LEGENDS)->get('defId');


        if($legends->count() > 0){
            $legendArr =  $legends->pluck('defId')->toArray();
            $legendArr = array_combine($legendArr,$legendArr);

            $legendArr = array_values(array_intersect_key(self::GALACTIC_LEGENDS_KEY,$legendArr));
            $legendList = implode(',', $legendArr);
        }

        return array(
            'count' => $legends->count(),
            'list' => empty($legendList) ? 'None' : $legendList
        );
    }

    // private function getAllTeams()
    // {

    // }
    /**
     * @param mixed $sendToGoogle
     * @return MetaController
     */
    public function setSendToGoogle($sendToGoogle): MetaController
    {
        $this->sendToGoogle = $sendToGoogle;

        return $this;
    }

    /**
     * @param false $debug
     * @return MetaController
     */
    public function setDebug(bool $debug): MetaController
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSendToGoogle()
    {
        return $this->sendToGoogle;
    }

}
