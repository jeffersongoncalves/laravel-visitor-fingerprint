<?php

namespace JeffersonGoncalves\VisitorFingerprint\Contracts;

use JeffersonGoncalves\VisitorFingerprint\Data\GeoLocation;

interface GeoIpDriver
{
    public function resolve(string $ip): GeoLocation;
}
