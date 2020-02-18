<?php

namespace App\Services\Dog ;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

class DogFacade extends IlluminateFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dog';
    }
}
