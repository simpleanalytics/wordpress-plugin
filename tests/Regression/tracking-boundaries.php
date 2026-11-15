<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $rules = new SimpleAnalytics\TrackingRules(new SimpleAnalytics\WordPressSettings());
    update_option(SimpleAnalytics\SettingName::EXCLUDED_IP_ADDRESSES, []);
    update_option(SimpleAnalytics\SettingName::EXCLUDED_ROLES, ['editor']);
    update_option(SimpleAnalytics\SettingName::AUTOMATED_EVENTS, true);
    update_option(SimpleAnalytics\SettingName::NOSCRIPT, true);

    wp_set_current_user(0);
    sa_assert(! $rules->hasExcludedUserRole(), 'Anonymous front-end visits must remain eligible for tracking.');
    wp_set_current_user(get_user_by('login', 'admin')->ID);
    if (! is_multisite()) sa_assert(! $rules->hasExcludedUserRole(), 'An unselected ordinary role must remain eligible.');
    wp_set_current_user(0);

    sa_assert(! wp_script_is('simpleanalytics', 'enqueued'), 'The init hook must not enqueue analytics.');
    foreach (['login_enqueue_scripts', 'admin_enqueue_scripts'] as $hook) {
        // Invoke only this plugin's callbacks; unrelated admin hooks require a full admin request.
        foreach (($GLOBALS['wp_filter'][$hook]->callbacks ?? []) as $callbacks) {
            foreach ($callbacks as $callback) {
                $function = $callback['function'];
                if ($function instanceof Closure && (new ReflectionFunction($function))->getClosureThis() instanceof SimpleAnalytics\Plugin) {
                    $function();
                }
            }
        }
        sa_assert(! wp_script_is('simpleanalytics', 'enqueued'), 'Analytics must not be enqueued on ' . $hook);
        sa_assert(! wp_script_is('simpleanalytics_auto_events', 'enqueued'), 'Events must not be enqueued on ' . $hook);
    }

    do_action('wp_enqueue_scripts');
    sa_assert(wp_script_is('simpleanalytics', 'enqueued'), 'Eligible front-end visits must enqueue analytics.');
    sa_assert(wp_script_is('simpleanalytics_auto_events', 'enqueued'), 'Eligible front-end visits must enqueue events.');
    wp_dequeue_script('simpleanalytics');
    wp_dequeue_script('simpleanalytics_auto_events');

    wp_set_current_user(get_user_by('login', 'admin')->ID);
    update_option(SimpleAnalytics\SettingName::EXCLUDED_ROLES, ['administrator']);
    $plugin = new SimpleAnalytics\Plugin(new SimpleAnalytics\WordPressHooks(), new SimpleAnalytics\WordPressSettings(), $rules, new SimpleAnalytics\ScriptRegistry(), new SimpleAnalytics\Settings\AdminPage('test'));
    $plugin->enqueueScripts();
    sa_assert(! wp_script_is('simpleanalytics', 'enqueued'), 'Excluded visitors must not enqueue analytics.');
    sa_assert(! wp_script_is('simpleanalytics_auto_events', 'enqueued'), 'Excluded visitors must not enqueue automated events.');

    // On a multisite fixture, model a network administrator with no site role.
    if (is_multisite()) {
        sa_assert(is_super_admin(), 'The test admin must be a network administrator.');
        wp_get_current_user()->roles = [];
        update_option(SimpleAnalytics\SettingName::EXCLUDED_ROLES, ['editor']);
        sa_assert($rules->hasExcludedUserRole(), 'A role-less network administrator must still be excluded.');
    }
});
