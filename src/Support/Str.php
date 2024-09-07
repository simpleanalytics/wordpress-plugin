<?php

namespace SimpleAnalytics\Support;

class Str
{
    public static function slug(string $value): string
    {
        return strtolower(str_replace(' ', '-', $value));
    }

    public static function htmlAttributes(array $attributes): string
    {
        $result = '';

        foreach ($attributes as $key => $value) {
            $result .= sprintf("%s=\"%s\" ", $key, esc_attr($value));
        }

        return trim($result);
    }
}
