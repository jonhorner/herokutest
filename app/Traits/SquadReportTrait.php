<?php

namespace App\Traits;

use App\Constants\Constants;
use App\Http\Controllers\MemberController;
use App\Models\SwGuildMember;
use Illuminate\Http\JsonResponse;
use Revolution\Google\Sheets\Facades\Sheets;

trait SquadReportTrait
{
    use GetterSetterTrait;

    /**
     * @return JsonResponse|null
     */
    public function getGuildMetaSquads(): ?JsonResponse
    {
        $members = SwGuildMember::where('active','1')
            ->orderBy('username')
            ->get();

        $content = '';
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
                    }
                }
            }

            $data[] = ['','','','','',''];
        }

        if ($this->getSendToGoogle() === true) {
            $this->sendToSheets($data);
        }

        if (!$this->getIsCron() === true) {
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
        $sheet = Sheets::spreadsheet(Constants::ID_GOOGLE_SHEET)
            ->sheet(Constants::NAME_SQUAD_SHEET);

        $sheet->update($data);
    }
}
