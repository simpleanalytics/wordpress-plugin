<?php

namespace SimpleAnalytics\Settings\Concerns;

use SimpleAnalytics\Settings\Blocks\Fields\Field;
use SimpleAnalytics\Settings\Tab;
use SimpleAnalytics\UI\PageComponent;

trait WordPressPageIntegration
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'wpAddMenu']);
        add_action('admin_init', [$this, 'wpAddFields']);
    }

    /** @internal */
    public function wpAddMenu(): void
    {
        add_options_page(
            $this->getTitle(),
            $this->getTitle(),
            'manage_options',
            $this->getSlug(),
            (new PageComponent($this)),
        );
    }

    /** @internal */
    public function wpAddFields(): void
    {
        foreach ($this->getTabs() as $tab) {
            $this->registerTab($tab);
        }
    }

    protected function registerTab(Tab $tab): void
    {
        $fields = array_filter($tab->getBlocks(), fn($block) => $block instanceof Field);

        foreach ($fields as $field) {
            $this->registerField($field, $tab);
        }
    }

    protected function registerField(Field $field, Tab $tab): void
    {
        register_setting(
            $this->getSlug() . '-' . $tab->getSlug(),
            $field->getKey(),
            [
                'type'              => 'string',
                'default'           => $field->getDefault(),
                'sanitize_callback' => $field->getSanitizer(),
            ]
        );
    }
}
