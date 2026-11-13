<?php

namespace JeffersonGoncalves\VisitorFingerprint\Support;

class RefererClassifier
{
    protected const SOCIAL = [
        'facebook.com', 'instagram.com', 'twitter.com', 'x.com', 't.co',
        'linkedin.com', 'lnkd.in', 'tiktok.com', 'pinterest.com', 'reddit.com',
        'whatsapp.com', 'telegram.org', 't.me', 'threads.net', 'youtube.com',
    ];

    protected const SEARCH = [
        'google.com', 'google.com.br', 'google.co.uk', 'google.ca', 'google.de',
        'google.fr', 'google.es', 'google.it', 'google.pt', 'google.com.mx',
        'google.com.ar', 'google.co.jp', 'google.co.in', 'google.com.au',
        'bing.com', 'yahoo.com', 'duckduckgo.com', 'baidu.com',
        'yandex.ru', 'yandex.com', 'ecosia.org', 'search.brave.com',
    ];

    protected const EMAIL = [
        'mail.google.com', 'outlook.live.com', 'outlook.office.com',
        'outlook.office365.com', 'mail.yahoo.com',
    ];

    public static function classify(?string $refererUrl, string $appHost): string
    {
        if (blank($refererUrl)) {
            return 'direct';
        }

        $host = static::normalize(parse_url($refererUrl, PHP_URL_HOST) ?: '');

        if ($host === static::normalize($appHost)) {
            return 'internal';
        }

        // Email before search: mail.google.com would otherwise match google.com.
        foreach (['social' => self::SOCIAL, 'email' => self::EMAIL, 'search' => self::SEARCH] as $type => $domains) {
            $domains = array_merge($domains, (array) config("visitor-fingerprint.referer_domains.{$type}", []));

            foreach ($domains as $domain) {
                if (static::matches($host, static::normalize($domain))) {
                    return $type;
                }
            }
        }

        return 'direct';
    }

    protected static function normalize(string $host): string
    {
        $host = strtolower($host);

        return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
    }

    protected static function matches(string $host, string $domain): bool
    {
        return $host === $domain || str_ends_with($host, '.'.$domain);
    }
}
