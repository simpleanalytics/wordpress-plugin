<?php

namespace Tests\Browser;

use function Tests\asAdmin;

const SA_ADMIN_NOTICE = '<!-- Simple Analytics: Not logging requests from admins -->';
const SA_DEFAULT_SCRIPT = 'src="https://scripts.simpleanalyticscdn.com/latest.js"></script>';
const SA_INACTIVE_ADMIN_SCRIPT = 'src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"';

const WP_PLUGIN_ROW_SELECTOR = 'tr[data-slug="simpleanalytics"]';
const WP_ACTIVATE_PLUGIN_SELECTOR = '#activate-simpleanalytics';
const WP_DEACTIVATE_PLUGIN_SELECTOR = '#deactivate-simpleanalytics';
const WP_PLUGIN_MENU_ITEM = 'a[href="options-general.php?page=simpleanalytics"]';

it('can be activated', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/plugins.php')
        ->screenshot()
        ->assertPresent(WP_PLUGIN_ROW_SELECTOR)
        ->click(WP_ACTIVATE_PLUGIN_SELECTOR)
        ->assertPresent(WP_PLUGIN_MENU_ITEM)
        ->assertPresent(WP_DEACTIVATE_PLUGIN_SELECTOR);
});

it('adds a script by default', function () {
    $homePage = visit('http://localhost:8100');
    expect($homePage->content())->dump()->toContain(SA_DEFAULT_SCRIPT);
});

it('adds a comment when an authenticated user visits', function () {
    $homePage = asAdmin()->navigate('http://localhost:8100');

    expect($homePage->content())->dump()
        ->toContain(SA_ADMIN_NOTICE)
        ->toContain(SA_INACTIVE_ADMIN_SCRIPT);
});

it('adds a script with a custom domain name', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=general'¬)
        ->fill('simpleanalytics_custom_domain', 'mydomain.com')
        ->click('Save Changes')
        ->assertValue('simpleanalytics_custom_domain', 'mydomain.com');

    expect(visit('http://localhost:8100')->content())
        ->toContain(str_replace('scripts.simpleanalyticscdn.com', 'mydomain.com', SA_DEFAULT_SCRIPT));
});
