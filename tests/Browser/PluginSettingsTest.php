<?php

namespace Tests\Browser;

use function Tests\{asAdmin, asAuthor, asEditor};

const SA_DEFAULT_SCRIPT = 'src="https://scripts.simpleanalyticscdn.com/latest.js"></script>';
const SA_INACTIVE_ADMIN_NOTICE = '<!-- Simple Analytics: Not logging requests from admins -->';
const SA_INACTIVE_ADMIN_SCRIPT = 'src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"';

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
    $homePage = visit('http://localhost:8100');
    expect($homePage->content())->toContain(SA_DEFAULT_SCRIPT);
});

it('adds inactive script for authenticated users by default', function () {
    $homePage = asAdmin()->navigate('http://localhost:8100');

    expect($homePage->content())
        ->toContain(SA_INACTIVE_ADMIN_NOTICE)
        ->toContain(SA_INACTIVE_ADMIN_SCRIPT);
});

it('adds a script with a custom domain name', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=general')
        ->fill('simpleanalytics_custom_domain', 'mydomain.com')
        ->click('Save Changes')
        ->assertValue('simpleanalytics_custom_domain', 'mydomain.com');

    $script = str_replace('scripts.simpleanalyticscdn.com', 'mydomain.com', SA_DEFAULT_SCRIPT);

    expect(visit('http://localhost:8100')->content())->toContain($script);
});

it('adds a script with ignored pages', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
        ->fill('simpleanalytics_ignore_pages', '/vouchers')
        ->click('Save Changes')
        ->assertValue('simpleanalytics_ignore_pages', '/vouchers');

    expect(visit('http://localhost:8100')->content())->toContain('data-ignore-pages="/vouchers"');
});

it('adds inactive script for selected user roles', function () {
    $admin = asAdmin();

    $admin->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
        ->check('simpleanalytics_exclude_user_roles-editor')
        ->check('simpleanalytics_exclude_user_roles-author')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_exclude_user_roles-editor')
        ->assertChecked('simpleanalytics_exclude_user_roles-author');

    expect($admin->navigate('http://localhost:8100')->content())
        ->toContain(SA_DEFAULT_SCRIPT);

    expect(asEditor()->navigate('http://localhost:8100')->content())
        ->toContain(SA_INACTIVE_ADMIN_NOTICE)
        ->toContain(SA_INACTIVE_ADMIN_SCRIPT);

    expect(asAuthor()->navigate('http://localhost:8100')->content())
        ->toContain(SA_INACTIVE_ADMIN_NOTICE)
        ->toContain(SA_INACTIVE_ADMIN_SCRIPT);
});
