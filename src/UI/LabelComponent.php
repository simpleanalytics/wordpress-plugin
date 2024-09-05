<?php

namespace SimpleAnalytics\UI;

readonly class LabelComponent
{
    public function __construct(
        private string  $value,
        private string  $for,
        private ?string $docs,
    ) {
    }

    public function __invoke(): void
    {
        ?>
        <label
            for="<?php echo esc_attr($this->for) ?>"
            class="block text-sm font-medium leading-6 text-gray-900"
        >
            <?php echo esc_html($this->value) ?>
            <?php if ($this->docs): ?>
                <a href="<?php echo esc_url($this->docs) ?>" target="_blank" class="text-primary">(docs)</a>
            <?php endif ?>
        </label>
        <?php
    }
}
