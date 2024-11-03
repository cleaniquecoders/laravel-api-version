<?php

namespace CleaniqueCoders\LaravelApiVersion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\LaravelApiVersion\LaravelApiVersion
 */
class LaravelApiVersion extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\LaravelApiVersion\LaravelApiVersion::class;
    }
}
