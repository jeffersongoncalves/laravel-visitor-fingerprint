<?php

namespace JeffersonGoncalves\VisitorFingerprint\GeoIp;

use GeoIp2\Database\Reader;
use JeffersonGoncalves\VisitorFingerprint\Contracts\GeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\Data\GeoLocation;
use Throwable;

/**
 * Reads a local MaxMind GeoLite2/GeoIP2 database. Requires the optional
 * `geoip2/geoip2` package (see composer.json "suggest") and a database path
 * configured via visitor-fingerprint.geoip.maxmind_database_path. Missing
 * package or database never raises — it just yields an empty GeoLocation.
 */
class MaxMindGeoIpDriver implements GeoIpDriver
{
    public function resolve(string $ip): GeoLocation
    {
        $databasePath = config('visitor-fingerprint.geoip.maxmind_database_path');

        if (! $databasePath || ! class_exists(Reader::class) || ! is_file($databasePath)) {
            return new GeoLocation;
        }

        try {
            $reader = new Reader($databasePath);
            $record = $reader->city($ip);

            return new GeoLocation(
                country: $record->country->name,
                countryCode: $record->country->isoCode,
                region: $record->mostSpecificSubdivision->name,
                city: $record->city->name,
                latitude: $record->location->latitude,
                longitude: $record->location->longitude,
                timezone: $record->location->timeZone,
            );
        } catch (Throwable $e) {
            report($e);

            return new GeoLocation;
        }
    }
}
