<?php

namespace SimpleAnalytics\Settings\Fields;

use SimpleAnalytics\Setting;
use SimpleAnalytics\Settings\Concerns\ManagesDocs;
use SimpleAnalytics\UI\LabelComponent;

class Input extends Field
{
    use ManagesDocs;

    protected string $type = 'text';

    protected bool $autofocus = false;

    protected ?string $placeholder = null;

    public function getSanitizer(): callable
    {
        return 'sanitize_text_field';
    }

    public function render(): void
    {
        ?>
        <?php (new LabelComponent(value: $this->getLabel(), docs: $this->docs, for: $this->getKey()))() ?>
        <input
            type="<?php echo esc_attr($this->type) ?>"
            name="<?php echo esc_attr($this->getKey()) ?>"
            id="<?php echo esc_attr($this->getKey()) ?>"
            class="mt-2 block w-full rounded-md border-0 placeholder:text-gray-400 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 py-1.5 focus:ring-primary focus:ring-2 focus:ring-inset sm:max-w-md sm:text-sm sm:leading-6"
            placeholder="<?php echo esc_attr($this->placeholder) ?>"
            <?php if ($this->autofocus) echo "autofocus" ?>
            value="<?php echo esc_attr(Setting::get($this->getKey())) ?>"
        >
        <?php if ($this->description): ?>
        <p class="mt-2 text-sm text-gray-500">
            <?php echo esc_html($this->description) ?>
        </p>
        <?php endif;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function autofocus(): self
    {
        $this->autofocus = true;

        return $this;
    }
}
