<?php

namespace SimpleAnalytics\Fluent\Settings\Fields;

abstract class Field
{
    protected string $key;

    protected string $label;

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    abstract public function render(): void;
}
