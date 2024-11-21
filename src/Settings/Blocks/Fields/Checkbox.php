<?php

namespace SimpleAnalytics\Settings\Blocks\Fields;

use SimpleAnalytics\Setting;
use SimpleAnalytics\Settings\Concerns\HasDocs;
use SimpleAnalytics\UI\LabelComponent;

class Checkbox extends Field
{
    use HasDocs;

    protected bool $default = false;

    public function getValueSanitizer(): callable
    {
        return 'absint';
    }

    public function getValueType(): string
    {
        return 'integer';
    }

    public function default(bool $value): self
    {
        $this->default = $value;

        return $this;
    }

    public function render(): void
    {
        ?>
        <div class="relative flex gap-x-3">
            <div class="flex h-6 items-center">
                <input
                    id="<?php 
        echo esc_attr($this->getKey());
        ?>"
                    name="<?php 
        echo esc_attr($this->getKey());
        ?>"
                    type="checkbox"
                    value="1"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    <?php 
        if (Setting::boolean($this->getKey(), $this->default) === true) echo 'checked';
        ?>
                >
            </div>
            <div class="text-sm leading-6">
                <?php 
        (new LabelComponent($this->getLabel(), $this->docs, $this->getKey()))();
        ?>

                <?php 
        if ($this->description): ?>
                    <p class="text-gray-500">
                        <?php echo esc_html($this->description); ?>
                    </p>
                <?php endif;
        ?>
            </div>
        </div>
        <?php 
    }
}
