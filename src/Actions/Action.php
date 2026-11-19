<?php

namespace SimpleAnalytics\Actions;

defined('ABSPATH') || exit;

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
