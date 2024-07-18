<?php

namespace SimpleAnalytics\Fluent;

class Str
{
    public static function of(string $value): Stringable
    {
        return new Stringable($value);
    }

    public static function slug(string $value): Stringable
    {
        return (new Stringable($value))->slug();
    }
}
