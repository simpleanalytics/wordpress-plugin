<?php

namespace SimpleAnalytics\Support;

class IpAddress
{
    public static function current(): ?string
    {
        // Forwarded headers are not trustworthy without a deployment-specific
        // proxy policy. Let the web server normalize REMOTE_ADDR by default.
        return self::normalize(apply_filters('simpleanalytics_client_ip', $_SERVER['REMOTE_ADDR'] ?? null));
    }

    public static function normalize($value): ?string
    {
        if (! is_string($value)) return null;
        $value = trim($value);
        if (filter_var($value, FILTER_VALIDATE_IP) === false) return null;

        return inet_ntop(inet_pton($value));
    }
}
