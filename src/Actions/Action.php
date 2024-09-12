<?php

namespace SimpleAnalytics\Actions;

trait Action
{
    protected function hook(): string
    {
        return $this->hook;
    }

    public static function register(...$args): void
    {
        $instance = new static(...$args);

        add_action($instance->hook(), [$instance, 'handle']);
    }
}
