<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MetaController;
use App\Http\Controllers\SquadController;
use App\Http\Controllers\GuildController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GetRaidReport;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|≤
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * Authorisation routing
 */
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers',
 ], function ($router) {
    Route::post('login', 'AuthController@login')->withoutMiddleware(['auth:api']);
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh')->withoutMiddleware(['auth:api']);
    Route::get('user', 'AuthController@me');
});


/**
 * API calls for members
 */
//Route::get('/update-guild-members', [GuildController::class, 'updateGuidMembers']);
Route::get('/members/all', [MemberController::class, 'getAll']);
Route::get('/members/update', [GuildController::class, 'updateGuidMembers']);
Route::get('/members/{allycode}', [MemberController::class, 'getMember']);

/**
 * API calls for unit data
 */
Route::get('/units/update', [UnitController::class, 'updateUnits']);

/**
 * API routes for report generating
 */
Route::get('/report', [MetaController::class, 'getReport']);
Route::get('/report/google-report', [MetaController::class, 'googleReportCron']);
Route::get('/report/google-report-with-keys', [MetaController::class, 'googleReportWithKeys']);

Route::get('/report/squads', [SquadController::class, 'returnGuildMetaSquads']);
Route::get('/report/google-squad-report', [SquadController::class, 'submitGuildMetaSquadsToGoogle']);

/**
 * API routes for squad data
 */
Route::get('/meta-squads', [SquadController::class, 'getAll']);
Route::get('/squads/all', [SquadController::class, 'getAll']);
//Route::get('/guild-squad-report', [SquadController::class, 'returnGuildMetaSquads']);
Route::put('/squad-builder', [SquadController::class, 'store']);
Route::put('/squads/update/{id?}', [SquadController::class, 'store']);
Route::delete('/squad-builder/{id}', [SquadController::class, 'destroy']);
Route::get('/squads/getform', [SquadController::class, 'returnSquadForm']);
Route::get('/squads/get/{id}', [SquadController::class, 'getOne']);

/**
 * API routes for custom reports
 */
Route::get('/squads/crancor/all', [SquadController::class, 'getPlayersWithCrancorSquads']);
Route::get('/squads/crancor/phases', [GetRaidReport::class, 'getPlayersWithCrancorSquadsByPhase']);
