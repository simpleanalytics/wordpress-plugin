<?php

namespace Tests\Browser;

use function Tests\asAdmin;

const DEFAULT_SA_NOTICE = '<!-- Simple Analytics: Not logging requests from admins -->';

test('Sign in as admin and activate the plugin', function () {
    $page = asAdmin();

    $page->navigate('http://127.0.0.1:8100/wp-admin/plugins.php')->screenshot();
    $page->assertSee('Simple Analytics Official');

    $page2 = visit('http://127.0.0.1:8100');//->screenshot();
    expect($page2->content())->not->toContain(DEFAULT_SA_NOTICE);
});
