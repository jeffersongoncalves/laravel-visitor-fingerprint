<?php

namespace JeffersonGoncalves\VisitorFingerprint\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\VisitorFingerprint\VisitorFingerprint
 */
class VisitorFingerprint extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-visitor-fingerprint';
    }
}
