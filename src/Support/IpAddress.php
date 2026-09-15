<?php

namespace SimpleAnalytics\Support;

class IpAddress
{
    public static function current(): ?string
    {
        // Preserve the existing preference for the visitor's forwarded address.
        // Reverse proxies must overwrite this header with a trustworthy value.
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        if (is_string($ip)) {
            $ip = explode(',', $ip, 2)[0];
        } elseif ($ip === null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        // Do not also match REMOTE_ADDR: a shared proxy IP could exclude every
        // visitor behind that proxy. Invalid forwarded values remain unmatched.
        return self::normalize(apply_filters('simpleanalytics_client_ip', $ip));
    }

    public static function normalize($value): ?string
    {
        if (! is_string($value)) return null;
        $value = trim($value);
        if (filter_var($value, FILTER_VALIDATE_IP) === false) return null;

        return inet_ntop(inet_pton($value));
    }
}
