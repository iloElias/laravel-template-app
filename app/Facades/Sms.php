<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Sms extends Facade
{
    /**
     * Retorna o nome do registro no container.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sms';
    }
}
