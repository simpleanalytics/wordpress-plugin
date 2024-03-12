<?php

namespace SimpleAnalytics;

defined('ABSPATH') || exit;

class CustomDomainField extends AbstractField
{
    protected string $name = 'simpleanalytics_custom_domain';
    protected string $label = 'Custom domain';

    public function sanitize($input)
    {
        if (empty($input)) {
            return false;
        }

        return sanitize_text_field($input);
    }

    public function render(): void
    {
        ?>
        <input
            type="text"
            id="custom_domain"
            name="<?php echo esc_attr($this->name) ?>"
            value="<?php echo esc_attr(get_option($this->name)) ?>"
        />
        <p class="description">
            <?php esc_html_e('E.g. api.example.com. Leave empty to use the default domain (most users).', 'simple-analytics'); ?>
            <a
                href="https://docs.simpleanalytics.com/bypass-ad-blockers"
                target="_blank"
            >
                <?php esc_html_e('Learn more.', 'simple-analytics'); ?>
            </a>
        </p>
        <?php
    }
}
