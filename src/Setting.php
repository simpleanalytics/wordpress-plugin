<?php

namespace SimpleAnalytics;

class Setting
{
    /**
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $value = get_option($key);

        if (empty($value)) {
            return $default;
        }

        return $value;
    }

    public static function boolean(string $key, ?bool $default = null): ?bool
    {
        $value = get_option($key);

        if (empty($value)) {
            return $default;
        }

        return (bool)$value;
    }

    public static function array(string $key): array
    {
        $value = self::get($key, []);

        return is_array($value) ? $value : [$value];
    }
}
