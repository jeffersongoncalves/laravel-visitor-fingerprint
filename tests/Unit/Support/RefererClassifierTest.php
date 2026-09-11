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

it('classifies an absent referer as direct', function () {
    expect(RefererClassifier::classify(null, 'example.com'))->toBe('direct')
        ->and(RefererClassifier::classify('', 'example.com'))->toBe('direct');
});

it('classifies an unrecognized referrer as direct', function () {
    expect(RefererClassifier::classify('https://some-random-blog.test/post', 'example.com'))->toBe('direct');
});
