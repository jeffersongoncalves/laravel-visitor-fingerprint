<?php

namespace JeffersonGoncalves\VisitorFingerprint\Support;

class BotDetector
{
    protected const SIGNATURES = [
        'bot', 'spider', 'crawl', 'slurp', 'facebookexternalhit', 'preview',
        'headless', 'phantomjs', 'curl/', 'wget/', 'python-requests', 'axios/',
        'googlebot', 'bingbot', 'yandexbot', 'duckduckbot', 'baiduspider',
        'discordbot', 'telegrambot', 'whatsapp', 'linkedinbot', 'pingdom',
        'uptimerobot', 'ahrefsbot', 'semrushbot', 'mj12bot',
    ];

    public static function isBot(string $userAgent): bool
    {
        if (trim($userAgent) === '') {
            return true;
        }

        $needle = strtolower($userAgent);

        foreach (self::SIGNATURES as $signature) {
            if (str_contains($needle, $signature)) {
                return true;
            }
        }

        return false;
    }
}
