<?php

use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\VisitorFingerprint\Compliance\PersonalDataExporter;
use JeffersonGoncalves\VisitorFingerprint\Support\IpAnonymizer;
use JeffersonGoncalves\VisitorFingerprint\Tests\Fixtures\TestVisit;

beforeEach(function () {
    Schema::create('test_visits', function ($table) {
        $table->id();
        $table->string('ip_hash')->nullable();
        $table->string('ip_anonymized')->nullable();
        $table->unsignedTinyInteger('ip_version')->nullable();
        $table->string('user_agent_hash')->nullable();
        $table->string('url')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_visits');
});

it('exports rows matching the hashed ip', function () {
    $ip = '203.0.113.7';

    TestVisit::create([
        'ip_hash' => IpAnonymizer::hash($ip),
        'ip_anonymized' => IpAnonymizer::truncate($ip),
        'ip_version' => IpAnonymizer::version($ip),
        'user_agent_hash' => hash('sha256', 'ua'),
        'url' => '/some-page',
    ]);

    TestVisit::create([
        'ip_hash' => IpAnonymizer::hash('198.51.100.1'),
        'url' => '/other-page',
    ]);

    $exporter = new PersonalDataExporter(TestVisit::class);

    $result = $exporter->exportForIp($ip);

    expect($result)->toHaveCount(1)
        ->and($result[0]['url'])->toBe('/some-page');
});

it('nulls only the pii columns and leaves the rest of the row intact', function () {
    $ip = '203.0.113.7';

    $visit = TestVisit::create([
        'ip_hash' => IpAnonymizer::hash($ip),
        'ip_anonymized' => IpAnonymizer::truncate($ip),
        'ip_version' => IpAnonymizer::version($ip),
        'user_agent_hash' => hash('sha256', 'ua'),
        'url' => '/some-page',
    ]);

    $exporter = new PersonalDataExporter(TestVisit::class);

    $affected = $exporter->forgetForIp($ip);

    expect($affected)->toBe(1);

    $visit->refresh();

    expect($visit->ip_hash)->toBeNull()
        ->and($visit->ip_anonymized)->toBeNull()
        ->and($visit->ip_version)->toBeNull()
        ->and($visit->user_agent_hash)->toBeNull()
        ->and($visit->url)->toBe('/some-page');
});

it('supports a custom ip hash column name', function () {
    Schema::table('test_visits', function ($table) {
        $table->string('visitor_ip_hash')->nullable();
    });

    $ip = '203.0.113.7';

    TestVisit::create([
        'visitor_ip_hash' => IpAnonymizer::hash($ip),
        'url' => '/custom-column',
    ]);

    $exporter = new PersonalDataExporter(TestVisit::class, 'visitor_ip_hash');

    expect($exporter->exportForIp($ip))->toHaveCount(1);
});
