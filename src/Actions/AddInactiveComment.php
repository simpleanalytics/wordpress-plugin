<?php

namespace SimpleAnalytics\Actions;

class AddInactiveComment
{
    use Action;

    /**
     * @var string
     */
    protected $hook = 'wp_footer';

    public function handle(): void
    {
        echo "<!-- Simple Analytics: Not logging requests from admins -->\n";
    }
}
