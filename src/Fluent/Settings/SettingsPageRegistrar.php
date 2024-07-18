<?php

namespace SimpleAnalytics\Fluent\Settings;

use SimpleAnalytics\Fluent\Settings\Fields\Field;

readonly class SettingsPageRegistrar
{
    protected SettingsPage $page;
    protected SettingsPageRenderer $renderer;

    public function __construct(SettingsPage $settings)
    {
        $this->page = $settings;
        $this->renderer = new SettingsPageRenderer(
            $settings->getTitle(),
            $settings->getSlug(),
            $settings->getTabs(),
        );
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerPage']);
        add_action('admin_init', [$this, 'registerFields']);
    }

    public function registerPage(): void
    {
        add_options_page(
            $this->page->getTitle(),
            $this->page->getTitle(),
            'manage_options',
            $this->page->getSlug(),
            $this->renderer,
        );
    }

    public function registerFields(): void
    {
        foreach ($this->page->getTabs() as $tab) foreach ($tab->getFields() as $field) {
            $this->registerField($field, $tab);
        }
    }

    protected function registerField(Field $field, Tab $tab): void
    {
        register_setting(
            $this->page->getSlug() . '-' . $tab->getSlug(),
            $field->getKey(),
        );
    }
}
