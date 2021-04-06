<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;

class ModController extends BaseController
{
    public function showMods(): string
    {
        return View::make('welcome', array('mods' => $this->getMods()));
    }

    private	function getMods()
    {
    	 // return swgoh()->getMods('752975616');
    }
}
