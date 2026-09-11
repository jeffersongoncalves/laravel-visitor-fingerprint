<?php

namespace JeffersonGoncalves\VisitorFingerprint\Compliance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\VisitorFingerprint\Support\IpAnonymizer;

/**
 * LGPD/GDPR data-subject requests, scoped by IP hash. Reusable against any
 * consumer's own visit-like model — this package has no model of its own.
 * Matching is limited to whatever hash_salt was in effect when a row was
 * recorded — that's by design (see visitor-fingerprint.hash_salt).
 *
 * @template TModel of Model
 */
class PersonalDataExporter
{
    /**
     * @var list<string>
     */
    protected const PII_COLUMNS = ['ip_hash', 'ip_anonymized', 'ip_version', 'user_agent_hash'];

    /**
     * @param  class-string<TModel>  $modelClass
     */
    public function __construct(
        protected string $modelClass,
        protected string $ipHashColumn = 'ip_hash',
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function exportForIp(string $ip): array
    {
        return $this->modelClass::query()
            ->where($this->ipHashColumn, IpAnonymizer::hash($ip))
            ->get()
            ->map(fn (Model $model) => $model->toArray())
            ->all();
    }

    /**
     * Right to erasure: strips identifying fields from matching rows in
     * place rather than deleting them, so historical aggregates built on
     * top stay accurate. Only nulls columns that actually exist on the
     * target model, so it never fails against a table missing some of
     * them.
     */
    public function forgetForIp(string $ip): int
    {
        $table = (new $this->modelClass)->getTable();
        $columns = array_intersect(self::PII_COLUMNS, Schema::getColumnListing($table));

        if (empty($columns)) {
            return 0;
        }

        return $this->modelClass::query()
            ->where($this->ipHashColumn, IpAnonymizer::hash($ip))
            ->update(array_fill_keys($columns, null));
    }
}
