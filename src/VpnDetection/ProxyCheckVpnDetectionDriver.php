<?php

namespace JeffersonGoncalves\VisitorFingerprint\VpnDetection;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\VisitorFingerprint\Contracts\VpnDetectionDriver;
use JeffersonGoncalves\VisitorFingerprint\Data\ThreatResult;
use Throwable;

/**
 * proxycheck.io — free tier works keyless (rate-limited). Distinguishes
 * VPN from generic proxy via the "type" field.
 */
class ProxyCheckVpnDetectionDriver implements VpnDetectionDriver
{
    public function check(string $ip): ThreatResult
    {
        return Cache::remember(
            $this->cacheKey($ip),
            (int) config('visitor-fingerprint.vpn_detection.cache_ttl', 3600),
            fn () => $this->lookup($ip)
        );
    }

    protected function lookup(string $ip): ThreatResult
    {
        try {
            $apiKey = config('visitor-fingerprint.vpn_detection.proxycheck_api_key');
            $query = array_filter(['vpn' => 1, 'asn' => 1, 'key' => $apiKey]);

            $response = Http::timeout((float) config('visitor-fingerprint.vpn_detection.timeout', 2.0))
                ->get("https://proxycheck.io/v2/{$ip}", $query);

            if (! $response->successful()) {
                return new ThreatResult(provider: 'proxycheck_io');
            }

            // Plain array access, not json($ip) — the IP contains dots,
            // which Laravel's json()/Arr::get would parse as a nested path.
            $data = $response->json()[$ip] ?? [];

            if (! is_array($data) || ($data['proxy'] ?? 'no') !== 'yes') {
                return new ThreatResult(provider: 'proxycheck_io');
            }

            $type = strtolower((string) ($data['type'] ?? ''));

            return new ThreatResult(
                isVpn: $type === 'vpn',
                isProxy: true,
                isTor: $type === 'tor',
                confidence: 0.8,
                provider: 'proxycheck_io',
            );
        } catch (ConnectionException) {
            return new ThreatResult(provider: 'proxycheck_io');
        } catch (Throwable $e) {
            report($e);

            return new ThreatResult(provider: 'proxycheck_io');
        }
    }

    protected function cacheKey(string $ip): string
    {
        return "visitor_fingerprint:vpn:proxycheck:{$ip}";
    }
}
