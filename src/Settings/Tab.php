<?php

namespace SimpleAnalytics\Settings;

use SimpleAnalytics\Settings\Fields\Field;
use SimpleAnalytics\Support\SvgIcon;

class Tab
{
    use Concerns\ManagesFields;
    use Concerns\HasDocs;

    protected readonly string $name;

    protected readonly string $slug;

    protected ?SvgIcon $icon = null;

    protected ?string $title;

    /** @var Field[] */
    protected array $fields = [];

    public function __construct(string $name, string $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    #[\Override]
    protected function addField(Field $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function render(): void
    {
        ?>
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <?php if (isset($this->title) || isset($this->description)): ?>
                <div class="sm:col-span-4">
                    <?php if (isset($this->title)): ?>
                        <h1 class="text-sm font-medium leading-6 text-gray-900">
                            <?php echo esc_html($this->title) ?>
                            <?php if (isset($this->docs)): ?>
                                <a href="<?php echo esc_url($this->docs) ?>" target="_blank"
                                   class="text-primary">(docs)</a>
                            <?php endif ?>
                        </h1>
                    <?php endif ?>
                    <?php if (isset($this->description)): ?>
                        <p class="mt-2 text-sm text-gray-500">
                            <?php echo esc_html($this->description) ?>
                        </p>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <?php foreach ($this->getFields() as $field): ?>
                <div class="sm:col-span-4">
                    <?php $field->render() ?>
                </div>
            <?php endforeach ?>
        </div>
        <?php
    }
}
