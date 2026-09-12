<?php

declare(strict_types=1);

namespace JeffersonGoncalves\VisitorFingerprint\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Downloads MaxMind's GeoLite2-City and GeoLite2-ASN databases and swaps them
 * into place for the "maxmind" GeoIP driver (config
 * visitor-fingerprint.geoip.maxmind_database_path /
 * maxmind_asn_database_path). MaxMind reissues GeoLite2 roughly twice a week
 * as IP ranges get reassigned — a consuming app typically schedules this
 * weekly, e.g. `Schedule::command('geoip:update')->weeklyOn(1, '02:00')`.
 *
 * Each database is only replaced after its own full, verified download +
 * extraction — a failed run (missing license key, network error, corrupt
 * archive) leaves that database on the last-known-good file instead of
 * breaking it. The two editions are independent: a failure downloading one
 * doesn't stop the other from updating.
 */
class UpdateMaxMindDatabasesCommand extends Command
{
    protected $signature = 'geoip:update';

    protected $description = 'Download and install the latest MaxMind GeoLite2 City and ASN databases';

    /** @var array<string, string> edition => config key for its target path */
    private const EDITIONS = [
        'GeoLite2-City' => 'visitor-fingerprint.geoip.maxmind_database_path',
        'GeoLite2-ASN' => 'visitor-fingerprint.geoip.maxmind_asn_database_path',
    ];

    public function handle(): int
    {
        $licenseKey = config('visitor-fingerprint.geoip.maxmind_license_key');

        if (! is_string($licenseKey) || $licenseKey === '') {
            $this->error('No MaxMind license key configured — get a free key at https://www.maxmind.com/en/geolite2/signup-form');

            return self::FAILURE;
        }

        $exitCode = self::SUCCESS;

        foreach (self::EDITIONS as $edition => $configKey) {
            if (! $this->updateEdition($edition, (string) config($configKey), $licenseKey)) {
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }

    private function updateEdition(string $edition, string $targetPath, string $licenseKey): bool
    {
        $workDir = dirname($targetPath).'/.tmp-'.uniqid();

        try {
            @mkdir($workDir, 0755, recursive: true);

            $archivePath = "{$workDir}/{$edition}.tar.gz";
            $this->download($edition, $licenseKey, $archivePath);

            $mmdbPath = $this->extractMmdb($edition, $archivePath, $workDir);

            @mkdir(dirname($targetPath), 0755, recursive: true);
            // rename() is atomic on the same filesystem — readers never see a
            // half-written database.
            if (! rename($mmdbPath, $targetPath)) {
                throw new RuntimeException("Could not move the extracted database to {$targetPath}.");
            }

            $this->info("{$edition} database updated at {$targetPath}.");

            return true;
        } catch (RuntimeException $e) {
            Log::error('geoip:update failed', ['edition' => $edition, 'error' => $e->getMessage()]);
            $this->error($e->getMessage());

            return false;
        } finally {
            $this->cleanUp($workDir);
        }
    }

    private function download(string $edition, string $licenseKey, string $destination): void
    {
        $response = Http::timeout(60)->get('https://download.maxmind.com/app/geoip_download', [
            'edition_id' => $edition,
            'license_key' => $licenseKey,
            'suffix' => 'tar.gz',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("MaxMind download of {$edition} failed with HTTP {$response->status()}.");
        }

        file_put_contents($destination, $response->body());
    }

    /**
     * MaxMind ships <edition>_<date>.tar.gz containing one
     * <edition>_<date>/<edition>.mmdb — the exact date-stamped folder name
     * isn't predictable, so the file is located by extension after
     * extracting.
     */
    private function extractMmdb(string $edition, string $archivePath, string $workDir): string
    {
        $phar = new \PharData($archivePath);
        $phar->decompress(); // .tar.gz -> .tar, written alongside it.

        $tarPath = substr($archivePath, 0, -3); // strip ".gz"
        (new \PharData($tarPath))->extractTo($workDir);

        $matches = glob("{$workDir}/*/{$edition}.mmdb");

        if ($matches === false || $matches === []) {
            throw new RuntimeException("Extracted archive did not contain a {$edition}.mmdb file.");
        }

        return $matches[0];
    }

    private function cleanUp(string $workDir): void
    {
        if (! is_dir($workDir)) {
            return;
        }

        foreach (glob($workDir.'/*') ?: [] as $entry) {
            is_dir($entry) ? $this->removeDirectory($entry) : @unlink($entry);
        }

        @rmdir($workDir);
    }

    private function removeDirectory(string $dir): void
    {
        foreach (glob($dir.'/*') ?: [] as $entry) {
            is_dir($entry) ? $this->removeDirectory($entry) : @unlink($entry);
        }

        @rmdir($dir);
    }
}
