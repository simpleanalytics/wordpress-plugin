<?php

namespace SimpleAnalytics\Settings\Concerns;

trait HasPlaceholder
{
    /**
     * @var string|null
     */
    protected $placeholder;

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }
}
