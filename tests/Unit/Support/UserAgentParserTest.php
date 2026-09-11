<?php

use JeffersonGoncalves\VisitorFingerprint\Support\UserAgentParser;

it('detects device type', function () {
    expect(UserAgentParser::fastDeviceType('Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X)'))->toBe('tablet')
        ->and(UserAgentParser::fastDeviceType('Mozilla/5.0 (Linux; Android 14; Pixel 8)'))->toBe('mobile')
        ->and(UserAgentParser::fastDeviceType('Mozilla/5.0 (Windows NT 10.0; Win64; x64)'))->toBe('desktop');
});

it('parses chrome on windows', function () {
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36';

    expect(UserAgentParser::parse($ua))->toBe([
        'browser' => 'Chrome',
        'browser_version' => '128.0.0.0',
        'operating_system' => 'Windows',
        'operating_system_version' => '10.0',
    ]);
});

it('parses safari on macos', function () {
    $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15';

    $parsed = UserAgentParser::parse($ua);

    expect($parsed['browser'])->toBe('Safari')
        ->and($parsed['browser_version'])->toBe('17.5')
        ->and($parsed['operating_system'])->toBe('macOS')
        ->and($parsed['operating_system_version'])->toBe('14.5');
});

it('parses firefox on linux', function () {
    $ua = 'Mozilla/5.0 (X11; Linux x86_64; rv:129.0) Gecko/20100101 Firefox/129.0';

    $parsed = UserAgentParser::parse($ua);

    expect($parsed['browser'])->toBe('Firefox')
        ->and($parsed['browser_version'])->toBe('129.0')
        ->and($parsed['operating_system'])->toBe('Linux');
});

it('parses safari on ios', function () {
    $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1';

    $parsed = UserAgentParser::parse($ua);

    expect($parsed['operating_system'])->toBe('iOS')
        ->and($parsed['operating_system_version'])->toBe('17.5');
});

it('returns nulls for an unrecognized user agent', function () {
    expect(UserAgentParser::parse('some-unknown-client/1.0'))->toBe([
        'browser' => null,
        'browser_version' => null,
        'operating_system' => null,
        'operating_system_version' => null,
    ]);
});
