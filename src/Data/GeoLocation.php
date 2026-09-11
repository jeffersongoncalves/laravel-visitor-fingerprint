<?php

namespace JeffersonGoncalves\VisitorFingerprint\Data;

readonly class GeoLocation
{
    public function __construct(
        public ?string $country = null,
        public ?string $countryCode = null,
        public ?string $region = null,
        public ?string $city = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $timezone = null,
        public ?string $isp = null,
        public ?string $asn = null,
    ) {}
}
