<?php

namespace Takt\Score\Facades;

use Illuminate\Support\Facades\Facade;

class Score extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Score';
    }
}
