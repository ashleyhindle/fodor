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
    public function index()
    {
/*        $sshConnection = ssh2_connect('mycloud.smellynose.com', 22, array('hostkey'=>'ssh-rsa'));
        $sshSuccess = ssh2_auth_pubkey_file(
            $sshConnection,
            'ahindle',
            storage_path('app/' . $publicKeyName),
            storage_path('app/' . $privateKeyName)
        );*/

        return view('home');
    }
}
