<?php

namespace SimpleAnalytics\Fluent;

class Stringable
{
    public function __construct(
        protected string $value
    ) {
    }

    public function prefix(string $prefix): self
    {
        $this->value = $prefix . $this->value;

        return $this;
    }

    public function suffix(string $suffix): self
    {
        $this->value = $this->value . $suffix;

        return $this;
    }

    public function slug(): self
    {
        $this->value = strtolower(str_replace(' ', '-', $this->value));

        return $this;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
