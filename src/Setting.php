<?php

namespace SimpleAnalytics;

class Setting
{
    public static function get(string $key, $default = null)
    {
        $value = get_option(self::getKey($key));

        if (empty($value)) {
            return $default;
        }

        return $value;
    }

    public static function getKey(string $key): string
    {
        return "simpleanalytics_{$key}";
    }
}
