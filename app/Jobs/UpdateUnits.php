<?php

namespace App\Jobs;

use App\Models\SwUnitCategories;
use App\Models\SwUnitCategoryXrefs;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\SwUnitData;

class UpdateUnits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public const BASE_URL = 'https://swgoh.gg';
    public const UNIT_URL = self::BASE_URL.'/api/characters/';

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
        $client = new Client(); //GuzzleHttp\Client
        $units = $client->request(
            'GET',
            self::UNIT_URL,
            [
                'Authorisation' => ['jon.horner@gmail.com','nFYCJ4aft67cCx6nRCzg']
            ]
        );

        $units = json_decode($units->getBody()->getContents());

        foreach ($units as $unit) {
            // Filter out event characters
            if (
                !strpos($unit->base_id, '_')
                || in_array($unit->base_id, array('L3_37', 'T3_M4'))
            ) {
                // get categories - add any which dont exist to the category table
                $categoryIds = [];
                if ($unit->categories) {
                    foreach ($unit->categories as $value) {
                        $category = SwUnitCategories::firstOrCreate(
                            [
                                'category' => trim($value)
                            ]
                        );

                        $categoryIds[] = $category->id;
                    }
                }

                // Create or update unit data
                $unit = SwUnitData::updateOrCreate(
                    ['baseId' => $unit->base_id],
                    [
                        //'thumbnailName' => $unit['base_id'],
                        'baseId' => $unit->base_id,
                        'nameKey' => $unit->name,
                        'combatType' => (int)$unit->combat_type,
                        'categories' => implode(',', $categoryIds)
                    ]
                );

                // Add cross reference data for unite categories
                if ($categoryIds) {
                    foreach ($categoryIds as $id) {
                        $unit = SwUnitCategoryXrefs::updateOrCreate(
                            ['sw_unit_categories_id' => $id],
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
