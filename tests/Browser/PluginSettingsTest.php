<?php

namespace Tests\Browser;

class PluginSettingsTest extends BrowserTestCase
{
    public function test_can_be_activated(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/plugins.php')
            ->assertSeeElement('tr[data-slug="simpleanalytics"]')
            ->click('#activate-simpleanalytics')
            ->assertSeeElement('a[href="options-general.php?page=simpleanalytics"]')
            ->assertSeeElement('#deactivate-simpleanalytics');
    }

    public function test_adds_a_script_by_default(): void
    {
        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertSeeElement('script[src="https://scripts.simpleanalyticscdn.com/latest.js"]');
    }

    public function test_adds_inactive_script_for_authenticated_users_by_default(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100')
            ->assertSeeElement('script[src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"]')
            ->assertContains('<!-- Simple Analytics: Not logging requests from admins -->');
    }

    public function test_adds_a_script_with_ignored_pages(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
            ->fillField('simpleanalytics_ignore_pages', '/vouchers')
            ->click('Save Changes')
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
            ->assertFieldEquals('simpleanalytics_ignore_pages', '/vouchers');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-ignore-pages="/vouchers"');
    }

    public function test_adds_inactive_script_for_selected_user_roles(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules')
            ->checkField('simpleanalytics_exclude_user_roles-editor')
            ->checkField('simpleanalytics_exclude_user_roles-author')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_exclude_user_roles-editor')
            ->assertFieldChecked('simpleanalytics_exclude_user_roles-author')
            ->visit('http://localhost:8100')
            ->assertSeeElement('script[src="https://scripts.simpleanalyticscdn.com/latest.js"]');

        $this->asAuthor()
            ->visit('http://localhost:8100')
            ->assertSeeElement('script[src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"]')
            ->assertContains('<!-- Simple Analytics: Not logging requests from admins -->');

        $this->asEditor()
            ->visit('http://localhost:8100')
            ->assertSeeElement('script[src="http://localhost:8100/wp-content/plugins/simpleanalytics/resources/js/inactive.js"]')
            ->assertContains('<!-- Simple Analytics: Not logging requests from admins -->');
    }

    public function test_adds_a_script_with_collect_do_not_track_enabled(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
            ->checkField('simpleanalytics_collect_dnt')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_collect_dnt');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-collect-dnt="true"');
    }

    public function test_adds_a_script_with_hash_mode_enabled(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
            ->checkField('simpleanalytics_hash_mode')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_hash_mode');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-mode="hash"');
    }

    public function test_adds_a_script_with_manually_collect_page_views_enabled(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
            ->checkField('simpleanalytics_manual_collect')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_manual_collect');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-auto-collect="true"');
    }

    public function test_adds_a_script_with_overwrite_domain_name(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
            ->fillField('simpleanalytics_hostname', 'example.com')
            ->click('Save Changes')
            ->assertFieldEquals('simpleanalytics_hostname', 'example.com');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-hostname="example.com"');
    }

    public function test_adds_a_script_with_global_variable_name(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=advanced')
            ->fillField('simpleanalytics_sa_global', 'ba_event')
            ->click('Save Changes')
            ->assertFieldEquals('simpleanalytics_sa_global', 'ba_event');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-sa-global="ba_event"');
    }

    public function test_adds_automated_events_script_when_enabled(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
            ->checkField('simpleanalytics_automated_events')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_automated_events');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertSeeElement('script[src="https://scripts.simpleanalyticscdn.com/auto-events.js"]');
    }

    public function test_adds_automated_events_script_with_auto_collect_downloads(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
            ->checkField('simpleanalytics_automated_events')
            ->fillField('simpleanalytics_event_collect_downloads', 'outbound,emails,downloads')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_automated_events')
            ->assertFieldEquals('simpleanalytics_event_collect_downloads', 'outbound,emails,downloads');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-collect="outbound,emails,downloads"');
    }

    public function test_adds_automated_events_script_with_download_file_extensions(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
            ->checkField('simpleanalytics_automated_events')
            ->fillField('simpleanalytics_event_extensions', 'pdf,zip')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_automated_events')
            ->assertFieldEquals('simpleanalytics_event_extensions', 'pdf,zip');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-extensions="pdf,zip"');
    }

    public function test_adds_automated_events_script_with_use_titles_enabled(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
            ->checkField('simpleanalytics_automated_events')
            ->checkField('simpleanalytics_event_use_title')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_automated_events')
            ->assertFieldChecked('simpleanalytics_event_use_title');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-use-title');
    }

    public function test_adds_automated_events_script_with_full_urls_enabled(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
            ->checkField('simpleanalytics_automated_events')
            ->checkField('simpleanalytics_event_full_urls')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_automated_events')
            ->assertFieldChecked('simpleanalytics_event_full_urls');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-full-urls');
    }

    public function test_adds_automated_events_script_with_override_global(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=events')
            ->checkField('simpleanalytics_automated_events')
            ->fillField('simpleanalytics_event_sa_global', 'ba_event')
            ->click('Save Changes')
            ->assertFieldChecked('simpleanalytics_automated_events')
            ->assertFieldEquals('simpleanalytics_event_sa_global', 'ba_event');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertContains('data-sa-global="ba_event"');
    }

    public function test_adds_a_script_with_custom_domain_name(): void
    {
        $this->asAdmin()
            ->visit('http://localhost:8100/wp-admin/options-general.php?page=simpleanalytics&tab=general')
            ->fillField('simpleanalytics_custom_domain', 'mydomain.com')
            ->click('Save Changes')
            ->assertFieldEquals('simpleanalytics_custom_domain', 'mydomain.com');

        $this->myBrowser()
            ->visit('http://localhost:8100')
            ->assertSeeElement('script[src="https://mydomain.com/latest.js"]');
    }
}
