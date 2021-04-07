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

    public function updateUnits(){
        UpdateUnits::dispatch();
    }

}
