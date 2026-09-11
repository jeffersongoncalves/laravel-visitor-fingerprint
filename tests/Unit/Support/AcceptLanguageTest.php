<?php

use JeffersonGoncalves\VisitorFingerprint\Support\AcceptLanguage;

it('returns a single language value as-is', function () {
    expect(AcceptLanguage::preferred('pt-BR'))->toBe('pt-BR');
});

it('picks the first entry when multiple values with q-values are present', function () {
    expect(AcceptLanguage::preferred('en-US,en;q=0.9,pt-BR;q=0.8'))->toBe('en-US');
});

it('returns null for an empty or null header', function () {
    expect(AcceptLanguage::preferred(null))->toBeNull()
        ->and(AcceptLanguage::preferred(''))->toBeNull();
});
