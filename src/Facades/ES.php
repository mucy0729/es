<?php

namespace Qdd\Es\Facades;

use Illuminate\Support\Facades\Facade;

class ES extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'es';
    }
}