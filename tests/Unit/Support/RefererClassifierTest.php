<?php

use JeffersonGoncalves\VisitorFingerprint\Support\RefererClassifier;

it('classifies social referrers', function (string $url) {
    expect(RefererClassifier::classify($url, 'example.com'))->toBe('social');
})->with([
    'https://www.facebook.com/some-post',
    'https://t.co/abc123',
    'https://www.linkedin.com/feed',
]);

it('classifies search referrers', function (string $url) {
    expect(RefererClassifier::classify($url, 'example.com'))->toBe('search');
})->with([
    'https://www.google.com/search?q=test',
    'https://www.bing.com/search?q=test',
    'https://duckduckgo.com/?q=test',
]);

it('classifies email referrers', function () {
    expect(RefererClassifier::classify('https://mail.google.com/mail/u/0/', 'example.com'))->toBe('email');
});

it('classifies same-host referrers as internal', function () {
    expect(RefererClassifier::classify('https://www.example.com/page', 'example.com'))->toBe('internal');
});

it('classifies same-site referrers as internal when the app is served on www', function (string $url) {
    expect(RefererClassifier::classify($url, 'www.example.com'))->toBe('internal');
})->with([
    'https://www.example.com/page',
    'https://example.com/page',
]);

it('does not trust forged or look-alike referer hosts', function (string $url) {
    expect(RefererClassifier::classify($url, 'example.com'))->toBe('direct');
})->with([
    'https://www.google.example-tld/',
    'https://www.yahoo.example-tld/',
    'https://www.yandex.example-tld/',
    'https://microsoft.com/',
    'https://reddit.com.evil.io/',
]);

it('matches regional search domains', function () {
    expect(RefererClassifier::classify('https://www.google.com.br/search?q=x', 'example.com'))->toBe('search');
});

it('merges extra domains from config', function () {
    config()->set('visitor-fingerprint.referer_domains.search', ['google.com.co']);

    expect(RefererClassifier::classify('https://www.google.com.co/', 'example.com'))->toBe('search');
});

it('classifies an absent referer as direct', function () {
    expect(RefererClassifier::classify(null, 'example.com'))->toBe('direct')
        ->and(RefererClassifier::classify('', 'example.com'))->toBe('direct');
});

it('classifies an unrecognized referrer as direct', function () {
    expect(RefererClassifier::classify('https://some-random-blog.test/post', 'example.com'))->toBe('direct');
});
