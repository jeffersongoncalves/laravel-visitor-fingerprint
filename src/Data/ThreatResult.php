<?php

namespace JeffersonGoncalves\VisitorFingerprint\Data;

readonly class ThreatResult
{
    public function __construct(
        public bool $isVpn = false,
        public bool $isProxy = false,
        public bool $isTor = false,
        public bool $isDatacenter = false,
        public ?float $confidence = null,
        public ?string $provider = null,
    ) {}
}
