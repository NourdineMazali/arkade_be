<?php

namespace App\Providers;

use App\Services\Sync\Sync;
use Illuminate\Support\ServiceProvider;

class SyncServiceProvider extends ServiceProvider
{
    public function register()
    {
        \App::bind('shopify', function ($app) {
            return new Sync(new Shopify(), new DogApi());
        });
    }
}
