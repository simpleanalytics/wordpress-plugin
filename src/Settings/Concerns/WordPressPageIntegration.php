<?php

namespace SimpleAnalytics\Settings\Concerns;

use SimpleAnalytics\Settings\Blocks\Fields\Field;
use SimpleAnalytics\Settings\Tab;
use SimpleAnalytics\UI\PageLayoutComponent;

trait WordPressPageIntegration
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'wpAddMenu']);
        add_action('admin_init', [$this, 'wpAddFields']);
    }

    public function getOptionGroup(Tab $tab): string
    {
        return $this->getSlug() . '-' . $tab->getSlug();
    }

    /** @internal */
    public function wpAddMenu(): void
    {
        add_options_page(
            $this->getTitle(),
            $this->getTitle(),
            'manage_options',
            $this->getSlug(),
            (new PageLayoutComponent($this)),
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
            $this->getOptionGroup($tab),
            $field->getKey(),
            [
                'type'              => $field->getValueType(),
                'default'           => $field->getDefaultValue(),
                'sanitize_callback' => $field->getValueSanitizer(),
            ]
        );
    }
}
