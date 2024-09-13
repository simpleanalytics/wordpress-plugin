<?php

namespace SimpleAnalytics\Support;

class SvgIcon
{
    /**
     * @var string
     */
    protected $icon;
    /**
     * @var mixed[]
     */
    protected $attributes = [];
    public function __construct(string $icon)
    {
        $this->icon = $icon;
    }
    public function class(string $class): self
    {
        $this->attributes['class'] = $class;

        return $this;
    }
    public function __toString(): string
    {
        return str_replace('<svg', '<svg ' . Str::htmlAttributes($this->attributes), $this->icon);
    }
}
