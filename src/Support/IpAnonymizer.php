<?php

namespace JeffersonGoncalves\VisitorFingerprint\Support;

class IpAnonymizer
{
    /**
     * Truncates an IP to its anonymized form: IPv4 keeps the /24, IPv6 the /48.
     */
    public static function truncate(string $ip): string
    {
        if (str_contains($ip, ':')) {
            $groups = explode(':', self::expandIpv6($ip));

            return implode(':', array_slice($groups, 0, 3)).'::';
        }

        $octets = explode('.', $ip);

        if (count($octets) !== 4) {
            return $ip;
        }

        $octets[3] = '0';

        return implode('.', $octets);
    }

    public static function version(string $ip): int
    {
        return str_contains($ip, ':') ? 6 : 4;
    }

    /**
     * Salted hash of the raw IP, for uniqueness comparisons without storing it.
     */
    public static function hash(string $ip): string
    {
        $salt = (string) config('visitor-fingerprint.hash_salt');

        return hash('sha256', $salt.$ip);
    }

    protected static function expandIpv6(string $ip): string
    {
        $binary = @inet_pton($ip);

        if ($binary === false) {
            return $ip;
        }

        $hex = bin2hex($binary);

        $groups = str_split($hex, 4);

        return implode(':', $groups);
    }
}
