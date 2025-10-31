<?php

namespace Tests\Browser;

use function Tests\{asAdmin, asAuthor, asEditor};

const SA_DEFAULT_SCRIPT_SELECTOR = 'script[src="https://scripts.simpleanalyticscdn.com/latest.js"]';
const SA_INACTIVE_ADMIN_SCRIPT_SELECTOR = 'script[src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"]';
const SA_INACTIVE_ADMIN_COMMENT = '<!-- Simple Analytics: Not logging requests from admins -->';

it('can be activated', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/plugins.php')
        ->screenshot()
        ->assertPresent('tr[data-slug="simpleanalytics"]')
        ->click('#activate-simpleanalytics')
        ->assertPresent('a[href="options-general.php?page=simpleanalytics"]')
        ->assertPresent('#deactivate-simpleanalytics');
});

it('adds a script by default', function () {
    visit('http://localhost:8100')->assertPresent(SA_DEFAULT_SCRIPT_SELECTOR);
});

it('adds inactive script for authenticated users by default', function () {
    asAdmin()
        ->navigate('http://localhost:8100')
        ->dd()
        ->assertPresent('script[url="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"]')
        ->assertSourceHas(SA_INACTIVE_ADMIN_COMMENT);
});

it('adds a script with ignored pages', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
        ->fill('simpleanalytics_ignore_pages', '/vouchers')
        ->click('Save Changes')
        ->assertValue('simpleanalytics_ignore_pages', '/vouchers');

    visit('http://localhost:8100')->assertSourceHas('data-ignore-pages="/vouchers"');
});

it('adds inactive script for selected user roles', function () {
    $admin = asAdmin()->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
        ->check('simpleanalytics_exclude_user_roles-editor')
        ->check('simpleanalytics_exclude_user_roles-author')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_exclude_user_roles-editor')
        ->assertChecked('simpleanalytics_exclude_user_roles-author');

    $admin->navigate('http://localhost:8100')
        ->assertPresent(SA_DEFAULT_SCRIPT_SELECTOR);

    asAuthor()->navigate('http://localhost:8100')
        ->assertPresent(SA_INACTIVE_ADMIN_SCRIPT_SELECTOR)
        ->assertSourceHas(SA_INACTIVE_ADMIN_COMMENT);

    asEditor()->navigate('http://localhost:8100')
        ->assertPresent(SA_INACTIVE_ADMIN_SCRIPT_SELECTOR)
        ->assertSourceHas(SA_INACTIVE_ADMIN_COMMENT);
});

it('adds a script with a custom domain name', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=general')
        ->fill('simpleanalytics_custom_domain', 'mydomain.com')
        ->click('Save Changes')
        ->assertValue('simpleanalytics_custom_domain', 'mydomain.com');

    visit('http://localhost:8100')->assertPresent('script[src="https://mydomain.com/latest.js"]');
});
