<?php

namespace SimpleAnalytics\Support;

class Str
{
    public static function slug(string $value): string
    {
        return strtolower(str_replace(' ', '-', $value));
    }
}
