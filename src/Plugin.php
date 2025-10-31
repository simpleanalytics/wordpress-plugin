<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Actions\AddInactiveComment;
use SimpleAnalytics\Actions\AddNoScriptTag;
use SimpleAnalytics\Actions\AddPluginSettingsLink;
use SimpleAnalytics\Scripts\AnalyticsScript;
use SimpleAnalytics\Scripts\AutomatedEventsScript;
use SimpleAnalytics\Scripts\InactiveScript;
use SimpleAnalytics\Settings\AdminPage;

final class Plugin
{
    protected $hooks;
    protected $settings;
    protected $trackingRules;
    protected $scripts;
    protected $adminPage;

    public function __construct(
        WordPressHooks    $hooks,
        WordPressSettings $settings,
        TrackingRules     $trackingRules,
        ScriptRegistry    $scripts,
        AdminPage         $adminPage
    ) {
        $this->hooks = $hooks;
        $this->settings = $settings;
        $this->trackingRules = $trackingRules;
        $this->scripts = $scripts;
        $this->adminPage = $adminPage;
    }

    public function boot(): void
    {
        $this->hooks->addAction('init', \Closure::fromCallable([$this, 'onInit']));
        $this->hooks->onActivation(\Closure::fromCallable([$this, 'onActivation']));
        $this->hooks->onDeactivation(\Closure::fromCallable([$this, 'onUninstall']));

        if ($this->hooks->isAdmin()) {
            $this->adminPage->register();
            AddPluginSettingsLink::register();
        }
    }

    public function onInit(): void
    {
        $tracking = ! $this->trackingRules->hasExcludedIp();
        $excludedRole = $this->trackingRules->hasExcludedUserRole();

        if ($tracking && $this->settings->get(SettingName::NOSCRIPT)) {
            AddNoScriptTag::register();
        }

        if (! $excludedRole) {
            AddInactiveComment::register();
        }

        if ($tracking && ! $excludedRole) {
            $this->scripts->push(new AnalyticsScript);
        } else {
            $this->scripts->push(new InactiveScript);
        }

        if ($this->settings->boolean(SettingName::AUTOMATED_EVENTS)) {
            $this->scripts->push(new AutomatedEventsScript);
        }

        $this->scripts->register();
    }

    public function onActivation(): void
    {
        $this->settings->register(SettingName::ENABLED, true);

        foreach (array_diff(SettingName::cases(), [SettingName::ENABLED]) as $name) {
            $this->settings->register($name);
        }

        // Legacy. Update the option introduced in a previous release to use autoloading.
        $this->settings->update(SettingName::CUSTOM_DOMAIN, $this->settings->get(SettingName::CUSTOM_DOMAIN), true);
    }

    public function onUninstall(): void
    {
        foreach (SettingName::cases() as $key) $this->settings->delete($key);
    }
}
