<?php

namespace SimpleAnalytics\Settings;

use SimpleAnalytics\Settings\Fields\Field;
use SimpleAnalytics\UI\PageComponent;

readonly class PageRegistrar
{
    protected Page $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
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
            (new PageComponent($this->page)),
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
            [
                'type'              => 'string',
                'default'           => $field->getDefault(),
                'sanitize_callback' => $field->getSanitizer(),
            ]
        );
    }
}
