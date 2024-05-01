<?php

namespace SimpleAnalytics\Admin;

use SimpleAnalytics\Admin\Fields\Field;

abstract class Form
{
    public function register(): void
    {
        add_action('admin_init', [$this, 'addFields']);
    }

    public function addFields(): void
    {
        foreach ($this->fields() as $field) {
            //
        }
    }

    /** @return Field[] */
    abstract protected function fields(): array;
}
