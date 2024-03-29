<?php

namespace SimpleAnalytics\Fields;

defined('\\ABSPATH') || exit;

class ExcludedRolesField extends AbstractField
{
    protected string $name = 'simpleanalytics_exclude_user_roles';
    protected string $label = 'Exclude user roles from being tracked';

    public function sanitize($input): array
    {
        if (! is_array($input)) {
            return [];
        }

        $acceptable_roles = array_keys($this->getAllRoles());
        $input = array_intersect($acceptable_roles, $input);

        return array_map('sanitize_text_field', $input);
    }

    public function render(): void
    {
        $excluded_roles = get_option($this->name, []);

        foreach ($this->getAllRoles() as $role_value => $role_name) {
            ?>
            <label>
                <input
                    type="checkbox"
                    name="<?php echo esc_attr($this->name) ?>[]"
                    value="<?php echo esc_attr($role_value) ?>"
                    <?php echo in_array($role_value, $excluded_roles) ? 'checked="checked"' : '' ?>
                />
                <?php echo esc_html($role_name) ?>
            </label>
            <br>
            <?php
        }
    }

    protected function getAllRoles(): array
    {
        global $wp_roles;
        return $wp_roles->get_names();
    }
}
