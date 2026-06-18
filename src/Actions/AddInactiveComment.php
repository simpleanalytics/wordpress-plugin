<?php

namespace SimpleAnalytics\Actions;

class AddInactiveComment
{
    use Action;

    /**
     * @var string
     */
    protected $hook = 'wp_footer';

    /** @var string */
    protected $triggeredRule;

    /**
     * @param string $triggeredRule
     */
    public function __construct(string $triggeredRule = '')
    {
        $this->triggeredRule = trim($triggeredRule);
    }

    public function handle(): void
    {
        $reason = $this->triggeredRule !== '' ? $this->triggeredRule : 'Unknown Rule';

        echo sprintf(
            "<!-- Simple Analytics: Script not included because this visitor is excluded by tracking rule: %s -->\n",
            \esc_html($reason)
        );
    }
}
