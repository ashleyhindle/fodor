<?php
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::group(['middleware' => ['web']], function () {
    Route::get('/', 'HomeController@index');
    Route::get('/provision-base', function() {
        dd(View::make('provision-base.ubuntu-14-04-x64',[
            'installpath' => '/var/www/fodor-example/',
            'name' => 'ashleyhindle/fodor-example',
        ])->render());
    });

    Route::get('/github/start', 'GitHubController@start');
    Route::get('/github/callback', 'GitHubController@callback');

    Route::post('/provision/start/{repo}', 'ProvisionController@start')->where('repo', '(.*)'); // Check logged into DO, check repo valid, show ssh key options
    Route::post('/provision/doit', 'ProvisionController@doit'); // Create SSH key, ask DO to create droplet

    // Check DO status to see when it's considered 'active' (and not new)
    Route::get('/provision/waiting/{id}/{uuid}.json', 'ProvisionController@waitingJson')->where(['id' => '[0-9]+', 'uuid' => '(.*)']);

    Route::get('/provision/waiting/{id}/{uuid}', 'ProvisionController@waiting')->where(['id' => '[0-9]+', 'uuid' => '(.*)']);

    Route::get('/provision/provision/{id}/{uuid}', 'ProvisionController@provision')->where(['id' => '[0-9]+', 'uuid' => '(.*)']);
    Route::get('/provision/provisioning/{id}/{uuid}', 'ProvisionController@provisioning')->where(['id' => '[0-9]+', 'uuid' => '(.*)']);

    Route::get('/provision/log/{id}/{uuid}', 'ProvisionController@log')->where(['id' => '[0-9]+', 'uuid' => '(.*)']);
    Route::get('/provision/logDownload/{id}/{uuid}', 'ProvisionController@logDownload')->where(['id' => '[0-9]+', 'uuid' => '(.*)']);

    Route::get('/provision/ready/{id}/{uuid}', 'ProvisionController@ready')->where(['id' => '[0-9]+', 'uuid' => '(.*)']);

    Route::get('/provision/start/{repo}', 'ProvisionController@start')->where('repo', '(.*)');
    Route::get('/provision/{repo}', 'ProvisionController@view')->where('repo', '(.*)');


    Route::get('/do/start', function (Request $request) {
        $provider = $this->app['DigitalOceanOauthServiceProvider'];

        $authorizationUrl = $provider->getAuthorizationUrl();
        $request->session()->set('oauth2state', $provider->getState());

        return redirect($authorizationUrl);
    });

    Route::get('/do/callback', function (Request $request) {
        if (empty($request->input('state'))) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Invalid state, what are you up to?!']);
            return redirect('/?ohno');
        }

        $provider = $this->app['DigitalOceanOauthServiceProvider'];

        try {
            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);

            if (!is_object($accessToken)) {
                $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'We couldn\'t get your details and log you in!']);
                return redirect('/?ohno');
            }

            $request->session()->set('digitalocean', [
                'token' => $accessToken->getToken(),
                'refreshToken' => $accessToken->getToken(),
                'expires' => $accessToken->getExpires(),
                'hasExpired' => $accessToken->hasExpired()
            ]);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details.
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'We couldn\'t get your details and log you in because of a pesky exception!']);
            return redirect('/?ohno');
        }

        if ($request->session()->has('intendedRepo')) {
	        $intendedRepo = $request->session()->get('intendedRepo');
            $request->session()->forget('intendedRepo');
            return redirect(url('/provision/start/' . $intendedRepo));
        }

        return redirect(url('/'));
    });

});
