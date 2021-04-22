<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MetaController;
use App\Http\Controllers\SquadController;
use App\Http\Controllers\GuildController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\MemberController;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});



//Route::get('/update-guild-members', [GuildController::class, 'updateGuidMembers']);
Route::get('/members/all', [MemberController::class, 'getAll']);
Route::get('/members/update', [GuildController::class, 'updateGuidMembers']);
Route::get('/members/{allycode}', [MemberController::class, 'getMember']);


Route::get('/units/update', [UnitController::class, 'updateUnits']);

Route::get('/report', [MetaController::class, 'getReport']);
Route::get('/report/google-report', [MetaController::class, 'googleReport']);
Route::get('/report/google-report-with-keys', [MetaController::class, 'googleReportWithKeys']);

Route::get('/report/squads', [SquadController::class, 'returnGuildMetaSquads']);
Route::get('/report/google-squad-report', [SquadController::class, 'submitGuildMetaSquadsToGoogle']);


Route::get('/meta-squads', [SquadController::class, 'getAll']);
Route::get('/squads/all', [SquadController::class, 'getAll']);
//Route::get('/guild-squad-report', [SquadController::class, 'returnGuildMetaSquads']);
Route::put('/squad-builder', [SquadController::class, 'store']);
Route::put('/squads/update/{id?}', [SquadController::class, 'store']);
Route::delete('/squad-builder/{id}', [SquadController::class, 'destroy']);
Route::get('/squads/getform', [SquadController::class, 'returnSquadForm']);
Route::get('/squads/get/{id}', [SquadController::class, 'getOne']);
