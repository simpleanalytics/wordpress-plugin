<?php

namespace SimpleAnalytics\Fluent\Scripts;

/**
 * Register scripts with WordPress.
 */
class ScriptCollection
{
    public function __construct(
        /** @var Script[] */
        private $scripts = []
    ) {
    }

    public function add(Script $script): void
    {
        $this->scripts[] = $script;
    }

    /**
     * Register the scripts with WordPress.
     */
    public function register(): void
    {
        $this->enqueueScripts();
        $this->applyAttributes();
    }

    protected function enqueueScripts(): void
    {
        foreach ($this->scripts as $script) {
            wp_enqueue_script(
                $script->handle(),
                $script->path(),
                [],
                null,
                true
            );
        }
    }

    /**
     * As WordPress does not provide a way of directly assigning attributes to scripts, we need to use a filter.
     * @see https://developer.wordpress.org/reference/hooks/wp_script_attributes
     */
    protected function applyAttributes(): void
    {
        add_filter('wp_script_attributes', $this->attributesFilter(...), 10, 2);
    }

    /**
     * Filter implementation that adds attributes to scripts.
     */
    protected function attributesFilter($attributes)
    {
        foreach ($this->scripts as $script) {
            if (
                $script instanceof HasAttributes &&
                $script->handle() === $script['id']
            ) {
                return [...$attributes, ...$script->attributes()];
            }
        }

        return $attributes;
    }
}
