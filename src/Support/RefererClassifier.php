<?php

namespace JeffersonGoncalves\VisitorFingerprint\Support;

class RefererClassifier
{
    protected const SOCIAL = [
        'facebook.com', 'instagram.com', 'twitter.com', 'x.com', 't.co',
        'linkedin.com', 'tiktok.com', 'pinterest.com', 'reddit.com',
        'whatsapp.com', 'telegram.org', 'threads.net',
    ];

    protected const SEARCH = [
        'google.', 'bing.com', 'yahoo.', 'duckduckgo.com', 'baidu.com', 'yandex.',
    ];

    protected const EMAIL = [
        'mail.google.com', 'outlook.', 'outlook.live.com', 'mail.yahoo.com',
    ];

    public static function classify(?string $refererUrl, string $appHost): string
    {
        if (blank($refererUrl)) {
            return 'direct';
        }

        $host = strtolower(parse_url($refererUrl, PHP_URL_HOST) ?: '');
        $host = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        if ($host === strtolower($appHost)) {
            return 'internal';
        }

        foreach (self::SOCIAL as $needle) {
            if (str_contains($host, $needle)) {
                return 'social';
            }
        }

        foreach (self::EMAIL as $needle) {
            if (str_contains($host, $needle)) {
                return 'email';
            }
        }

        foreach (self::SEARCH as $needle) {
            if (str_contains($host, $needle)) {
                return 'search';
            }
        }

        return 'direct';
    }
}
