<?php

namespace SimpleAnalytics;

defined('ABSPATH') || exit;

class ExcludedIpAddressesField extends AbstractField
{
    protected string $name = 'simpleanalytics_exclude_ip_addresses';
    protected string $label = 'Exclude IP addresses from being tracked';

    public function sanitize($input): string
    {
        // WIP
        return sanitize_textarea_field($input);
    }

    public function render()
    {
        ?>
        <textarea
            id="exclude_ip_addresses"
            name="<?php echo esc_attr($this->name) ?>"
            rows="5"
            cols="50"
            placeholder="127.0.0.1
192.168.0.1"
        ><?php echo esc_textarea(get_option($this->name, '')); ?></textarea>
        <!-- Add current ip address to the textarea -->
        <button
            type="button"
            id="add_current_ip"
            class="button"
            onclick="document.getElementById('exclude_ip_addresses').value += '<?php echo $_SERVER['REMOTE_ADDR']; ?>\n';"
        >
            Add current IP address
        </button>
        <?php
    }
}
