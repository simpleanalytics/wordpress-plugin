<?php

namespace SimpleAnalytics\Fields;

defined('\\ABSPATH') || exit;

abstract class AbstractField
{
    protected string $name;
    protected string $label;

    public function __construct(
        string $group,
        string $page,
        string $section
    ) {
        register_setting($group, $this->name, [$this, 'sanitize']);

        add_settings_field(
            $this->name,
            $this->label,
            [$this, 'render'],
            $page,
            $section
        );
    }

    abstract public function sanitize($input);

    abstract public function render();
}
