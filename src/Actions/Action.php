<?php

namespace SimpleAnalytics\Actions;

abstract class Action
{
    protected string $hook;

    abstract public function handle();

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
