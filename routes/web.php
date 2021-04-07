<?php

use App\Http\Controllers\MetaController;
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
