<?php

namespace SimpleAnalytics\Actions;

use SimpleAnalytics\Setting;
use SimpleAnalytics\SettingName;

class AddNoScriptTag
{
    use Action;

    /**
     * @var string
     */
    protected $hook = 'wp_footer';

    public function handle(): void
    {
        echo sprintf("<noscript><img src=\"%s\" alt=\"\" referrerpolicy=\"no-referrer-when-downgrade\"></noscript>\n", $this->getCustomDomain());
    }

    public function getCustomDomain(): string
    {
        return esc_url('https://' . Setting::get(SettingName::CUSTOM_DOMAIN, 'queue.simpleanalyticscdn.com') . '/noscript.gif');
    }
}
