<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cloudflare\Api;

class CloudflareApiServiceProvider extends ServiceProvider
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
        $this->app->singleton('CloudflareApiServiceProvider', function() {
            return new Api(env('CLOUDFLARE_API_EMAIL'), env('CLOUDFLARE_API_KEY'));
        });
    }
}
