<?php

namespace SimpleAnalytics\Admin\Fields;

abstract class Field
{
    public static function make(...$args): self
    {
        return new static(...$args);
    }

    public function __construct(
        protected string $name,
        protected string $label,
        protected string $group,
        protected string $page,
        protected string $section
    ) {
    }

    abstract function render($value);
}
