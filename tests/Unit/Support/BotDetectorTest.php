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

it('does not flag a normal browser user agent', function () {
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36';

    expect(BotDetector::isBot($ua))->toBeFalse();
});
