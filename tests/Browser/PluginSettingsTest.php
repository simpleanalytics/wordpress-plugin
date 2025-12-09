<?php

namespace Tests\Browser;

class PluginSettingsTest extends BrowserTestCase
{
    public function test_activation_and_presence_of_default_scripts(): void
    {
        $browser = $this->asAdmin();
        $this->activatePluginIfNeeded($browser);

        $browser->visit('/wp-admin/plugins.php')
            ->assertSeeElement('#deactivate-simpleanalytics')
            ->visit('/')
            ->assertContains('<!-- Simple Analytics: Not logging requests from admins -->')
            ->assertContains('<script src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"');

        $this->myBrowser()
            ->visit('/')
            ->assertContains('<script src="https://scripts.simpleanalyticscdn.com/latest.js"></script>');
    }

    public function test_adds_script_with_ignored_pages(): void
    {
        $browser = $this->asAdmin();
        $this->activatePluginIfNeeded($browser);

        $browser->visit('/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
            ->fillField('simpleanalytics_ignore_pages', '/vouchers')
            ->click('Save Changes')
            ->visit('/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
            ->assertFieldEquals('simpleanalytics_ignore_pages', '/vouchers');

        $this->myBrowser()
            ->visit('/')
            ->assertContains('data-ignore-pages="/vouchers"');
    }
    //it('adds inactive script for selected user roles', function () {
    //    $admin = asAdmin()->navigate('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
    //        ->check('simpleanalytics_exclude_user_roles-editor')
    //        ->check('simpleanalytics_exclude_user_roles-author')
    //        ->click('Save Changes')
    //        ->assertChecked('simpleanalytics_exclude_user_roles-editor')
    //        ->assertChecked('simpleanalytics_exclude_user_roles-author');
    //
    //    $admin->navigate('http://localhost:8100')
    //        ->assertPresent(DEFAULT_SCRIPT_SELECTOR);
    //
    //    asAuthor()->navigate('http://localhost:8100')
    //        ->assertPresent(INACTIVE_ADMIN_SCRIPT_SELECTOR)
    //        ->assertSourceHas(INACTIVE_ADMIN_COMMENT);
    //
    //    asEditor()->navigate('http://localhost:8100')
    //        ->assertPresent(INACTIVE_ADMIN_SCRIPT_SELECTOR)
    //        ->assertSourceHas(INACTIVE_ADMIN_COMMENT);
    //});

//    public function
}
