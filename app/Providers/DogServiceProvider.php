<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DogServiceProvider extends ServiceProvider
{
    public function register()
    {
        \App::bind('dog', function ($app) {
            return new \App\Services\Dog\Dog;
        });
    }
}
