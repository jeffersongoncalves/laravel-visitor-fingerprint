# Changelog

All notable changes to this project will be documented in this file.

## 1.0.5 - 2026-09-19

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-visitor-fingerprint/compare/1.0.4...1.0.5

## 1.0.4 - 2026-09-13

### What's Changed

* fix: make VPN detection timeout configurable, stop logging expected timeouts as errors by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-visitor-fingerprint/pull/3

### New Contributors

* @jeffersongoncalves made their first contribution in https://github.com/jeffersongoncalves/laravel-visitor-fingerprint/pull/3

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-visitor-fingerprint/compare/1.0.3...1.0.4

## 1.0.3 - 2026-09-12

Move the geoip:update MaxMind database download command into this package.

Previously app-owned, tightly coupled to this package's own config keys/driver. Now auto-registered via the service provider — a consuming app just schedules 'geoip:update', no custom command needed. License key config falls back to the plain MAXMIND_LICENSE_KEY env var.

## 1.0.2 - 2026-09-12

Populate isp/asn when using the maxmind geoip driver.

GeoLite2-City has no ISP/ASN fields — MaxMindGeoIpDriver now also reads an optional separate GeoLite2-ASN database (visitor-fingerprint.geoip.maxmind_asn_database_path) to fill isp/asn, matching the ip_api driver's shape. Missing ASN database degrades to the prior city-only behavior, no error.

## 1.0.1 - 2026-09-11

Fixed

- geoip.maxmind_database_path now defaults to
  storage_path('app/geoip/GeoLite2-City.mmdb') instead of null, matching
  jeffersongoncalves/laravel-short-url's config default. An app already
  running a scheduled MaxMind download for short-url gets GeoIP working
  here too without setting VISITOR_FINGERPRINT_MAXMIND_DB_PATH — both
  packages read the same database file off disk.

## 1.0.0 - 2026-09-11

Initial release.

- UA parsing (browser/version/OS/device type)
- IP anonymization (IPv4 /24, IPv6 /48) and salted hashing
- Bot detection
- Accept-Language preference parsing
- Referer classification (social/search/email/internal/direct)
- GeoIP resolution: headers, ip-api, MaxMind drivers
- VPN/proxy/Tor detection: ip-api, ProxyCheck drivers
- GDPR-style personal data export/erasure, generic over any Eloquent model

Extracted from jeffersongoncalves/laravel-short-url into a standalone,
domain-agnostic package.

## [Unreleased]
