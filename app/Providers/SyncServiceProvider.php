<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SyncServiceProvider extends ServiceProvider
{
    public function register()
    {
        \App::bind('shopify', function ($app) {
            return new \App\Services\Sync\Sync;
        });
    }
}
