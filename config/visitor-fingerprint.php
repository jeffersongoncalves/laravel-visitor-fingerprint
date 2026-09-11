<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hash Salt
    |--------------------------------------------------------------------------
    |
    | Salt mixed into every hashed value this package produces (IP hashes,
    | user agent hashes, ...). Rotate periodically for privacy; rotating it
    | breaks continuity of anything a consumer package matched by hash
    | (e.g. unique-visitor counting) by design.
    |
    */
    'hash_salt' => env('VISITOR_FINGERPRINT_HASH_SALT', config('app.key')),

    /*
    |--------------------------------------------------------------------------
    | GeoIP
    |--------------------------------------------------------------------------
    |
    | - driver: headers|ip_api|maxmind.
    |   - headers: trusts geo headers injected by a CDN/edge proxy in front
    |     of the app (Cloudflare, CloudFront, ...). Zero latency, zero
    |     external calls.
    |   - ip_api: free-tier ip-api.com HTTP lookup.
    |   - maxmind: reads a local MaxMind GeoLite2/GeoIP2 database (requires
    |     the optional geoip2/geoip2 package).
    | - maxmind_database_path: filesystem path to the MaxMind database,
    |   only read when driver is "maxmind".
    |
    */
    'geoip' => [
        'driver' => env('VISITOR_FINGERPRINT_GEOIP_DRIVER', 'headers'),
        'maxmind_database_path' => env('VISITOR_FINGERPRINT_MAXMIND_DB_PATH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | VPN / Proxy / Tor Detection
    |--------------------------------------------------------------------------
    |
    | - driver: ip_api|proxycheck. Both are best-effort: any failure yields
    |   a "clean" result instead of raising, so a third party being down
    |   never breaks the caller.
    | - proxycheck_api_key: optional key for proxycheck.io (works keyless,
    |   rate-limited, on the free tier).
    | - cache_ttl: seconds a per-IP lookup result is cached for.
    |
    */
    'vpn_detection' => [
        'driver' => env('VISITOR_FINGERPRINT_VPN_DRIVER', 'ip_api'),
        'proxycheck_api_key' => env('VISITOR_FINGERPRINT_PROXYCHECK_API_KEY'),
        'cache_ttl' => env('VISITOR_FINGERPRINT_VPN_CACHE_TTL', 3600),
    ],

];
