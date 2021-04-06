<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Http\Middleware\SwgohHelp;

use App\Http\Models\SwUnitData;

class UpdateUnits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->getAll();
    }

    private function getAll(){

        $units = (new SwgohHelp)->getUnitData();
        // $processedUnits = [];

        foreach ($units as $unit) {

            // Filter out event characters
            if(
                !strpos($unit['baseId'], '_')
                || in_array($unit['baseId'], array('L3_37','T3_M4'))
            ){
                $unit = SwUnitData::updateOrCreate(
                    [ 'baseId' => $unit['baseId'] ],
                    [
                        'thumbnailName' => $unit['thumbnailName'],
                        'baseId' => $unit['baseId'],
                        'nameKey' => $unit['nameKey'],
                        'combatType' => (int)$unit['combatType']
                    ]
                );

                // $processedUnits[] = $unit;
            }
        }
       // return $processedUnits;
    }
}
