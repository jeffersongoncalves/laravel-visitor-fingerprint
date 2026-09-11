<?php

use JeffersonGoncalves\VisitorFingerprint\Contracts\GeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\Contracts\VpnDetectionDriver;
use JeffersonGoncalves\VisitorFingerprint\Facades\VisitorFingerprint as VisitorFingerprintFacade;
use JeffersonGoncalves\VisitorFingerprint\GeoIp\HeadersGeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\GeoIp\IpApiGeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\VisitorFingerprint;
use JeffersonGoncalves\VisitorFingerprint\VpnDetection\IpApiVpnDetectionDriver;
use JeffersonGoncalves\VisitorFingerprint\VpnDetection\ProxyCheckVpnDetectionDriver;

it('publishes the config file', function () {
    expect(config('visitor-fingerprint'))->toBeArray()
        ->and(config('visitor-fingerprint.geoip.driver'))->toBe('headers')
        ->and(config('visitor-fingerprint.vpn_detection.driver'))->toBe('ip_api');
});

it('resolves the facade to the main class', function () {
    expect(VisitorFingerprintFacade::getFacadeRoot())->toBeInstanceOf(VisitorFingerprint::class);
});

it('binds the default geoip driver from config', function () {
    expect(app(GeoIpDriver::class))->toBeInstanceOf(HeadersGeoIpDriver::class);

    config()->set('visitor-fingerprint.geoip.driver', 'ip_api');

    expect(app(GeoIpDriver::class))->toBeInstanceOf(IpApiGeoIpDriver::class);
});

it('binds the default vpn detection driver from config', function () {
    expect(app(VpnDetectionDriver::class))->toBeInstanceOf(IpApiVpnDetectionDriver::class);

    config()->set('visitor-fingerprint.vpn_detection.driver', 'proxycheck');

    expect(app(VpnDetectionDriver::class))->toBeInstanceOf(ProxyCheckVpnDetectionDriver::class);
});
