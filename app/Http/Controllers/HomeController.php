<?php

namespace App\Http\Controllers;

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
        if ($request->session()->has('intendedRepo')) {
            return redirect(url('/provision/' . $request->session()->get('intendedRepo')));
        }
        
        return view('home');
    }
}
