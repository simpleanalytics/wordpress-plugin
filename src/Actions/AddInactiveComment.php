<?php

namespace SimpleAnalytics\Actions;

class AddInactiveComment
{
    use Action;

    protected string $hook = 'wp_footer';

    public function handle(): void
    {
        echo "<!-- Simple Analytics: Not logging requests from admins -->\n";
    }
}
