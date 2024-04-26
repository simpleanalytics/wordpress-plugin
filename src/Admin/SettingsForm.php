<?php

namespace SimpleAnalytics\Admin;

defined('\\ABSPATH') || exit;

class SettingsForm
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerFields']);
    }

    public function registerFields()
    {
    }
}
