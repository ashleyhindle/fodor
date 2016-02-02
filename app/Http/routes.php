<?php
use phpseclib\Crypt\RSA;
use Illuminate\Http\Request;
use DigitalOceanV2\Adapter\GuzzleHttpAdapter;
use DigitalOceanV2\DigitalOceanV2;

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
    Route::get('/do/start', function (Request $request) {
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => getenv('DIGITALOCEAN_CLIENT_ID'),    // The client ID assigned to you by the provider
            'clientSecret'            => getenv('DIGITALOCEAN_CLIENT_SECRET'),   // The client password assigned to you by the provider
            'redirectUri'             => 'https://fodor.xyz/do/callback',
            'urlAuthorize'            => 'https://cloud.digitalocean.com/v1/oauth/authorize',
            'urlAccessToken'          => 'https://cloud.digitalocean.com/v1/oauth/token',
            'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource',
            'scopes'                   => 'read write'
        ]);

        $authorizationUrl = $provider->getAuthorizationUrl();
        $request->session()->set('oauth2state', $provider->getState());

        return redirect($authorizationUrl);
    });

    Route::get('/do/callback', function (Request $request) {
        if (empty($request->input('state'))) {
            die('Invalid');
        }

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => getenv('DIGITALOCEAN_CLIENT_ID'),    // The client ID assigned to you by the provider
            'clientSecret'            => getenv('DIGITALOCEAN_CLIENT_SECRET'),   // The client password assigned to you by the provider
            'redirectUri'             => 'https://fodor.xyz/do/callback',
            'urlAuthorize'            => 'https://cloud.digitalocean.com/v1/oauth/authorize',
            'urlAccessToken'          => 'https://cloud.digitalocean.com/v1/oauth/token',
            'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource',
            'scopes'                   => 'read write'
        ]);

        try {
            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);

            // We have an access token, which we may use in authenticated
            // requests against the service provider's API.
            echo $accessToken->getToken() . "<hr/>";
            echo $accessToken->getRefreshToken() . "<hr/>";
            echo $accessToken->getExpires() . "<hr/>";
            echo ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<hr/>";

            $adapter = new GuzzleHttpAdapter($accessToken->getToken());
            $digitalocean = new DigitalOceanV2($adapter);

            $size = $digitalocean->size();
            $sizes = $size->getAll();
            print_r($sizes);
            exit;
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details.
            exit($e->getMessage());
        }

        die('redirected, woo');
    });


    Route::get('/', function () {
        $installationUuid = 'mousey';
        $publicKeyName = 'publickeys/' . $installationUuid . '.pub';
        $privateKeyName = 'privatekeys/' . $installationUuid . '.key';

        if (Storage::exists($publicKeyName) === false || Storage::exists($privateKeyName) === false) {
            $rsa = new RSA();
            $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);
            $keys = $rsa->createKey(4096);

            $privateKey = $keys['privatekey'];
            $publicKey = $keys['publickey'];

            Storage::disk('local')->put($privateKeyName, $privateKey);
            Storage::disk('local')->put($publicKeyName, $publicKey);
        }

        $sshConnection = ssh2_connect('mycloud.smellynose.com', 22, array('hostkey'=>'ssh-rsa'));
        if (ssh2_auth_pubkey_file(
            $sshConnection,
            'ahindle',
            storage_path('app/' . $publicKeyName),
            storage_path('app/' . $privateKeyName)
        )) {
            echo "Public Key Authentication Successful";
        } else {
            echo 'Public Key Authentication Failed';
        }
        echo '<a href="/do/start">DO Start</a>';
        exit;
        return view('welcome');
    });
});

Route::group(['middleware' => 'web'], function () {
    Route::auth();

    Route::get('/home', 'HomeController@index');
});
