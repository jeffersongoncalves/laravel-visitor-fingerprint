<?php

namespace JeffersonGoncalves\VisitorFingerprint;

use JeffersonGoncalves\VisitorFingerprint\Contracts\GeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\Contracts\VpnDetectionDriver;
use JeffersonGoncalves\VisitorFingerprint\Data\GeoLocation;
use JeffersonGoncalves\VisitorFingerprint\Data\ThreatResult;
use JeffersonGoncalves\VisitorFingerprint\Support\AcceptLanguage;
use JeffersonGoncalves\VisitorFingerprint\Support\BotDetector;
use JeffersonGoncalves\VisitorFingerprint\Support\IpAnonymizer;
use JeffersonGoncalves\VisitorFingerprint\Support\RefererClassifier;
use JeffersonGoncalves\VisitorFingerprint\Support\UserAgentParser;

/**
 * Thin facade-friendly entry point over the package's Support classes and
 * the container-bound GeoIP / VPN detection drivers.
 */
class VisitorFingerprint
{
    /**
     * @return array{browser: ?string, browser_version: ?string, operating_system: ?string, operating_system_version: ?string}
     */
    public function parseUserAgent(string $userAgent): array
    {
        return UserAgentParser::parse($userAgent);
    }

    public function deviceType(string $userAgent): string
    {
        return UserAgentParser::fastDeviceType($userAgent);
    }

    public function isBot(string $userAgent): bool
    {
        return BotDetector::isBot($userAgent);
    }

    public function anonymizeIp(string $ip): string
    {
        return IpAnonymizer::truncate($ip);
    }

    public function hashIp(string $ip): string
    {
        return IpAnonymizer::hash($ip);
    }

    public function preferredLanguage(?string $acceptLanguageHeader): ?string
    {
        return AcceptLanguage::preferred($acceptLanguageHeader);
    }

    public function classifyReferer(?string $refererUrl, string $appHost): string
    {
        return RefererClassifier::classify($refererUrl, $appHost);
    }

    public function geoLocate(string $ip): GeoLocation
    {
        return app(GeoIpDriver::class)->resolve($ip);
    }

    public function checkThreat(string $ip): ThreatResult
    {
        return app(VpnDetectionDriver::class)->check($ip);
    }
}
