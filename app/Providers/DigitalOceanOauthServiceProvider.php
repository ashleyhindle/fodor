<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DigitalOceanOauthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('DigitalOceanOauthServiceProvider', function($app) { // TODO: Make this not dreadful
            return new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId'                => getenv('DIGITALOCEAN_CLIENT_ID'),    // The client ID assigned to you by the provider
                'clientSecret'            => getenv('DIGITALOCEAN_CLIENT_SECRET'),   // The client password assigned to you by the provider
                'redirectUri'             => 'https://fodor.xyz/do/callback',
                'urlAuthorize'            => 'https://cloud.digitalocean.com/v1/oauth/authorize',
                'urlAccessToken'          => 'https://cloud.digitalocean.com/v1/oauth/token',
                'urlResourceOwnerDetails' => 'NA',
                'scopes'                   => 'read write'
            ]);
        });
    }
}
