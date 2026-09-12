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
    | - maxmind_database_path: filesystem path to the MaxMind City database, only
    |   read when driver is "maxmind". Defaults to the same location
    |   jeffersongoncalves/laravel-short-url's config defaults to
    |   (storage/app/geoip/GeoLite2-City.mmdb) so an app already running a
    |   scheduled `geoip:update`-style download for that package doesn't
    |   need to duplicate the file or set this — both packages just read
    |   the one database off disk.
    | - maxmind_asn_database_path: filesystem path to the MaxMind ASN database
    |   (a separate .mmdb from the City one — GeoLite2-City has no ISP/ASN
    |   fields at all). Optional: isp/asn simply stay null if this file isn't
    |   present, same best-effort behavior as everything else here.
    |
    */
    'geoip' => [
        'driver' => env('VISITOR_FINGERPRINT_GEOIP_DRIVER', 'headers'),
        'maxmind_database_path' => env('VISITOR_FINGERPRINT_MAXMIND_DB_PATH', storage_path('app/geoip/GeoLite2-City.mmdb')),
        'maxmind_asn_database_path' => env('VISITOR_FINGERPRINT_MAXMIND_ASN_DB_PATH', storage_path('app/geoip/GeoLite2-ASN.mmdb')),

        /*
         * Free account + license key: https://www.maxmind.com/en/geolite2/signup-form
         * Used by the `geoip:update` command this package registers to download
         * and install both databases above. Falls back to the plain
         * MAXMIND_LICENSE_KEY name so an app already setting that for another
         * purpose doesn't need a second env var.
         */
        'maxmind_license_key' => env('VISITOR_FINGERPRINT_MAXMIND_LICENSE_KEY', env('MAXMIND_LICENSE_KEY')),
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
