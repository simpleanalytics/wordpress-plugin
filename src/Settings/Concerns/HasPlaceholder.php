<?php

namespace SimpleAnalytics\Settings\Concerns;

trait HasPlaceholder
{
    protected ?string $placeholder = null;

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }
}
