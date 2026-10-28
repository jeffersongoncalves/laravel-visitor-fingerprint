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

    /**
     * Minimum plausible major version per evergreen browser token. Real visitors
     * auto-update, so anything below this is a scraped UA string. Raise over time.
     * CriOS/FxiOS use their own tokens and are never matched here.
     */
    protected const MIN_MAJOR_VERSIONS = [
        'chrome' => 80,
        'firefox' => 70,
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

        return self::claimsObsoleteBrowser($needle) || self::lacksBrowserProduct($needle);
    }

    /**
     * Truncated scraper UAs name a rendering engine but no browser product.
     * iOS WKWebView / in-app browsers end in "Mobile/<build>" without "Safari/";
     * macOS WKWebView sends the bare "AppleWebKit/... (KHTML, like Gecko)" form.
     */
    protected static function lacksBrowserProduct(string $needle): bool
    {
        // ponytail: Macintosh exempt wholesale, so truncated Mac-platform scraper UAs pass
        if (str_contains($needle, 'applewebkit/') && ! str_contains($needle, 'macintosh') && ! preg_match('#chrome/|crios/|safari/|version/|mobile/#', $needle)) {
            return true;
        }

        return preg_match('#gecko/\d#', $needle) === 1 && ! preg_match('#firefox/|fxios/#', $needle);
    }

    protected static function claimsObsoleteBrowser(string $needle): bool
    {
        if (str_contains($needle, 'msie ') || str_contains($needle, 'trident/')) {
            return true;
        }

        foreach (self::MIN_MAJOR_VERSIONS as $token => $minimum) {
            if (preg_match('#\b'.$token.'/(\d+)#', $needle, $match) && (int) $match[1] < $minimum) {
                return true;
            }
        }

        return false;
    }
}
