<?php

namespace SimpleAnalytics\Settings\Blocks\Fields;

use SimpleAnalytics\Settings\Block;

abstract class Field implements Block
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $label;

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
}
