<?php

namespace JeffersonGoncalves\VisitorFingerprint\Support;

/**
 * Minimal regex-based UA parser. No composer dependency: callers that only
 * need device type on a hot path can call fastDeviceType() directly,
 * everything else is a full parse().
 */
class UserAgentParser
{
    /**
     * Cheap device-type-only read, safe to call inline on a hot path.
     */
    public static function fastDeviceType(string $userAgent): string
    {
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Cheap OS-name-only read (no version), safe to call inline.
     */
    public static function fastOperatingSystem(string $userAgent): ?string
    {
        return match (true) {
            (bool) preg_match('/iphone|ipad|ipod/i', $userAgent) => 'iOS',
            (bool) preg_match('/windows/i', $userAgent) => 'Windows',
            (bool) preg_match('/mac os x|macintosh/i', $userAgent) => 'macOS',
            (bool) preg_match('/android/i', $userAgent) => 'Android',
            (bool) preg_match('/linux/i', $userAgent) => 'Linux',
            default => null,
        };
    }

    /**
     * Full parse (browser + versions).
     *
     * @return array{browser: ?string, browser_version: ?string, operating_system: ?string, operating_system_version: ?string}
     */
    public static function parse(string $userAgent): array
    {
        return [
            'browser' => self::browser($userAgent),
            'browser_version' => self::browserVersion($userAgent),
            'operating_system' => self::fastOperatingSystem($userAgent),
            'operating_system_version' => self::operatingSystemVersion($userAgent),
        ];
    }

    protected static function browser(string $userAgent): ?string
    {
        return match (true) {
            (bool) preg_match('/edg\//i', $userAgent) => 'Edge',
            (bool) preg_match('/opr\/|opera/i', $userAgent) => 'Opera',
            (bool) preg_match('/chrome\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/firefox\//i', $userAgent) => 'Firefox',
            (bool) preg_match('/version\/.*safari/i', $userAgent) => 'Safari',
            (bool) preg_match('/msie|trident/i', $userAgent) => 'Internet Explorer',
            default => null,
        };
    }

    protected static function browserVersion(string $userAgent): ?string
    {
        $patterns = [
            'Edge' => '/edg\/([\d.]+)/i',
            'Opera' => '/(?:opr|opera)\/([\d.]+)/i',
            'Chrome' => '/chrome\/([\d.]+)/i',
            'Firefox' => '/firefox\/([\d.]+)/i',
            'Safari' => '/version\/([\d.]+).*safari/i',
            'Internet Explorer' => '/(?:msie |rv:)([\d.]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    protected static function operatingSystemVersion(string $userAgent): ?string
    {
        return match (true) {
            (bool) preg_match('/windows nt ([\d.]+)/i', $userAgent, $m) => $m[1],
            (bool) preg_match('/mac os x ([\d_]+)/i', $userAgent, $m) => str_replace('_', '.', $m[1]),
            (bool) preg_match('/android ([\d.]+)/i', $userAgent, $m) => $m[1],
            (bool) preg_match('/os ([\d_]+) like mac os x/i', $userAgent, $m) => str_replace('_', '.', $m[1]),
            default => null,
        };
    }
}
