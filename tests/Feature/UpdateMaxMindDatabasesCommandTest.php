<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

function buildFakeGeoLiteArchive(string $edition, string $destination): void
{
    // Source content and the tar being built live in separate directories —
    // building the tar into the same tree it's reading would otherwise
    // recursively pick up its own (still-growing) output file.
    $srcDir = sys_get_temp_dir().'/geoip-src-'.uniqid();
    $folder = $srcDir.'/'.$edition.'_20260101';
    mkdir($folder, 0755, recursive: true);
    file_put_contents($folder.'/'.$edition.'.mmdb', 'fake-'.$edition.'-bytes');

    $outDir = sys_get_temp_dir().'/geoip-out-'.uniqid();
    mkdir($outDir, 0755, recursive: true);
    $tarPath = $outDir.'/'.$edition.'.tar';

    $tar = new PharData($tarPath);
    $tar->buildFromDirectory($srcDir);
    $tar->compress(Phar::GZ);
    unset($tar);

    rename($tarPath.'.gz', $destination);

    unlink($tarPath);
    unlink($folder.'/'.$edition.'.mmdb');
    rmdir($folder);
    rmdir($srcDir);
    rmdir($outDir);
}

it('fails cleanly with no license key configured', function () {
    config(['visitor-fingerprint.geoip.maxmind_license_key' => null]);

    $this->artisan('geoip:update')->assertFailed();
});

it('downloads, extracts and installs both the city and asn databases', function () {
    $cityTarget = sys_get_temp_dir().'/geoip-test-'.uniqid().'/GeoLite2-City.mmdb';
    $asnTarget = sys_get_temp_dir().'/geoip-test-'.uniqid().'/GeoLite2-ASN.mmdb';
    config([
        'visitor-fingerprint.geoip.maxmind_license_key' => 'test-key',
        'visitor-fingerprint.geoip.maxmind_database_path' => $cityTarget,
        'visitor-fingerprint.geoip.maxmind_asn_database_path' => $asnTarget,
    ]);

    $cityFixture = sys_get_temp_dir().'/geoip-archive-'.uniqid().'.tar.gz';
    $asnFixture = sys_get_temp_dir().'/geoip-archive-'.uniqid().'.tar.gz';
    buildFakeGeoLiteArchive('GeoLite2-City', $cityFixture);
    buildFakeGeoLiteArchive('GeoLite2-ASN', $asnFixture);

    Http::swap(new Factory);
    Http::fake(function ($request) use ($cityFixture, $asnFixture) {
        $edition = $request->data()['edition_id'] ?? null;

        return match ($edition) {
            'GeoLite2-City' => Http::response(file_get_contents($cityFixture), 200),
            'GeoLite2-ASN' => Http::response(file_get_contents($asnFixture), 200),
            default => Http::response('', 404),
        };
    });

    $this->artisan('geoip:update')->assertSuccessful();

    expect(file_get_contents($cityTarget))->toBe('fake-GeoLite2-City-bytes')
        ->and(file_get_contents($asnTarget))->toBe('fake-GeoLite2-ASN-bytes');

    unlink($cityFixture);
    unlink($asnFixture);
});

it('updates the city database even when the asn download fails', function () {
    $cityTarget = sys_get_temp_dir().'/geoip-test-'.uniqid().'/GeoLite2-City.mmdb';
    $asnTarget = sys_get_temp_dir().'/geoip-test-'.uniqid().'/GeoLite2-ASN.mmdb';
    config([
        'visitor-fingerprint.geoip.maxmind_license_key' => 'test-key',
        'visitor-fingerprint.geoip.maxmind_database_path' => $cityTarget,
        'visitor-fingerprint.geoip.maxmind_asn_database_path' => $asnTarget,
    ]);

    $cityFixture = sys_get_temp_dir().'/geoip-archive-'.uniqid().'.tar.gz';
    buildFakeGeoLiteArchive('GeoLite2-City', $cityFixture);

    Http::swap(new Factory);
    Http::fake(function ($request) use ($cityFixture) {
        $edition = $request->data()['edition_id'] ?? null;

        return match ($edition) {
            'GeoLite2-City' => Http::response(file_get_contents($cityFixture), 200),
            default => Http::response('', 500),
        };
    });

    $this->artisan('geoip:update')->assertFailed();

    expect(file_get_contents($cityTarget))->toBe('fake-GeoLite2-City-bytes')
        ->and(file_exists($asnTarget))->toBeFalse();

    unlink($cityFixture);
});
