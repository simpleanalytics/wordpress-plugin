<?php

namespace SimpleAnalytics\Settings\Concerns;

defined('ABSPATH') || exit;

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
