<?php

namespace SimpleAnalytics\Settings\Fields;

use SimpleAnalytics\Setting;

class Checkbox extends Field
{
    protected ?string $description = null;
    protected ?string $docs = null;

    public function render(): void
    {
        ?>
        <div class="relative flex gap-x-3">
            <div class="flex h-6 items-center">
                <input
                    id="<?php echo esc_attr($this->getKey()); ?>"
                    name="<?php echo esc_attr($this->getKey()); ?>"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    <?php if (Setting::get($this->getKey())): ?>
                        checked
                    <?php endif; ?>
                >
            </div>
            <div class="text-sm leading-6">
                <label
                    for="<?php echo esc_attr($this->getKey()); ?>"
                    class="font-medium text-gray-900"
                >
                    <?php echo esc_html($this->getLabel()); ?>

                    <?php if ($this->docs): ?>
                        <a href="<?php echo esc_url($this->docs); ?>" target="_blank" class="text-primary">(docs)</a>
                    <?php endif; ?>
                </label>

                <?php if ($this->description): ?>
                    <p class="text-gray-500">
                        <?php echo esc_html($this->description); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php
    }

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
