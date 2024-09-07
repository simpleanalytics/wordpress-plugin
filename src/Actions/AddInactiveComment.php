<?php

namespace SimpleAnalytics\Actions;

class AddInactiveComment extends Action
{
    protected string $hook = 'wp_footer';

    #[\Override]
    public function handle(): void
    {
        echo "<!-- Simple Analytics: Not logging requests from admins -->\n";
    }
}
