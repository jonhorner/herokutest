<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use Illuminate\Http\Request;
use Revolution\Google\Sheets\Facades\Sheets;


class SheetsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(PostRequest $request)
    {
        /**
         * Service Account demo.
         */

    }

    public function index(Request $request)
    {
        // $sheets = Sheets::spreadsheet('1D-baQNmzJNfUBArr7fpQC7m_hPCI7KMnAITHVqJyL9c')
        //                 ->sheetById('1D-baQNmzJNfUBArr7fpQC7m_hPCI7KMnAITHVqJyL9c')
        //                 ->get();

        $sheet = Sheets::spreadsheet('1D-baQNmzJNfUBArr7fpQC7m_hPCI7KMnAITHVqJyL9c')
                    ->sheet('Autoupdate test');
        // $sheets = Sheets::spreadsheetByTitle('Overview');
        // $sheets = Sheets::sheetById('1D-baQNmzJNfUBArr7fpQC7m_hPCI7KMnAITHVqJyL9c');
        // dd($sheets);

        // Sheets::all();

        // dd($sheet->all());


        // Sheets::append([array])

        // $posts = Sheets::collection($header, $sheets);
        // $posts = $posts->reverse()->take(10);

        // return view('welcome')->with(compact('posts'));


        $append = [
            $request->input('name'),
            $request->input('message'),
            now()->toDateTimeString(),
        ];

        $sheet->update(
            array(
                array(now()->toDateTimeString(),'',''),
                array('1','2','3')
            )
        );

        return back();
    }
}
