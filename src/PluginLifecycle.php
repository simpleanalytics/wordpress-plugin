<?php

namespace SimpleAnalytics;

trait PluginLifecycle
{
    public function boot(): void
    {
        $this->onBoot();
        add_action('init', $this->onInit(...));
        register_activation_hook(ENTRYPOINT_FILE, $this->onActivation(...));
        register_deactivation_hook(ENTRYPOINT_FILE, $this->onUninstall(...));
    }

    /** @internal */
    abstract protected function onBoot(): void;

    /** @internal */
    abstract public function onInit(): void;

    /** @internal */
    abstract public function onActivation(): void;

    /** @internal */
    abstract public function onUninstall(): void;
}
