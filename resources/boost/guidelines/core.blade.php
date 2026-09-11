## Laravel Visitor Fingerprint Package

The `jeffersongoncalves/laravel-visitor-fingerprint` package is a zero-domain-knowledge visitor-fingerprinting toolkit: device/browser/OS detection, IP anonymization, bot detection, GeoIP, VPN/proxy/Tor detection, and GDPR export/erasure. It has no concept of "short URL" or "page visit" — those belong to whatever package consumes it.

### Package Namespace

All classes are under `JeffersonGoncalves\VisitorFingerprint`.

### Architecture

- **Facade**: `JeffersonGoncalves\VisitorFingerprint\Facades\VisitorFingerprint` — thin wrapper over the Support classes plus the container-bound GeoIP/VPN drivers
- **Support** (`src/Support/`, pure static helpers, no I/O): `UserAgentParser`, `IpAnonymizer`, `BotDetector`, `AcceptLanguage`, `RefererClassifier`
- **Contracts**: `Contracts\GeoIpDriver`, `Contracts\VpnDetectionDriver` — swap the bound implementation instead of rebinding call sites
- **Drivers**: `GeoIp\{Headers,IpApi,MaxMind}GeoIpDriver`, `VpnDetection\{IpApi,ProxyCheck}VpnDetectionDriver` — selected by `visitor-fingerprint.geoip.driver` / `visitor-fingerprint.vpn_detection.driver`
- **Compliance**: `Compliance\PersonalDataExporter` — generic, works against any consumer's own Eloquent model

### Key Conventions

- All drivers are best-effort: a lookup failure (timeout, missing API key, missing MaxMind database) yields an empty/clean result rather than raising — never let a third party being down break the caller
- `IpAnonymizer::hash()` is salted via `visitor-fingerprint.hash_salt`; rotating the salt breaks continuity of anything a consumer matched by hash, by design
- Configuration lives in `config/visitor-fingerprint.php`, fully commented inline

### Basic Usage

@verbatim
<code-snippet name="Fingerprinting a request" lang="php">
use JeffersonGoncalves\VisitorFingerprint\Facades\VisitorFingerprint;

$deviceType = VisitorFingerprint::deviceType($request->userAgent());
$isBot = VisitorFingerprint::isBot($request->userAgent());
$location = VisitorFingerprint::geoLocate($request->ip());
$threat = VisitorFingerprint::checkThreat($request->ip());
</code-snippet>
@endverbatim

### GDPR / LGPD Erasure Against a Consumer's Model

@verbatim
<code-snippet name="Exporting and forgetting visits for an IP" lang="php">
use JeffersonGoncalves\VisitorFingerprint\Compliance\PersonalDataExporter;

$exporter = new PersonalDataExporter(\App\Models\Visit::class, ipHashColumn: 'ip_hash');

$exporter->exportForIp($ip);
$exporter->forgetForIp($ip); // nulls only the PII columns present on the target table
</code-snippet>
@endverbatim
