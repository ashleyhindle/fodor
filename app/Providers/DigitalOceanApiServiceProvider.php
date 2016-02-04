<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DigitalOceanV2\Adapter\GuzzleHttpAdapter;
use DigitalOceanV2\DigitalOceanV2;
use Illuminate\Http\Request;

class DigitalOceanApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('DigitalOceanApiServiceProvider', function(Request $request) { // TODO: Make this not dreadful
            $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
            return new DigitalOceanV2($adapter);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
