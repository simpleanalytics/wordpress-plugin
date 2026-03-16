<?php

namespace SimpleAnalytics;

class WordPressSettings
{
    /** @return mixed */
    public function get(string $key, $default = null)
    {
        $value = get_option($key);

        if (empty($value)) {
            return $default;
        }

        return $value;
    }

    public function register(string $key, $value = null, bool $autoload = true): void
    {
        // Serialise booleans for WordPress terms...
        if (is_bool($value)) $value = $value ? '1' : '0';

        add_option($key, $value, null, $autoload);
    }

    public function update(string $key, $value, ?bool $autoload = null)
    {
        update_option($key, $value, $autoload);
    }

    public function delete(string $key): void
    {
        delete_option($key);
    }

    public function boolean(string $key, ?bool $default = null): ?bool
    {
        $value = get_option($key, null);

        if ($value === null || $value === '') {
            return $default;
        }

        return (bool)$value;
    }

    public function array(string $key): array
    {
        $value = $this->get($key, []);

        return is_array($value) ? $value : [$value];
    }
}
