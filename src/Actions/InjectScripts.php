<?php

namespace SimpleAnalytics\Actions;

use SimpleAnalytics\Scripts\Script;

defined('\\ABSPATH') || exit;

class InjectScripts
{
    public function __construct(
        protected array $scripts,
    ) {
    }

    public function __invoke(): void
    {
        foreach ($this->scripts as $script) if ($script instanceof Script) {
            wp_enqueue_script(
                $script->getHandle(),
                $script->getPath(),
                [],
                null,
                true
            );
        }

        add_filter('wp_script_attributes', function ($attributes, $handle) {
            foreach ($this->scripts as $script) if ($script instanceof Script && $script->getHandle() === $handle) {
                return [...$attributes, ...$script->getAttributes()];
            }

            return $attributes;
        }, 10, 2);
    }
}
