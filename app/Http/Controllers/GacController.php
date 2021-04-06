<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

// use Revolution\Google\Sheets\Facades\Sheets;
use App\Models\SwGuildMember;
use App\Models\SwGuildTokens;
use App\Http\Controllers\PlayerController;

use App\Http\Middleware\SwgohHelp;

class GacController extends BaseController
{

    public function getIndex()
    {
       return View::make('gac.index', [
            'content' => '',
            // 'squads' => $this->squadList(),
        ]);
    }


    public function postIndex(Request $request)
    {

        // dd($request->allycode);

        $player = (new SwgohHelp)->getPlayer($request->allycode);

        // dd($player[0]['stats'][10]['value']);

        SwGuildTokens::create([
            'allycode' => $player[0]['allyCode'],
            'total_tickets' => $player[0]['stats'][10]['value']
        ]);

        // This weeks tokens
        // SwGuildTokens::lastWeek()->get;

        return View::make('gac.report', [
            'content' => '',
            // 'squads' => $this->squadList(),
        ]);
    }

    // private function sendToSheets($data)
    // {

    //     $sheet = Sheets::spreadsheet('1D-baQNmzJNfUBArr7fpQC7m_hPCI7KMnAITHVqJyL9c')
    //                 ->sheet('Overview');

    //     $sheet->update($data);

    //     // return back();
    // }

}
