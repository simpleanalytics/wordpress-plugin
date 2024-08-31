<?php

namespace SimpleAnalytics\Foundation\Settings\Fields;

class Input extends Field
{
    protected string $type = 'text';
    protected bool $autofocus = false;
    protected ?string $placeholder = null;
    protected ?string $description = null;
    protected ?string $docs = null;

    public function render(): void
    {
        ?>
        <label
            for="<?php echo esc_attr($this->getKey()) ?>"
            class="block text-sm font-medium leading-6 text-gray-900"
        >
            <?php echo esc_html($this->getLabel()); ?>
            <?php if ($this->docs): ?>
                <a href="<?php echo esc_url($this->docs); ?>" target="_blank" class="text-primary">(docs)</a>
            <?php endif; ?>
        </label>
        <input
            type="<?php echo esc_attr($this->type); ?>"
            name="<?php echo esc_attr($this->getKey()); ?>"
            id="<?php echo esc_attr($this->getKey()); ?>"
            class="mt-2 block w-full rounded-md border-0 placeholder:text-gray-400 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 py-1.5 focus:ring-primary focus:ring-2 focus:ring-inset sm:max-w-md sm:text-sm sm:leading-6"
            placeholder="<?php echo esc_attr($this->placeholder); ?>"
            <?php if ($this->autofocus): ?>
                autofocus
            <?php endif; ?>
            value="<?php echo esc_attr(get_option($this->getKey())); ?>"
        >
        <?php if ($this->description): ?>
        <p class="mt-2 text-sm text-gray-500">
            <?php echo esc_html($this->description); ?>
        </p>
    <?php endif; ?>
        <?php
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

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function autofocus(): self
    {
        $this->autofocus = true;

        return $this;
    }

    public function docs(string $docs): self
    {
        $this->docs = $docs;

        return $this;
    }
}
