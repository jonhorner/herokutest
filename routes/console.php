<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote');

Artisan::command('updateGuild', function () {
    $this->comment((new App\Http\Controllers\GuildController)->updateGuidMembers());
})->purpose('update guild');

Artisan::command('googleReport', function () {
    $this->comment((new App\Http\Controllers\MetaController)->googleReportCron());
})->purpose('Generate meta report');

Artisan::command('squadReport', function () {
    $this->comment((new App\Http\Controllers\SquadController)->submitGuildMetaSquadsToGoogleCron());
})->purpose('Generate squad report');
