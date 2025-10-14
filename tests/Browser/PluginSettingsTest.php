<?php

test('Sign in', function () {
    $page = visit('http://127.0.0.1:8100/wp-admin');

    $page->fill('user_login', 'admin');
    $page->fill('user_pass', 'admin');
    $page->press('wp-submit');

    $page->assertSee('Welcome to WordPress!');
});
