<?php

namespace Tests;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Pest\Browser\Api\Webpage;

pest()->extend(TestCase::class)->in('Feature');
pest()->browser()->timeout(30000);

function testBaseUrl(): string
{
    return rtrim(getenv('TEST_BASE_URL') ?: 'http://localhost:8100', '/');
}

function testUrl(string $path = ''): string
{
    return testBaseUrl() . '/' . ltrim($path, '/');
}

beforeEach(function () {
    $headers = @get_headers(testUrl('/wp-login.php'));
    if (!$headers) throw new \RuntimeException('WordPress test site is unavailable. Set TEST_BASE_URL or run local site on localhost:8100.');
});

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function asUser(string $login, string $password)
{
    return visit(testUrl('/wp-login.php'))
        ->assertPresent('#loginform')
        ->fill('user_login', $login)
        ->fill('user_pass', $password)
        ->press('wp-submit')
        ->assertPresent('#wpadminbar');
}

function asAdmin()
{
    return asUser('admin', 'admin');
}

function asAuthor()
{
    return asUser('author', 'author');
}

function asEditor()
{
    return asUser('editor', 'editor');
}
