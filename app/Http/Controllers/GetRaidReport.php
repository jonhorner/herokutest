<?php


namespace App\Http\Controllers;


use App\Models\Raid;
use App\Models\RaidSquad;
use App\Models\SwGuildMembersRoster;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;


class GetRaidReport extends BaseController
{

    private $phaseHealth;
    private $squadController;

    public function __construct()
    {
        $this->squadController = new SquadController();
    }

    /**
     * @param $squad
     * @param $count
     * @return Int
     */
    public function calculatePotentialDamageForSquad($squad, $count): int
    {
        return $count * $squad->damage;
    }

    /**
     * @param string $returnAs
     * @param string $groupBy DB column to group results on
     * @return JsonResponse|RaidSquad
     */
    public function getCrancorSquads(string $returnAs = '', string $groupBy = '')
    {
        $squads = RaidSquad::with('squads')
            ->where('raid_name_id', '=', '5')
            ->orderBy('phase')->get();

        if ($groupBy !== '') {
            $squads = $squads->groupBy($groupBy);
        }

        if (strtolower($returnAs) === 'json') {
            return response()->json($squads);
        }

        return $squads;
    }

    public function getPlayersWithCrancorSquadsByPhase(): array
    {
        $phases = $this->getCrancorSquads('collection', 'phase');
        $raidData = [];
        foreach ($phases as $phase) {
            $phaseData = [];
            foreach ($phase as $squad) {
                $item = $squad->squadDetails;

                $squadData = $this->squadController->getPlayersWithFullSquad($item, 'crancor');

                $health = $this->getPhaseHealth() ?? $this->getDamageRequired($squad);
                $required = $health > 0;

                $phaseDamageForTeam = $this->calculatePotentialDamageForSquad($squad, count($squadData));

                $this->setPhaseHealth(
                    $this->calculateRemainingPhaseHealth($phaseDamageForTeam, $health)
                );

                // Set the data for the squad run
                $endOfSquadRunHealthData = [
                    'total_phase_health' => $this->getDamageRequired($squad),
                    'damage_by_team' => $phaseDamageForTeam,
                    'health_remaining' => $this->getPhaseHealth(),
                    'required' => $required
                ];

                $phaseData[$item->name] = [
                    'end_of_run_data' => $endOfSquadRunHealthData,
                    'alliance_members' => $squadData
                ];
                $phaseNo = $squad->phase;
            }

            // Set the data for the phase
            $raidData[$phaseNo] = [
                'total_health' => $this->phaseHealth,
                'phase_data' => $phaseData
            ];

            $this->phaseHealth = null;
            $health = null;
        }
        return $raidData;
    }

    public function countCrancorMembers($squad, $guildmember)
    {
        return (new SwGuildMembersRoster)->crancor()
            ->where('sw_guild_member_id', '=', $guildmember->id)
            ->where(
                function ($query) use ($squad) {
                    $query->where('defId', '=', $squad->p1)
                        ->orWhere('defId', '=', $squad->p2)
                        ->orWhere('defId', '=', $squad->p3)
                        ->orWhere('defId', '=', $squad->p4)
                        ->orWhere('defId', '=', $squad->p5);
                }
            )
            ->count();
    }


    /**
     * @return array
     */
    public function getPlayersWithCrancorSquads(): array
    {
        $squads = $this->getCrancorSquads();
        $squadDetails = [];

        foreach ($squads as $raidSquad) {
            foreach ($raidSquad->squads as $squad) {
                $squadDetails[$squad->name][] = $this->squadController->getPlayersWithFullSquad($squad, 'crancor');
            }
        }

        return $squadDetails;
    }



    /**
     * @param RaidSquad $squad
     * @return mixed
     */
    private function getDamageRequired(RaidSquad $squad)
    {
        $damageRequired = Raid::where('id', '5')->first();
        $healthField = 'p' . $squad->phase . '_health';

        return $damageRequired->{$healthField};
    }


    /**
     * @param Int $damageToRemove
     * @param Int $health
     * @return Int
     */
    private function calculateRemainingPhaseHealth(Int $damageToRemove, Int $health): Int
    {
        return $health - $damageToRemove;
    }

    /**
     * @return Int|null
     */
    public function getPhaseHealth(): ?Int
    {
        return $this->phaseHealth;
    }


    /**
     * @param mixed $phaseHealth
     */
    public function setPhaseHealth(int $phaseHealth): void
    {
        $this->phaseHealth = $phaseHealth;
    }
}
