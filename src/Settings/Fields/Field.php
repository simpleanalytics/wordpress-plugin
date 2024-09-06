<?php

namespace SimpleAnalytics\Settings\Fields;

abstract class Field
{
    protected string $key;

    protected string $label;

    protected mixed $default = null;

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

    abstract public function getSanitizer(): callable;

    abstract public function render(): void;

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }
}
