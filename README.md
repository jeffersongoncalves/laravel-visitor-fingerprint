<div class="filament-hidden">

![Laravel Visitor Fingerprint](https://raw.githubusercontent.com/jeffersongoncalves/laravel-visitor-fingerprint/main/art/jeffersongoncalves-laravel-visitor-fingerprint.png)

</div>

# Laravel Visitor Fingerprint

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-visitor-fingerprint.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-visitor-fingerprint)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-visitor-fingerprint/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-visitor-fingerprint/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-visitor-fingerprint/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-visitor-fingerprint/actions?query=workflow%3A%22Fix+PHP+code+styling%22+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-visitor-fingerprint.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-visitor-fingerprint)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-visitor-fingerprint.svg?style=flat-square)](LICENSE.md)

A zero-domain-knowledge visitor-fingerprinting toolkit for Laravel: given an HTTP request, safely identify device/browser/OS, anonymize/hash the IP, detect bots, resolve GeoIP, detect VPN/proxy/Tor, and support GDPR export/erasure. This package has no concept of "short URL" or "page visit" — those are concerns of the packages that consume it.

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-visitor-fingerprint
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="visitor-fingerprint-config"
```

## Usage

### User agent, bot, language, referer

```php
use JeffersonGoncalves\VisitorFingerprint\Facades\VisitorFingerprint;

VisitorFingerprint::deviceType($request->userAgent());     // 'desktop' | 'mobile' | 'tablet'
VisitorFingerprint::parseUserAgent($request->userAgent());  // ['browser' => 'Chrome', 'browser_version' => '128.0.0.0', 'operating_system' => 'Windows', 'operating_system_version' => '10.0']
VisitorFingerprint::isBot($request->userAgent());           // bool

VisitorFingerprint::preferredLanguage($request->header('Accept-Language'));  // 'en-US'
VisitorFingerprint::classifyReferer($request->header('Referer'), $request->getHost()); // 'social' | 'search' | 'email' | 'internal' | 'direct'
```

### IP anonymization and hashing

```php
use JeffersonGoncalves\VisitorFingerprint\Support\IpAnonymizer;

IpAnonymizer::truncate($request->ip()); // '203.0.113.0' (IPv4 /24) or '2001:0db8:1234::' (IPv6 /48)
IpAnonymizer::hash($request->ip());     // salted sha256, safe to store for uniqueness comparisons
IpAnonymizer::version($request->ip());  // 4 or 6
```

### GeoIP

```php
use JeffersonGoncalves\VisitorFingerprint\Facades\VisitorFingerprint;

$location = VisitorFingerprint::geoLocate($request->ip());
$location->country; $location->city; $location->latitude; $location->longitude;
$location->isp; $location->asn; // only populated by the ip_api driver, or the maxmind driver when an ASN database is configured (see below)
```

Driver is selected via `visitor-fingerprint.geoip.driver`: `headers` (trusts CDN-injected geo headers, e.g. Cloudflare/CloudFront — no isp/asn), `ip_api` (free-tier `ip-api.com` HTTP lookup, includes isp/asn), or `maxmind` (local MaxMind database, requires `geoip2/geoip2`).

The `maxmind` driver reads two **separate** MaxMind databases: GeoLite2-City (country/region/city/coordinates) has no ISP/ASN fields at all — those only exist in GeoLite2-ASN. Configure both paths and this package's own `geoip:update` command keeps them fresh:

```bash
php artisan geoip:update
```

Downloads and installs both editions with your MaxMind license key (`visitor-fingerprint.geoip.maxmind_license_key`, falls back to plain `MAXMIND_LICENSE_KEY`). Each edition is independent — a failure on one doesn't block the other from updating, and a failed run never touches the last-known-good file. Schedule it, e.g.:

```php
// routes/console.php
Schedule::command('geoip:update')->weeklyOn(1, '02:00');
```

The ASN database is optional — without it (or with the `headers`/`ip_api` driver), isp/asn just stay `null`, same best-effort behavior as everything else in this package.

### VPN / proxy / Tor detection

```php
use JeffersonGoncalves\VisitorFingerprint\Facades\VisitorFingerprint;

$threat = VisitorFingerprint::checkThreat($request->ip());
$threat->isVpn; $threat->isProxy; $threat->isTor; $threat->isDatacenter; $threat->confidence;
```

Driver is selected via `visitor-fingerprint.vpn_detection.driver`: `ip_api` or `proxycheck`. Both are best-effort — any lookup failure yields a "clean" result instead of raising, and results are cached per IP.

### GDPR / LGPD export and erasure

`PersonalDataExporter` is reusable against any consumer's own visit-like Eloquent model — this package has no model of its own:

```php
use JeffersonGoncalves\VisitorFingerprint\Compliance\PersonalDataExporter;

$exporter = new PersonalDataExporter(\App\Models\Visit::class, ipHashColumn: 'ip_hash');

$exporter->exportForIp($ip);  // rows matching the hashed IP, as arrays
$exporter->forgetForIp($ip);  // nulls only the PII columns that exist on the model's table
```

## Configuration

```php
// config/visitor-fingerprint.php
return [
    'hash_salt' => env('VISITOR_FINGERPRINT_HASH_SALT', config('app.key')),

    'geoip' => [
        'driver' => env('VISITOR_FINGERPRINT_GEOIP_DRIVER', 'headers'),
        'maxmind_database_path' => env('VISITOR_FINGERPRINT_MAXMIND_DB_PATH', storage_path('app/geoip/GeoLite2-City.mmdb')),
        'maxmind_asn_database_path' => env('VISITOR_FINGERPRINT_MAXMIND_ASN_DB_PATH', storage_path('app/geoip/GeoLite2-ASN.mmdb')),
        'maxmind_license_key' => env('VISITOR_FINGERPRINT_MAXMIND_LICENSE_KEY', env('MAXMIND_LICENSE_KEY')),
    ],

    'vpn_detection' => [
        'driver' => env('VISITOR_FINGERPRINT_VPN_DRIVER', 'ip_api'),
        'proxycheck_api_key' => env('VISITOR_FINGERPRINT_PROXYCHECK_API_KEY'),
        'cache_ttl' => env('VISITOR_FINGERPRINT_VPN_CACHE_TTL', 3600),
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email the author instead of using the issue tracker.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
