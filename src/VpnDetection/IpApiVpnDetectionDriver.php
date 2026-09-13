<?php

namespace JeffersonGoncalves\VisitorFingerprint\VpnDetection;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\VisitorFingerprint\Contracts\VpnDetectionDriver;
use JeffersonGoncalves\VisitorFingerprint\Data\ThreatResult;
use Throwable;

/**
 * ip-api.com's free tier exposes `proxy` (VPN/proxy/Tor) and `hosting`
 * (datacenter ASN) fields — enough for a flag/block decision without a
 * paid key. Result is cached per IP; any failure yields a "clean" verdict
 * rather than raising on a third party being down.
 */
class IpApiVpnDetectionDriver implements VpnDetectionDriver
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
            $response = Http::timeout((float) config('visitor-fingerprint.vpn_detection.timeout', 2.0))
                ->get("http://ip-api.com/json/{$ip}", ['fields' => 'status,proxy,hosting']);

            if (! $response->successful() || $response->json('status') !== 'success') {
                return new ThreatResult(provider: 'ip_api');
            }

            $isProxy = (bool) $response->json('proxy', false);
            $isDatacenter = (bool) $response->json('hosting', false);

            return new ThreatResult(
                isProxy: $isProxy,
                isDatacenter: $isDatacenter,
                confidence: $isProxy || $isDatacenter ? 0.6 : 0.0,
                provider: 'ip_api',
            );
        } catch (ConnectionException) {
            return new ThreatResult(provider: 'ip_api');
        } catch (Throwable $e) {
            report($e);

            return new ThreatResult(provider: 'ip_api');
        }
    }

    protected function cacheKey(string $ip): string
    {
        return "visitor_fingerprint:vpn:ip_api:{$ip}";
    }
}
