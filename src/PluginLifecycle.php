<?php

namespace SimpleAnalytics;

trait PluginLifecycle
{
    public function boot(): void
    {
        $this->onBoot();
        add_action('init', $this->onInit(...));
        register_activation_hook(ENTRYPOINT_FILE, $this->onActivation(...));
        register_deactivation_hook(ENTRYPOINT_FILE, $this->onDeactivation(...));
    }

    abstract protected function onBoot(): void;

    abstract public function onInit(): void;

    abstract public function onActivation(): void;

    abstract public function onDeactivation(): void;
}
