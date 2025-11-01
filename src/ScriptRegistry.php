<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Scripts\Contracts\HasAttributes;
use SimpleAnalytics\Scripts\Contracts\HideScriptId;
use SimpleAnalytics\Scripts\Contracts\Script;

/**
 * Register scripts with WordPress.
 */
final class ScriptRegistry
{
    /** @var Script[] */
    private $scripts = [];

    public function __construct()
    {
    }

    public function push(Script $script): void
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
            wp_enqueue_script($script->handle(), $script->path(), [], null, true);
        }
    }

    /**
     * As WordPress does not provide a way of directly assigning attributes to scripts, we need to use a filter.
     * @see https://developer.wordpress.org/reference/hooks/wp_script_attributes
     */
    protected function addAttributes(): void
    {
        add_filter('wp_script_attributes', \Closure::fromCallable([$this, 'addAttributesFilter']), 10, 2);
    }

    protected function addAttributesFilter($attributes)
    {
        foreach ($this->scripts as $script) {
            if (
                $script instanceof HasAttributes &&
                $script->handle() . '-js' === $attributes['id']
            ) {
                return array_merge(is_array($attributes) ? $attributes : iterator_to_array($attributes), $script->attributes());
            }
        }

        return $attributes;
    }

    protected function removeIds(): void
    {
        add_filter('script_loader_tag', \Closure::fromCallable([$this, 'removeIdsFilter']), 10, 2);
    }

    protected function removeIdsFilter($tag, $handle): string
    {
        foreach ($this->scripts as $script) {
            if ($script instanceof HideScriptId && $script->handle() === $handle) {
                // Remove the id attribute from the script tag
                return preg_replace('/ id=([\'"])[^\'"]*\\1/', '', $tag);
            }
        }

        return $tag;
    }
}
