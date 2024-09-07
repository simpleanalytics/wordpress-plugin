<?php

namespace SimpleAnalytics\Support;

class SvgIcon implements \Stringable
{
    protected array $attributes = [];

    public function __construct(protected string $icon)
    {
    }

    public function class(string $class): self
    {
        $this->attributes['class'] = $class;

        return $this;
    }

    public function __toString(): string
    {
        return str_replace('<svg', '<svg ' . $this->attributesToHtml(), $this->icon);
    }

    protected function attributesToHtml(): string
    {
        $attributes = '';

        foreach ($this->attributes as $key => $value) {
            $attributes .= $key . '="' . $value . '" ';
        }

        return trim($attributes);
    }
}
