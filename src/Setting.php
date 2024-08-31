<?php

namespace SimpleAnalytics;

class Setting
{
    public static function get(string $key, $default = null)
    {
        $value = get_option($key);

        if (empty($value)) {
            return $default;
        }

        return $value;
    }
}
