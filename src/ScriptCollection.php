<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Scripts\{HasAttributes, HiddenScriptId, Script};

/**
 * Register scripts with WordPress.
 */
final class ScriptCollection
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
        $this->addAttributes();
        $this->removeIds();
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
    protected function addAttributes(): void
    {
        add_filter('wp_script_attributes', $this->addAttributesFilter(...), 10, 2);
    }

    protected function addAttributesFilter($attributes)
    {
        foreach ($this->scripts as $script) {
            if (
                $script instanceof HasAttributes &&
                $script->handle() . '-js' === $attributes['id']
            ) {
                return [...$attributes, ...$script->attributes()];
            }
        }

        return $attributes;
    }

    protected function removeIds(): void
    {
        add_filter('script_loader_tag', $this->removeIdsFilter(...), 10, 2);
    }

    protected function removeIdsFilter($tag, $handle): string
    {
        foreach ($this->scripts as $script) {
            if ($script instanceof HiddenScriptId && $script->handle() === $handle) {
                // Remove the id attribute from the script tag
                return preg_replace('/ id=([\'"])[^\'"]*\\1/', '', $tag);
            }
        }

        return $tag;
    }
}
