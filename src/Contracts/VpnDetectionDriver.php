<?php

namespace JeffersonGoncalves\VisitorFingerprint\Contracts;

use JeffersonGoncalves\VisitorFingerprint\Data\ThreatResult;

interface VpnDetectionDriver
{
    public function check(string $ip): ThreatResult;
}
