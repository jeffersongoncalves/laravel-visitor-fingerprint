# Changelog

All notable changes to this project will be documented in this file.

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
