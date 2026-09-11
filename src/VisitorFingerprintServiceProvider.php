<?php

namespace JeffersonGoncalves\VisitorFingerprint;

use JeffersonGoncalves\VisitorFingerprint\Contracts\GeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\Contracts\VpnDetectionDriver;
use JeffersonGoncalves\VisitorFingerprint\GeoIp\HeadersGeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\GeoIp\IpApiGeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\GeoIp\MaxMindGeoIpDriver;
use JeffersonGoncalves\VisitorFingerprint\VpnDetection\IpApiVpnDetectionDriver;
use JeffersonGoncalves\VisitorFingerprint\VpnDetection\ProxyCheckVpnDetectionDriver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class VisitorFingerprintServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-visitor-fingerprint')
            ->hasConfigFile('visitor-fingerprint');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('laravel-visitor-fingerprint', fn () => new VisitorFingerprint);

        $this->app->bind(GeoIpDriver::class, fn ($app) => match (config('visitor-fingerprint.geoip.driver', 'headers')) {
            'ip_api' => new IpApiGeoIpDriver,
            'maxmind' => new MaxMindGeoIpDriver,
            default => new HeadersGeoIpDriver($app['request']),
        });

        $this->app->bind(VpnDetectionDriver::class, fn () => match (config('visitor-fingerprint.vpn_detection.driver', 'ip_api')) {
            'proxycheck' => new ProxyCheckVpnDetectionDriver,
            default => new IpApiVpnDetectionDriver,
        });
    }
}
