<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\VisitorFingerprint\VpnDetection\IpApiVpnDetectionDriver;
use JeffersonGoncalves\VisitorFingerprint\VpnDetection\ProxyCheckVpnDetectionDriver;

dataset('vpn_drivers', [
    'ip_api' => [IpApiVpnDetectionDriver::class],
    'proxycheck' => [ProxyCheckVpnDetectionDriver::class],
]);

it('returns a clean result without reporting on connection timeout', function (string $driverClass) {
    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    $result = app($driverClass)->check('98.95.129.148');

    expect($result->isProxy)->toBeFalse()
        ->and($result->isVpn)->toBeFalse();
})->with('vpn_drivers');
