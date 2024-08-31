<?php

namespace SimpleAnalytics\Settings;

use SimpleAnalytics\Settings\Concerns\ManagesFields;
use SimpleAnalytics\Settings\Fields\Field;
use SimpleAnalytics\Support\SvgIcon;

class Tab
{
    use ManagesFields;

    protected readonly string $title;

    protected readonly string $slug;

    protected ?SvgIcon $icon = null;

    /** @var Field[] */
    protected array $fields = [];

    public function __construct(string $title, string $slug)
    {
        $this->title = $title;
        $this->slug = $slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function icon(SvgIcon $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?SvgIcon
    {
        return $this->icon;
    }

    protected function addField(Field $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
