<?php

namespace SimpleAnalytics\Settings\Concerns;

trait HasDocs
{
    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $docs;

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function docs(string $docs): self
    {
        $this->docs = $docs;

        return $this;
    }
}
