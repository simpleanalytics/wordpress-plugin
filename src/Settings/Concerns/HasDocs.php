<?php

namespace SimpleAnalytics\Settings\Concerns;

trait HasDocs
{
    protected ?string $description = null;

    protected ?string $docs = null;

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
