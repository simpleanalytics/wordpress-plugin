<?php

namespace SimpleAnalytics\Foundation;

class Str
{
    public static function slug(string $value): string
    {
        return strtolower(str_replace(' ', '-', $value));
    }
}
