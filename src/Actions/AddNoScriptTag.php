<?php

namespace SimpleAnalytics\Actions;

defined('ABSPATH') || exit;

use SimpleAnalytics\Support\CustomDomain;

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
        return esc_url('https://' . CustomDomain::get('queue.simpleanalyticscdn.com') . '/noscript.gif');
    }
}
