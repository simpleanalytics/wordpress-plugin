<?php

namespace SimpleAnalytics\Actions;

use SimpleAnalytics\Enums\Setting;

class UpdateFooter
{
    public function __construct(protected bool $shouldCollectAnalytics)
    {
    }

    public function __invoke(): void
    {
        if (! $this->shouldCollectAnalytics) {
            echo "<!-- Simple Analytics: Not logging requests from admins -->\n";
        } else {
            echo '<noscript><img src="https://' . get_option(Setting::CUSTOM_DOMAIN, 'queue.simpleanalyticscdn.com') . '/noscript.gif" alt="" referrerpolicy="no-referrer-when-downgrade"></noscript>' . "\n";
        }
    }
}
