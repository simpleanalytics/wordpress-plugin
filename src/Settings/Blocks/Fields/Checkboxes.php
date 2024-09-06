<?php

namespace SimpleAnalytics\Settings\Blocks\Fields;

use Closure;
use SimpleAnalytics\Setting;
use SimpleAnalytics\Settings\Concerns\HasDocs;
use SimpleAnalytics\UI\LabelComponent;

class Checkboxes extends Field
{
    use HasDocs;

    /** @var array<mixed, string> */
    protected array $options;

    public function options(array|Closure $options): self
    {
        $this->options = $options instanceof Closure ? $options() : $options;

        return $this;
    }

    #[\Override]
    public function getSanitizer(): callable
    {
        return function ($value) {
            if (! is_array($value)) $value = [$value];

            return array_filter($value, fn($item) => array_key_exists($item, $this->options));
        };
    }

    #[\Override]
    public function render(): void
    {
        $currentValue = Setting::array($this->getKey());
        ?>
        <fieldset>
            <?php (new LabelComponent(value: $this->getLabel(), docs: $this->docs, as: 'legend'))() ?>
            <div class="mt-2 space-y-2">
                <?php foreach ($this->options as $value => $label): ?>
                    <div class="relative flex items-start">
                        <div class="flex h-6 items-center">
                            <input
                                id="<?php echo esc_attr($this->getKey() . '-' . $value); ?>"
                                name="<?php echo esc_attr($this->getKey()); ?>[]"
                                type="checkbox"
                                value="<?php echo esc_attr($value); ?>"
                                <?php checked(in_array($value, $currentValue)); ?>
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            >
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label
                                for="<?php echo esc_attr($this->getKey() . '-' . $value); ?>"
                                class="font-medium text-gray-900"
                            >
                                <?php echo esc_html($label); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($this->description): ?>
                <p class="mt-2 text-gray-500">
                    <?php echo esc_html($this->description) ?>
                </p>
            <?php endif ?>
        </fieldset>
        <?php
    }
}
