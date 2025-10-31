<?php

namespace Tests\Browser;

use function Tests\{asAdmin, asAuthor, asEditor};

const SA_DEFAULT_SCRIPT_SELECTOR = 'script[src="https://scripts.simpleanalyticscdn.com/latest.js"]';
const SA_INACTIVE_ADMIN_SCRIPT_SELECTOR = 'script[src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"]';
const SA_INACTIVE_ADMIN_COMMENT = '<!-- Simple Analytics: Not logging requests from admins -->';
const SA_NOSCRIPT_SELECTOR = 'noscript img[src="https://queue.simpleanalyticscdn.com/noscript.gif"][alt=""][referrerpolicy="no-referrer-when-downgrade"]';

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
        ->assertPresent('script[src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"]')
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

it('adds a script with collect do not track enabled', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
        ->check('simpleanalytics_collect_dnt')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_collect_dnt');

    visit('http://localhost:8100')->assertSourceHas('data-collect-dnt="true"');
});

it('adds a script with hash mode enabled', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
        ->check('simpleanalytics_hash_mode')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_hash_mode');

    visit('http://localhost:8100')->assertSourceHas('data-mode="hash"');
});

it('adds a script with manually collect page views enabled', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
        ->check('simpleanalytics_manual_collect')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_manual_collect');

    visit('http://localhost:8100')->assertSourceHas('data-auto-collect="true"');
});

it('adds noscript tag when support no javascript mode is enabled', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
        ->check('simpleanalytics_noscript')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_noscript');

    visit('http://localhost:8100')->assertPresent(SA_NOSCRIPT_SELECTOR);
});

it('adds a script with onload callback', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
        ->fill('simpleanalytics_onload_callback', 'sa_event("My event")')
        ->click('Save Changes')
        ->assertValue('simpleanalytics_onload_callback', 'sa_event("My event")');

    visit('http://localhost:8100')->assertSourceHas('data-onload="sa_event(\"My event\")"');
});

it('adds a script with global variable name', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
        ->fill('simpleanalytics_sa_global', 'ba_event')
        ->click('Save Changes')
        ->assertValue('simpleanalytics_sa_global', 'ba_event');

    visit('http://localhost:8100')->assertSourceHas('data-sa-global="ba_event"');
});

it('adds automated events script when collect automated events is enabled', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
        ->check('simpleanalytics_automated_events')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_automated_events');

    visit('http://localhost:8100')->assertPresent('script[src="https://scripts.simpleanalyticscdn.com/auto-events.js"]');
});

it('adds automated events script with auto collect downloads', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
        ->check('simpleanalytics_automated_events')
        ->fill('simpleanalytics_event_collect_downloads', 'outbound,emails,downloads')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_automated_events')
        ->assertValue('simpleanalytics_event_collect_downloads', 'outbound,emails,downloads');

    visit('http://localhost:8100')->assertSourceHas('data-collect="outbound,emails,downloads"');
});

it('adds automated events script with download file extensions', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
        ->check('simpleanalytics_automated_events')
        ->fill('simpleanalytics_event_extensions', 'pdf,zip')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_automated_events')
        ->assertValue('simpleanalytics_event_extensions', 'pdf,zip');

    visit('http://localhost:8100')->assertSourceHas('data-extensions="pdf,zip"');
});

it('adds automated events script with use titles of page enabled', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
        ->check('simpleanalytics_automated_events')
        ->check('simpleanalytics_event_use_title')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_automated_events')
        ->assertChecked('simpleanalytics_event_use_title');

    visit('http://localhost:8100')->assertSourceHas('data-use-title');
});

it('adds automated events script with use full urls enabled', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
        ->check('simpleanalytics_automated_events')
        ->check('simpleanalytics_event_full_urls')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_automated_events')
        ->assertChecked('simpleanalytics_event_full_urls');

    visit('http://localhost:8100')->assertSourceHas('data-full-urls');
});

it('adds automated events script with override global', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
        ->check('simpleanalytics_automated_events')
        ->fill('simpleanalytics_event_sa_global', 'ba_event')
        ->click('Save Changes')
        ->assertChecked('simpleanalytics_automated_events')
        ->assertValue('simpleanalytics_event_sa_global', 'ba_event');

    visit('http://localhost:8100')->assertSourceHas('data-sa-global="ba_event"');
});

it('adds a script with overwrite domain name', function () {
    asAdmin()
        ->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
        ->fill('simpleanalytics_hostname', 'example.com')
        ->click('Save Changes')
        ->assertValue('simpleanalytics_hostname', 'example.com');

    visit('http://localhost:8100')->assertSourceHas('data-hostname="example.com"');
});
