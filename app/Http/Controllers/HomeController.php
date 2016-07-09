<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;
use App\Http\Requests;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $baseScript = \View::make('provision-base.ubuntu-14-04-x64',[
            'installpath' => '{{ $installpath }}',
            'name' => '{{ $name }}',
            'domain' => '{{ $domain }}',
            'ipv4' => '{{ $ipv4 }}',
            'inputs' => [
                'APIKEY' => 'xxxx-xxxx-xxxx-xxxx',
            ]
        ]);

        return view('home', [
            'baseScript' => $baseScript,
        ]);
    }
}
