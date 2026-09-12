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
 *
 * City and ASN data live in two separate MaxMind databases — GeoLite2-City
 * has no ISP/ASN fields at all, so isp/asn only populate when
 * maxmind_asn_database_path also points at a real GeoLite2-ASN.mmdb. Missing
 * ASN database degrades to the same city-only result as before, never raises.
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

            [$isp, $asn] = $this->resolveAsn($ip);

            return new GeoLocation(
                country: $record->country->name,
                countryCode: $record->country->isoCode,
                region: $record->mostSpecificSubdivision->name,
                city: $record->city->name,
                latitude: $record->location->latitude,
                longitude: $record->location->longitude,
                timezone: $record->location->timeZone,
                isp: $isp,
                asn: $asn,
            );
        } catch (Throwable $e) {
            report($e);

            return new GeoLocation;
        }
    }

    /** @return array{0: ?string, 1: ?string} [isp, asn] */
    private function resolveAsn(string $ip): array
    {
        $asnDatabasePath = config('visitor-fingerprint.geoip.maxmind_asn_database_path');

        if (! $asnDatabasePath || ! is_file($asnDatabasePath)) {
            return [null, null];
        }

        try {
            $record = (new Reader($asnDatabasePath))->asn($ip);

            $asn = $record->autonomousSystemNumber !== null
                ? "AS{$record->autonomousSystemNumber}"
                : null;

            return [$record->autonomousSystemOrganization, $asn];
        } catch (Throwable $e) {
            report($e);

            return [null, null];
        }
    }
}
