<?php

use App\Http\Controllers\MetaController;
use App\Http\Controllers\SquadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuildController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\MemberController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', static function () {

});

Route::get('/api/update-guild-members', [GuildController::class, 'updateGuidMembers']);
Route::get('/api/members/all', [MemberController::class, 'getAll']);
Route::get('/api/members/{allycode}', [MemberController::class, 'getMember']);


Route::get('/api/units/update', [UnitController::class, 'updateUnits']);

Route::get('/api/report', [MetaController::class, 'index']);
Route::get('/api/google-report', [MetaController::class, 'googleReport']);

Route::get('/api/meta-squads', [SquadController::class, 'getAll']);
Route::get('/guild-squad-report', [SquadController::class, 'showGuildMetaSquads']);
Route::put('/api/squad-builder', [SquadController::class, 'store']);
Route::delete('/api/squad-builder/{id}', [SquadController::class, 'destroy']);

