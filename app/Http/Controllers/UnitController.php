<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;
use App\Models\SwUnitData;
use App\Models\SwUnitCategories;
use App\Models\SwUnitCategoryXrefs;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Jobs\UpdateUnits;
use App\Http\Middleware\SwgohHelp;

class UnitController extends BaseController
{

    const BASE_URL = 'https://swgoh.gg';
    const UNIT_URL = self::BASE_URL.'/api/characters/';

    public function viewAllUnits()
    {
        return View::make('units', [
            'content' => '',
        	'unitData' => $this->getUnitsWithGuzzle()
        ]);
    }


    public function updateUnits(){
        UpdateUnits::dispatch();
    }


    private function getUnitsWithGuzzle(){


        // dd(self::BASE_URL.self::UNIT_URL);

        $client = new Client(); //GuzzleHttp\Client
        $units = $client->request(
            'GET',
            self::UNIT_URL,
            [
                'Authorisation' => ['jon.horner@gmail.com','nFYCJ4aft67cCx6nRCzg']
            ]
        );

        $units = json_decode($units->getBody()->getContents());
        // $units = (new SwgohHelp)->getUnitData();
        $processedUnits = [];
        // dd($units);
        foreach ($units as $unit) {

            // Filter out event characters
            if(
                !strpos($unit->base_id, '_')
                || in_array($unit->base_id, array('L3_37','T3_M4'))
            ){
                // get categories - add any which dont exist to the category table
                $categoryIds = [];
                if($unit->categories){
                    foreach ($unit->categories as $value) {
                        $category = SwUnitCategories::firstOrCreate([
                            'category' => trim($value)
                        ]);

                        $categoryIds[] = $category->id;
                    }
                }

                // Create or update unit data
                $unit = SwUnitData::updateOrCreate(
                    [ 'baseId' => $unit->base_id ],
                    [
                        //'thumbnailName' => $unit['base_id'],
                        'baseId' => $unit->base_id,
                        'nameKey' => $unit->name,
                        'combatType' => (int)$unit->combat_type,
                        'categories' => implode(',',$categoryIds)
                    ]
                );

                $processedUnits[] = $unit;

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


        return $processedUnits;


        // var_dump($unitData);
        // return $player[0]['roster'];
        // foreach ($player[0]['roster'] as $unit) {
        //     // var_dump($unit['relic']['currentTier']);

        //     if((int)$unit['relic']['currentTier'] > 0){
        //         $roster[] = $unit['defId'];
        //     }
        // }

        // return $unitData;

    }
}
