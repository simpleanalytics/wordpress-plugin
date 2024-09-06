<?php

namespace SimpleAnalytics\Settings\Blocks\Fields\Concerns;

trait HasPlaceholder
{
    protected ?string $placeholder = null;

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }
}
