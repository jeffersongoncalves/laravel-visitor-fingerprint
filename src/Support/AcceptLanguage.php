<?php

namespace JeffersonGoncalves\VisitorFingerprint\Support;

class AcceptLanguage
{
    public static function preferred(?string $acceptLanguageHeader): ?string
    {
        if (! $acceptLanguageHeader) {
            return null;
        }

        $first = explode(',', $acceptLanguageHeader)[0];

        return trim(explode(';', $first)[0]) ?: null;
    }
}
