<?php

namespace SimpleAnalytics\Settings\Blocks\Fields;

use SimpleAnalytics\Settings\Block;

abstract class Field implements Block
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

    abstract public function getValueSanitizer(): callable;

    abstract public function getValueType(): string;

    abstract public function render(): void;

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function getDefaultValue(): mixed
    {
        return $this->default;
    }
}
