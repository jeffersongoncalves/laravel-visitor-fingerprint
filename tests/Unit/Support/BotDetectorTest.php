<?php

use JeffersonGoncalves\VisitorFingerprint\Support\BotDetector;

it('flags known bot signatures', function (string $userAgent) {
    expect(BotDetector::isBot($userAgent))->toBeTrue();
})->with([
    'Googlebot/2.1 (+http://www.google.com/bot.html)',
    'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
    'facebookexternalhit/1.1',
    'curl/8.4.0',
    'python-requests/2.31.0',
]);

it('flags an empty user agent as a bot', function () {
    expect(BotDetector::isBot(''))->toBeTrue()
        ->and(BotDetector::isBot('   '))->toBeTrue();
});

it('flags user agents claiming an obsolete browser version', function (string $userAgent) {
    expect(BotDetector::isBot($userAgent))->toBeTrue();
})->with([
    'chrome 8' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/8.0.552.344 Safari/534.16',
    'firefox 45' => 'Mozilla/5.0 (Windows NT 6.1; rv:45.0) Gecko/20100101 Firefox/45.0',
    'ie 11' => 'Mozilla/5.0 (Windows NT 10.0; Trident/7.0; rv:11.0) like Gecko',
    'ie 9' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
]);

it('does not flag a current browser user agent', function (string $userAgent) {
    expect(BotDetector::isBot($userAgent))->toBeFalse();
})->with([
    'chrome' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
    'firefox' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:131.0) Gecko/20100101 Firefox/131.0',
    'safari' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15',
    'edge' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0',
    'opera' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 OPR/114.0.0.0',
    'samsung internet' => 'Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/25.0 Chrome/121.0.0.0 Mobile Safari/537.36',
    'chrome ios' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/128.0.6613.98 Mobile/15E148 Safari/604.1',
    'firefox ios' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/131.0 Mobile/15E148 Safari/605.1.15',
]);
