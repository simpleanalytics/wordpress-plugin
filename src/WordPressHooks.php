<?php

namespace SimpleAnalytics;

class WordPressHooks
{
    public function addAction(string $name, $callback): void
    {
        add_action($name, $callback);
    }

    public function onActivation($callback): void
    {
        register_activation_hook(ENTRYPOINT_FILE, $callback);
    }

    public function onDeactivation($callback): void
    {
        register_deactivation_hook(ENTRYPOINT_FILE, $callback);
    }

    public function isAdmin(): bool
    {
        return is_admin();
    }
}
