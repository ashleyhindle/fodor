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

    Route::get('/provision/{repo}', 'ProvisionController@start')->where('repo', '[A-Za-z/0-9\-]+');
    Route::post('/provision/start', 'ProvisionController@start');
    Route::post('/provision/doit', 'ProvisionController@doit');
    Route::get('/provision/waiting/{id}/{uuid}', 'ProvisionController@waiting');


    Route::get('/do/start', function (Request $request) {
        $provider = $this->app['DigitalOceanOauthServiceProvider'];

        $authorizationUrl = $provider->getAuthorizationUrl();
        $request->session()->set('oauth2state', $provider->getState());

        return redirect($authorizationUrl);
    });
    Route::get('/do/callback', function (Request $request) {
        if (empty($request->input('state'))) {
            die('Invalid');
        }

        $provider = $this->app['DigitalOceanOauthServiceProvider'];

        try {
            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);

            if (!is_object($accessToken)) {
                die('We failed to get your DO tokens, sorry'); // TODO: Show nice error page here using proper view/template
            }

            $request->session()->set('digitalocean', [
                'token' => $accessToken->getToken(),
                'refreshToken' => $accessToken->getToken(),
                'expires' => $accessToken->getExpires(),
                'hasExpired' => $accessToken->hasExpired()
            ]);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details.
            exit($e->getMessage()); // TODO: Show nice error page here using proper view/template
        }

        return redirect(url('/'));
    });

});
