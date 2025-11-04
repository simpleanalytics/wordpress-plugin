<?php

namespace Tests\Browser;

class PluginSettingsTest extends BrowserTestCase
{
    public function test_activation_and_presence_of_default_scripts(): void
    {
        $this->asAdmin()->visit('/wp-admin/plugins.php')
            ->click('#activate-simpleanalytics')
            ->assertSeeElement('#deactivate-simpleanalytics')
            ->visit('/')
            ->assertContains('<!-- Simple Analytics: Not logging requests from admins -->')
            ->assertContains('<script src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"');

        $this->myBrowser()
            ->visit('/')
            ->assertContains('<script src="https://scripts.simpleanalyticscdn.com/latest.js"></script>');
    }
}
