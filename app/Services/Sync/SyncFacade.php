<?php

namespace App\Services\Sync;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

class SyncFacade extends IlluminateFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sync';
    }
}
