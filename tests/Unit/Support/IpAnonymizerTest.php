<?php

use JeffersonGoncalves\VisitorFingerprint\Support\IpAnonymizer;

it('truncates an ipv4 address to its /24', function () {
    expect(IpAnonymizer::truncate('192.168.10.42'))->toBe('192.168.10.0');
});

it('truncates an ipv6 address to its /48', function () {
    expect(IpAnonymizer::truncate('2001:db8:1234:5678::1'))->toBe('2001:0db8:1234::');
});

it('detects the ip version', function () {
    expect(IpAnonymizer::version('192.168.10.42'))->toBe(4)
        ->and(IpAnonymizer::version('2001:db8::1'))->toBe(6);
});

it('hashes deterministically for the same salt', function () {
    config()->set('visitor-fingerprint.hash_salt', 'same-salt');

    expect(IpAnonymizer::hash('203.0.113.5'))->toBe(IpAnonymizer::hash('203.0.113.5'));
});

it('changes the hash when the salt changes', function () {
    config()->set('visitor-fingerprint.hash_salt', 'salt-one');
    $first = IpAnonymizer::hash('203.0.113.5');

    config()->set('visitor-fingerprint.hash_salt', 'salt-two');
    $second = IpAnonymizer::hash('203.0.113.5');

    expect($first)->not->toBe($second);
});
