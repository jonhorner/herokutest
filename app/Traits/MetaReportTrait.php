<?php

namespace App\Traits;

use App\Http\Controllers\MemberController;
use App\Models\SwGuildMember;
use App\Models\SwGuildMembersRoster;
use Illuminate\Http\JsonResponse;
use Revolution\Google\Sheets\Facades\Sheets;
use App\Constants\Constants;

trait MetaReportTrait
{
    use GetterSetterTrait;

    /**
     * @param $id
     * @return string
     */
    private function hasGas($id): string
    {
        $hasGas = SwGuildMembersRoster::where('sw_guild_member_id', $id)
            ->where('defId','=', Constants::GAS)->get();

        if ((int) $hasGas->count() === 1) {
            return 'Yes';
        }

        return 'No';
    }


    /**
     * @param $id
     * @return string
     */
    private function hasJkl($id): string
    {
        $hasJkl = SwGuildMembersRoster::where('sw_guild_member_id', $id)
            ->where('defId','=', Constants::JKL)->get();

        if((int)$hasJkl->count() === 1){
            return 'Yes';
        }

        return 'No';
    }

    /**
     * @param $id
     * @return array
     */
    private function hasLegends($id): array
    {
        $legends = SwGuildMembersRoster::where('sw_guild_member_id', $id)
            ->whereIn('defId', Constants::GALACTIC_LEGENDS)->get('defId');

        if($legends->count() > 0){
            $legendArr =  $legends->pluck('defId')->toArray();
            $legendArr = array_combine($legendArr,$legendArr);

            $legendArr = array_values(array_intersect_key(Constants::GALACTIC_LEGENDS_KEY,$legendArr));
            $legendList = implode(',', $legendArr);
        }

        return array(
            'count' => $legends->count(),
            'list' => empty($legendList) ? 'None' : $legendList
        );
    }


    /**
     * @param $data
     */
    public function sendToSheets($data): void
    {
        $sheet = Sheets::spreadsheet('1D-baQNmzJNfUBArr7fpQC7m_hPCI7KMnAITHVqJyL9c')
            ->sheet('Overview');

        $sheet->update($data);
    }

    /**
     * Create a new job instance.
     *
     * @param bool $debug
     * @param bool $useKeys
     * @param bool $isCron
     * @param bool $sendToGoogle
     */
    public function __construct(
        Bool $debug = false,
        Bool $useKeys = false,
        Bool $isCron = false,
        Bool $sendToGoogle = true
    )
    {
        $this->setDebug($debug)
            ->setUseKeys($useKeys)
            ->setIsCron($isCron)
            ->setSendToGoogle($sendToGoogle);
    }


    /**
     * Execute the job.
     */
    public function submitDataToServer(): ?JsonResponse
    {
        // Get guild members
        try {
            $members = SwGuildMember::where('active','1')
                ->orderBy('username')
                ->get();

            if (empty($members->count())) {
                throw new \Exception('No guild members found',400);
            }
        } catch (\Exception $e) {
            $return = [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];

            return response()->json($return);
        }

        $data=[];
        if ($this->getUseKeys() !== true){
            $headers = [
                now()->toFormattedDateString(),
                'GAS',
                'JKL',
                'GLs',
                'GL list',
                'Meta Squads',
                'Legendary Squads'
            ];
        } else {
            $headers = [
                'username' => now()->toFormattedDateString(),
                'gas' => 'GAS',
                'jkl' => 'JKL',
                'legendCount' =>'GLs',
                'legendList' => 'GL list',
                'mostsquads' => 'Meta Squads',
                'legendaryCount' => 'Legendary Squads'
            ];
        }

        $data[] = $headers;

        foreach ($members as $member) {
            // Do they have GAS?
            $hasGas = $this->hasGas($member->id);
            $hasJkl = $this->hasJkl($member->id);

            // Galactic legends - how many & which ones
            $hasLegends = $this->hasLegends($member->id);

            $memberController = new MemberController();
            $memberController->setMemberId($member->id);

            $legendaries = $memberController->getGuildMembersLegendarySquads($member->allyCode,[1,2]);
            $squads = $memberController->getGuildMembersNonLegendarySquads($member->allyCode);

            // Get wildcard teams
            $wildcards = $memberController->getGuildMembersLegendarySquads($member->allyCode,[3],$memberController->standardSquads);

            $legendaries = array_merge($legendaries,$wildcards);

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

            $mostsquads = count($squads);

            if ($this->getUseKeys() !== true){
                $userData = [
                    $member->username,
                    $hasGas,
                    $hasJkl,
                    $hasLegends['count'],
                    $hasLegends['list'],
                    $mostsquads,
                    count($legendaries)
                ];
            } else {
                $userData = [
                    'username' => $member->username,
                    'gas' => $hasGas,
                    'jkl' => $hasJkl,
                    'legendCount' => $hasLegends['count'],
                    'legendList' => $hasLegends['list'],
                    'mostsquads' => $mostsquads,
                    'legendaryCount' => count($legendaries)
                ];
            }

            $data[] = $userData;
        }

        if ($this->getSendToGoogle()) {
            $sheetData = $this->getUseKeys() === true ? array_values($data) : $data;
            $this->sendToSheets(
                array_values($sheetData)
            );
        }

        if (!$this->getIsCron()){
            return response()->json($data);
        }

        return null;
    }
}
