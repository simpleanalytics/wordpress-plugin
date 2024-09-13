<?php

namespace SimpleAnalytics\Settings;

use SimpleAnalytics\Settings\Concerns\WordPressPageIntegration;
use SimpleAnalytics\Support\Str;

class Page
{
    use WordPressPageIntegration;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $slug;

    /** @var Tab[] */
    protected $tabs = [];

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public static function title(string $title): self
    {
        return new self($title);
    }

    public function slug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function tab(string $title, callable $callback): self
    {
        $tab = new Tab($title, Str::slug($title));
        $callback($tab);

        $this->tabs[] = $tab;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTabs(): array
    {
        return $this->tabs;
    }
}
