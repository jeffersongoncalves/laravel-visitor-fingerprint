<?php

namespace JeffersonGoncalves\VisitorFingerprint;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class VisitorFingerprintServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-visitor-fingerprint')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
