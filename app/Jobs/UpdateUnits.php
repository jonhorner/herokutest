<?php

namespace App\Jobs;

use App\Models\SwUnitCategories;
use App\Models\SwUnitCategoryXrefs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Http\Middleware\SwgohHelp;

use App\Models\SwUnitData;

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
    public function handle(): void
    {
        $this->getAll();
    }


    private function getAll(): void
    {
        $units = (new SwgohHelp)->getUnitData();

        foreach ($units as $unit) {
            // Filter out event characters
            if(
                !strpos($unit['baseId'], '_')
                || in_array($unit['baseId'], array('L3_37','T3_M4'))
            ){

                // get categories - add any which dont exist to the category table
                $categoryIds = [];
                if($unit['categories']){
                    foreach ($unit['categories'] as $value) {
                        $category = SwUnitCategories::firstOrCreate(
                            [
                                'category' => trim($value)
                            ]
                        );

                        $categoryIds[] = $category->id;
                    }
                }

                SwUnitData::updateOrCreate(
                    [ 'baseId' => $unit['baseId'] ],
                    [
                        'thumbnailName' => $unit['thumbnailName'],
                        'baseId' => $unit['baseId'],
                        'nameKey' => $unit['nameKey'],
                        'combatType' => (int)$unit['combatType'],
                        'categories' => implode(',',$categoryIds)
                    ]
                );

                // Add cross reference data for unite categories
                if($categoryIds){
                    foreach ($categoryIds as $id) {
                        $unit = SwUnitCategoryXrefs::updateOrCreate(
                            [ 'sw_unit_categories_id' => $id ],
                            [
                                'sw_unit_data_id' => $unit->id,
                                'sw_unit_categories_id' => $id
                            ]
                        );
                    }
                }
            }
        }
    }
}
