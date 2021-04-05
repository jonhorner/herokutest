<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuildController;
use App\Http\Controllers\UnitController;

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

Route::get('/', function () {
    //return view('welcome');
});

Route::get('/api/guild', [GuildController::class, 'showGuild']);
Route::get('/api/guild/all', [GuildController::class, 'getAll']);
Route::get('/api/update-guild-members', [GuildController::class, 'updateGuidMembers']);


Route::get('/api/units/all', [UnitController::class, 'getAll']);