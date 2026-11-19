<?php

namespace SimpleAnalytics\Settings\Blocks\Fields;

defined('ABSPATH') || exit;

use SimpleAnalytics\Setting;
use SimpleAnalytics\SettingName;
use SimpleAnalytics\Support\OnloadCallback;
use SimpleAnalytics\Support\CustomDomain;
use SimpleAnalytics\Settings\Concerns\HasDocs;
use SimpleAnalytics\Settings\Concerns\HasPlaceholder;
use SimpleAnalytics\UI\LabelComponent;

class Input extends Field
{
    use HasDocs;
    use HasPlaceholder;

    /**
     * @var string
     */
    protected $type = 'text';

    /**
     * @var bool
     */
    protected $autofocus = false;

    /**
     * @var string|null
     */
    protected $placeholder;

    public function getValueSanitizer(): callable
    {
        if ($this->getKey() === SettingName::ONLOAD_CALLBACK) {
            return [OnloadCallback::class, 'sanitize'];
        }
        if ($this->getKey() === SettingName::CUSTOM_DOMAIN) {
            return [CustomDomain::class, 'sanitize'];
        }
        return 'sanitize_text_field';
    }

    public function getValueType(): string
    {
        return 'string';
    }

    public function render(): void
    {
        ?>
        <?php 
        (new LabelComponent($this->getLabel(), $this->docs, $this->getKey()))();
        ?>
        <input
            type="<?php 
        echo esc_attr($this->type);
        ?>"
            name="<?php 
        echo esc_attr($this->getKey());
        ?>"
            id="<?php 
        echo esc_attr($this->getKey());
        ?>"
            class="mt-2 block w-full rounded-md border-0 placeholder:text-gray-400 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 py-1.5 focus:ring-primary focus:ring-2 focus:ring-inset sm:max-w-md sm:text-sm sm:leading-6"
            placeholder="<?php 
        echo esc_attr($this->placeholder);
        ?>"
            <?php 
        if ($this->autofocus) echo "autofocus";
        ?>
            value="<?php 
        echo esc_attr(Setting::get($this->getKey()));
        ?>"
        >
        <?php 
        if ($this->description): ?>
        <p class="mt-2 text-sm text-gray-500">
            <?php echo esc_html($this->description); ?>
        </p>
    <?php endif;
        foreach ($this->messages() as $message): ?>
        <p role="alert" class="mt-2 text-sm font-medium text-red-700">
            <?php echo esc_html($message); ?>
        </p>
    <?php endforeach;
    }

    /**
     * @return string[]
     */
    protected function messages(): array
    {
        // WordPress prints settings errors outside the settings page's shadow
        // root, where nobody sees them, so show them beside the field.
        $messages = array_column(get_settings_errors($this->getKey()), 'message');

        if ($this->getKey() === SettingName::CUSTOM_DOMAIN && $messages === []) {
            $messages[] = CustomDomain::problem();
        }

        return array_values(array_unique(array_filter($messages)));
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function autofocus(): self
    {
        $this->autofocus = true;

        return $this;
    }
}
