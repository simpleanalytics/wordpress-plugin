<?php

namespace SimpleAnalytics\Settings\Blocks\Fields;

use SimpleAnalytics\Setting;
use SimpleAnalytics\Settings\Concerns\HasDocs;
use SimpleAnalytics\Settings\Concerns\HasPlaceholder;
use SimpleAnalytics\UI\LabelComponent;

class IpList extends Field
{
    use HasDocs;
    use HasPlaceholder;

    #[\Override]
    public function getValueSanitizer(): callable
    {
        return function ($value) {
            $ips = [];

            if (! is_array($value)) {
                $ips = explode("\n", $value);
            }

            $ips = array_map('trim', $ips);
            $ips = array_filter($ips, function ($ip) {
                return filter_var($ip, FILTER_VALIDATE_IP) !== false;
            });
            $ips = array_unique($ips);
            return array_values($ips);
        };
    }

    #[\Override]
    public function getValueType(): string
    {
        return 'array';
    }

    #[\Override]
    public function render(): void
    {
        $value = implode("\n", Setting::array($this->getKey()));
        $currentIp = $_SERVER['REMOTE_ADDR'];
        ?>
        <?php (new LabelComponent(
            value: $this->getLabel(),
            docs: $this->docs,
            for: esc_attr($this->getKey()))
        )(); ?>
        <div class="mt-2">
            <textarea
                name="<?php echo esc_attr($this->getKey()); ?>"
                id="<?php echo esc_attr($this->getKey()); ?>"
                rows="5"
                class="block w-full rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:py-1.5 sm:text-sm sm:leading-6"
                <?php if ($this->placeholder): ?>
                    placeholder="<?php echo esc_attr($this->placeholder); ?>"
                <?php endif ?>
            ><?php echo esc_textarea($value); ?></textarea>
        </div>
        <div class="mt-2">
            <button
                type="button"
                onclick="sa_textarea_add_value(this.form.elements['<?php echo esc_js($this->getKey()); ?>'], '<?php echo esc_js($currentIp); ?>')"
                class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
                Add Current IP (<?php echo esc_html($currentIp); ?>)
            </button>
        </div>
        <p class="mt-2 text-sm text-gray-500">
            Enter IP addresses to exclude from tracking, one per line.
        </p>
        <?php
    }
}
